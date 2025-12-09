<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Barbero;
use App\Models\User;
use App\Models\Horario;
use App\Models\Reserva;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BarberoController extends Controller
{
    public function __construct()
    {
        /* $this->middleware('permission:barberos.view')->only(['index']);
        $this->middleware('permission:barberos.create')->only(['create','store']);
        $this->middleware('permission:barberos.update')->only(['edit','update']);
        $this->middleware('permission:barberos.delete')->only(['destroy']); */
    }

    public function index(Request $request)
    {
        $q = $request->string('q');

        $barberos = Barbero::query()
            ->with(['user:id,name,email'])
            ->when($q, function ($query) use ($q) {
                $query->whereHas('user', function ($uq) use ($q) {
                    $uq->where('name', 'ilike', "%{$q}%")
                       ->orWhere('email', 'ilike', "%{$q}%");
                });
            })
            ->orderByDesc('id_barbero')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Barberos.Index', [
            'barberos' => $barberos,
            'filters' => ['q' => $q],
        ]);
    }

    public function create()
    {
        // Solo usuarios tipo barbero que NO tengan un registro en la tabla barbero
        $usuarios = User::where('tipo_usuario', 'barbero')
            ->whereDoesntHave('barbero')
            ->orderBy('name')
            ->get(['id','name','email']);
        return Inertia::render('Barberos.Create', [
            'usuarios' => $usuarios,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_usuario' => ['required', 'unique:barbero,id_usuario', 'exists:users,id'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'calificacion_promedio' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'estado' => ['nullable', 'in:disponible,ocupado,ausente'],
        ]);

        // Procesar imagen si existe
        if ($request->hasFile('foto_perfil')) {
            $imagen = $request->file('foto_perfil');
            $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
            $ruta = $imagen->storeAs('barberos', $nombreImagen, 'public');
            $data['foto_perfil'] = '/storage/' . $ruta;
        }

        Barbero::create($data);
        return redirect()->route('barberos.index');
    }

    public function edit(Barbero $barbero)
    {
        // Usuarios tipo barbero sin asociar O el usuario actual del barbero
        $usuarios = User::where('tipo_usuario', 'barbero')
            ->where(function($query) use ($barbero) {
                $query->whereDoesntHave('barbero')
                      ->orWhere('id', $barbero->id_usuario);
            })
            ->orderBy('name')
            ->get(['id','name','email']);
        return Inertia::render('Barberos.Edit', [
            'barbero' => $barbero->load('user:id,name,email'),
            'usuarios' => $usuarios,
        ]);
    }

    public function update(Request $request, Barbero $barbero)
    {
        $data = $request->validate([
            'id_usuario' => ['required', 'exists:users,id', 'unique:barbero,id_usuario,' . $barbero->id_barbero . ',id_barbero'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'calificacion_promedio' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'estado' => ['nullable', 'in:disponible,ocupado,ausente'],
        ]);

        // Procesar imagen si existe
        if ($request->hasFile('foto_perfil')) {
            // Eliminar imagen anterior si existe
            if ($barbero->foto_perfil) {
                $rutaAntigua = str_replace('/storage/', '', $barbero->foto_perfil);
                Storage::disk('public')->delete($rutaAntigua);
            }
            
            $imagen = $request->file('foto_perfil');
            $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
            $ruta = $imagen->storeAs('barberos', $nombreImagen, 'public');
            $data['foto_perfil'] = '/storage/' . $ruta;
        }

        $barbero->update($data);
        return redirect()->route('barberos.index');
    }

    public function destroy(Barbero $barbero)
    {
        $barbero->delete();
        return redirect()->route('barberos.index');
    }

    /**
     * API: Obtener barberos disponibles con sus horarios
     */
    public function disponibles()
    {
        return response()->json(
            Barbero::with(['user:id,name', 'horarios'])
                ->where('estado', 'disponible')
                ->get(['id_barbero', 'id_usuario', 'especialidad', 'estado'])
        );
    }

    /**
     * API: Obtener horarios disponibles de un barbero en una fecha específica
     */
    public function horariosDisponibles(Request $request)
    {
        $barberoId = $request->integer('barbero_id');
        $fecha = $request->input('fecha');

        if (!$barberoId || !$fecha) {
            return response()->json([]);
        }

        try {
            // Obtener día de la semana sin tildes (como está en el ENUM de PostgreSQL)
            $carbon = Carbon::parse($fecha);
            $diaConTilde = strtolower($carbon->locale('es')->isoFormat('dddd'));
            
            // Mapeo de días con tilde a sin tilde (según el ENUM de PostgreSQL)
            $mapaDias = [
                'lunes' => 'lunes',
                'martes' => 'martes',
                'miércoles' => 'miercoles',
                'jueves' => 'jueves',
                'viernes' => 'viernes',
                'sábado' => 'sabado',
                'domingo' => 'domingo',
            ];
            
            $diaSemana = $mapaDias[$diaConTilde] ?? $diaConTilde;

            $horariosDelDia = Horario::where('id_barbero', $barberoId)
                ->where('dia_semana', $diaSemana)
                ->where('estado', 'activo')
                ->get();

            if ($horariosDelDia->isEmpty()) {
                return response()->json([]);
            }

            // Generar horarios disponibles cada 30 minutos
            $horariosDisponibles = [];

            foreach ($horariosDelDia as $horario) {
                $inicio = Carbon::parse($horario->hora_inicio);
                $fin = Carbon::parse($horario->hora_fin);
                $duracionServicio = 30; // minutos por defecto

                while ($inicio->copy()->addMinutes($duracionServicio) <= $fin) {
                    // Verificar si hay reserva en ese horario
                    $tieneReserva = Reserva::where('id_barbero', $barberoId)
                        ->where('fecha_reserva', $fecha)
                        ->where('hora_inicio', $inicio->format('H:i'))
                        ->whereNotIn('estado', ['cancelada', 'no_asistio'])
                        ->exists();

                    if (!$tieneReserva) {
                        $horariosDisponibles[] = $inicio->format('H:i');
                    }

                    $inicio->addMinutes(30);
                }
            }

            return response()->json(array_values(array_unique($horariosDisponibles)));
        } catch (\Exception $e) {
            \Log::error('Error en horariosDisponibles: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
