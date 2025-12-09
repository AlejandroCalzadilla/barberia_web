<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Barbero;
use App\Models\Servicio;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservaController extends Controller
{
    public function __construct()
    {
       /*  $this->middleware('permission:reservas.view')->only(['index']);
        $this->middleware('permission:reservas.create')->only(methods: ['create','store']);
        $this->middleware('permission:reservas.update')->only(['edit','update']);
        $this->middleware('permission:reservas.delete')->only(['destroy']); */
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isPropietario = $user->is_propietario;
        $isBarbero = $user->is_barbero;
        $isCliente = $user->is_cliente;

        // CLIENTE: Solo ve sus propias reservas
        if ($isCliente) {
            $cliente = $user->cliente;
            if (!$cliente) {
                return Inertia::render('Reservas/Index', [
                    'reservas' => [],
                    'isCliente' => true,
                    'filters' => [],
                    'message' => 'No tienes un perfil de cliente asociado',
                ]);
            }

            $reservas = Reserva::where('id_cliente', $cliente->id_cliente)
                ->with(['barbero.user:id,name', 'servicio:id_servicio,nombre,precio', 'pagos'])
                ->orderByDesc('fecha_reserva')
                ->get();

            return Inertia::render('Reservas/Index', [
                'reservas' => $reservas,
                'isCliente' => true,
                'clienteNombre' => $user->name,
                'filters' => [],
            ]);
        }

        // BARBERO: Solo ve sus propias reservas
        if ($isBarbero && !$isPropietario) {
            $barbero = $user->barbero;
            $reservas = Reserva::where('id_barbero', $barbero->id_barbero)
                ->with(['cliente.user:id,name', 'servicio:id_servicio,nombre,precio'])
                ->orderByDesc('fecha_reserva')
                ->get();

            return Inertia::render('Reservas/Index', [
                'reservas' => $reservas,
                'isBarbero' => true,
                'barberoNombre' => $user->name,
                'filters' => [],
            ]);
        }

        // PROPIETARIO: Ve todas las reservas con filtros
        $clienteId = $request->integer('cliente');
        $barberoId = $request->integer('barbero');
        $estado = $request->input('estado');
        $fecha = $request->input('fecha');

        $reservas = Reserva::query()
            ->with(['cliente.user:id,name', 'barbero.user:id,name', 'servicio:id_servicio,nombre'])
            ->when($clienteId, fn($q) => $q->where('id_cliente', $clienteId))
            ->when($barberoId, fn($q) => $q->where('id_barbero', $barberoId))
            ->when(is_string($estado) && $estado !== '', fn($q) => $q->where('estado', $estado))
            ->when(is_string($fecha) && $fecha !== '', fn($q) => $q->whereDate('fecha_reserva', $fecha))
            ->orderByDesc('id_reserva')
            ->paginate(10);

        $clientes = Cliente::with('user:id,name')->get(['id_cliente','id_usuario']);
        $barberos = Barbero::with('user:id,name')->get(['id_barbero','id_usuario']);
        $servicios = Servicio::orderBy('nombre')->get(['id_servicio','nombre']);

        return Inertia::render('Reservas/Index', [
            'reservas' => $reservas,
            'clientes' => $clientes,
            'barberos' => $barberos,
            'servicios' => $servicios,
            'isPropietario' => true,
            'filters' => [
                'cliente' => $clienteId,
                'barbero' => $barberoId,
                'estado' => $estado,
                'fecha' => $fecha,
            ],
        ]);
    }

    public function create()
    {
        $clientes = Cliente::with('user:id,name')->get(['id_cliente','id_usuario']);
        $barberos = Barbero::with('user:id,name')->get(['id_barbero','id_usuario']);
        $servicios = Servicio::orderBy('nombre')->get(['id_servicio','nombre','precio','duracion_minutos']);

        return Inertia::render('Reservas/Create', [
            'clientes' => $clientes,
            'barberos' => $barberos,
            'servicios' => $servicios,
            'hoy' => now()->format('Y-m-d'),
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('=== INICIO store() ===');
        \Log::info('Request data:', $request->all());
        
        $validated = $request->validate([
            'id_cliente' => 'required|exists:cliente,id_cliente',
            'id_barbero' => 'required|exists:barbero,id_barbero',
            'id_servicio' => 'required|exists:servicio,id_servicio',
            'fecha_reserva' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'notas' => 'nullable|string',
            'tipo_pago' => 'required|in:anticipo,completo',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,qr',
            'notas_pago' => 'nullable|string',
        ]);
        
        \Log::info('Validación exitosa:', $validated);

        DB::beginTransaction();
        
        try {
            // Calcular hora de finalización basada en la duración del servicio
            $servicio = Servicio::findOrFail($validated['id_servicio']);
            $horaInicio = Carbon::parse($validated['hora_inicio']);
            $horaFin = $horaInicio->copy()->addMinutes($servicio->duracion_minutos);

            $validated['hora_fin'] = $horaFin->format('H:i');
            $validated['total'] = $servicio->precio; // Cambiado de precio_servicio a total
            $validated['estado'] = 'confirmada'; // Estado por defecto
            
            // Calcular anticipo automáticamente (50%)
            $montoAnticipo = $servicio->precio * 0.50;
            $validated['monto_anticipo'] = $montoAnticipo;
            $validated['porcentaje_anticipo'] = 50;

            // Verificar disponibilidad del barbero
            $existeReserva = Reserva::where('id_barbero', $validated['id_barbero'])
                ->where('fecha_reserva', $validated['fecha_reserva'])
                ->where(function ($query) use ($horaInicio, $horaFin) {
                    $query->whereBetween('hora_inicio', [$horaInicio->format('H:i'), $horaFin->format('H:i')])
                        ->orWhereBetween('hora_fin', [$horaInicio->format('H:i'), $horaFin->format('H:i')])
                        ->orWhere(function ($q) use ($horaInicio, $horaFin) {
                            $q->where('hora_inicio', '<=', $horaInicio->format('H:i'))
                                ->where('hora_fin', '>=', $horaFin->format('H:i'));
                        });
                })
                ->whereNotIn('estado', ['cancelada', 'no_asistio'])
                ->exists();

            if ($existeReserva) {
                return back()->with('error', 'El barbero ya tiene una reserva en ese horario');
            }

            $reserva = Reserva::create($validated);
            \Log::info('Reserva creada:', ['id' => $reserva->id_reserva]);

            // Crear pagos según el tipo
            if ($validated['tipo_pago'] === 'anticipo') {
                // Crear pago de anticipo (50%)
                $reserva->pagos()->create([
                    'monto_total' => $montoAnticipo,
                    'fecha_pago' => now(),
                    'metodo_pago' => $validated['metodo_pago'],
                    'estado' => 'pagado',
                    'tipo_pago' => 'anticipo',
                    'notas' => $validated['notas_pago'] ?? null,
                ]);
                
                // Crear pago final pendiente (50% restante)
                $montoFinal = $servicio->precio - $montoAnticipo;
                $reserva->pagos()->create([
                    'monto_total' => $montoFinal,
                    'metodo_pago' => $validated['metodo_pago'],
                    'estado' => 'pendiente',
                    'tipo_pago' => 'pago_final',
                    'notas' => null,
                ]);
            } else {
                // Crear un solo pago completo
                $reserva->pagos()->create([
                    'monto_total' => $servicio->precio,
                    'fecha_pago' => now(),
                    'metodo_pago' => $validated['metodo_pago'],
                    'estado' => 'pagado',
                    'tipo_pago' => 'completo',
                    'notas' => $validated['notas_pago'] ?? null,
                ]);
            }
            DB::commit();
            \Log::info('Transaction committed successfully');

            return redirect()
                ->route('reservas.index')
                ->with('success', 'Reserva creada correctamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ERROR en store():', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al crear la reserva: ' . $e->getMessage());
        }
    }

    public function edit(Reserva $reserva)
    {
        $reserva->load(['cliente.user', 'barbero.user', 'servicio', 'pagos']);
        $clientes = Cliente::with('user:id,name')->get(['id_cliente','id_usuario']);
        $barberos = Barbero::with('user:id,name')->get(['id_barbero','id_usuario']);
        $servicios = Servicio::orderBy('nombre')->get(['id_servicio','nombre','precio','duracion_minutos']);

        return Inertia::render('Reservas/Edit', [
            'reserva' => $reserva,
            'clientes' => $clientes,
            'barberos' => $barberos,
            'servicios' => $servicios,
            'hoy' => now()->format('Y-m-d'),
        ]);
    }

    public function update(Request $request, Reserva $reserva)
    {
        $validated = $request->validate([
            'fecha_reserva' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'estado' => ['required', 'in:confirmada,completada,cancelada,no_asistio'],
        ]);

        DB::beginTransaction();
        
        try {
            // Calcular hora_fin basada en la duración del servicio
            $servicio = Servicio::findOrFail($reserva->id_servicio);
            $horaInicio = Carbon::parse($validated['hora_inicio']);
            $horaFin = $horaInicio->copy()->addMinutes($servicio->duracion_minutos);
            $validated['hora_fin'] = $horaFin->format('H:i');

            // Verificar disponibilidad (excluyendo la reserva actual)
            $existeReserva = Reserva::where('id_barbero', $reserva->id_barbero)
                ->where('id_reserva', '!=', $reserva->id_reserva)
                ->where('fecha_reserva', $validated['fecha_reserva'])
                ->where(function ($query) use ($horaInicio, $horaFin) {
                    $query->whereBetween('hora_inicio', [$horaInicio->format('H:i'), $horaFin->format('H:i')])
                        ->orWhereBetween('hora_fin', [$horaInicio->format('H:i'), $horaFin->format('H:i')])
                        ->orWhere(function ($q) use ($horaInicio, $horaFin) {
                            $q->where('hora_inicio', '<=', $horaInicio->format('H:i'))
                                ->where('hora_fin', '>=', $horaFin->format('H:i'));
                        });
                })
                ->whereNotIn('estado', ['cancelada', 'no_asistio'])
                ->exists();

            if ($existeReserva) {
                return back()->with('error', 'El barbero ya tiene una reserva en ese horario');
            }

            $reserva->update($validated);
            
            DB::commit();
            
            return redirect()->route('reservas.index')->with('success', 'Reserva actualizada correctamente');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la reserva: ' . $e->getMessage());
        }
    }

    public function destroy(Reserva $reserva)
    {
        DB::beginTransaction();
        
        try {
            if ($reserva->estado === 'completada') {
                return back()->with('error', 'No se puede eliminar una reserva completada');
            }

            // Eliminar pagos asociados
            $reserva->pagos()->delete();
            
            // Eliminar la reserva
            $reserva->delete();
            
            DB::commit();

            return redirect()
                ->route('reservas.index')
                ->with('success', 'Reserva eliminada correctamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la reserva: ' . $e->getMessage());
        }
    }

    public function horariosDisponibles(Request $request)
    {
        $barberoId = $request->integer('barbero_id');
        $fecha = $request->input('fecha');
        $servicioId = $request->integer('servicio_id');

        if (!$barberoId || !$fecha) {
            return response()->json(['horarios' => []]);
        }

        // Obtener el día de la semana (0=Domingo, 1=Lunes, ...)
        $diaSemana = Carbon::parse($fecha)->dayOfWeek;
        
        // Mapear a formato de BD (lunes, martes, etc.)
        $diasMap = [
            0 => 'domingo',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
        ];
        
        $diaNombre = $diasMap[$diaSemana];

        // Obtener horarios del barbero para ese día
        $horarios = \App\Models\Horario::where('id_barbero', $barberoId)
            ->where('dia_semana', $diaNombre)
            ->where('estado', 'activo')
            ->get();

        if ($horarios->isEmpty()) {
            return response()->json(['horarios' => []]);
        }

        // Obtener duración del servicio
        $duracionMinutos = 30; // Por defecto
        if ($servicioId) {
            $servicio = Servicio::find($servicioId);
            if ($servicio) {
                $duracionMinutos = $servicio->duracion_minutos;
            }
        }

        // Obtener reservas existentes para ese barbero y fecha
        $reservasExistentes = Reserva::where('id_barbero', $barberoId)
            ->where('fecha_reserva', $fecha)
            ->whereNotIn('estado', ['cancelada', 'no_asistio'])
            ->get(['hora_inicio', 'hora_fin']);

        $horariosDisponibles = [];

        foreach ($horarios as $horario) {
            $inicio = Carbon::parse($horario->hora_inicio);
            $fin = Carbon::parse($horario->hora_fin);

            // Generar slots de tiempo cada 30 minutos
            $current = $inicio->copy();
            while ($current->copy()->addMinutes($duracionMinutos) <= $fin) {
                $slotInicio = $current->format('H:i');
                $slotFin = $current->copy()->addMinutes($duracionMinutos)->format('H:i');

                // Verificar si el slot está ocupado
                $ocupado = false;
                foreach ($reservasExistentes as $reserva) {
                    $resInicio = Carbon::parse($reserva->hora_inicio);
                    $resFin = Carbon::parse($reserva->hora_fin);
                    $slotInicioCarbon = Carbon::parse($slotInicio);
                    $slotFinCarbon = Carbon::parse($slotFin);

                    // Verificar solapamiento
                    if (
                        ($slotInicioCarbon >= $resInicio && $slotInicioCarbon < $resFin) ||
                        ($slotFinCarbon > $resInicio && $slotFinCarbon <= $resFin) ||
                        ($slotInicioCarbon <= $resInicio && $slotFinCarbon >= $resFin)
                    ) {
                        $ocupado = true;
                        break;
                    }
                }

                if (!$ocupado) {
                    $horariosDisponibles[] = [
                        'hora' => $slotInicio,
                        'label' => $slotInicio . ' - ' . $slotFin,
                    ];
                }

                $current->addMinutes(30);
            }
        }

        return response()->json(['horarios' => $horariosDisponibles]);
    }
}
