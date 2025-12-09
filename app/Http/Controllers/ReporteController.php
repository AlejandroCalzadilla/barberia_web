<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Barbero;
use App\Models\Servicio;
use App\Models\Cliente;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function index()
    {
        // Obtener filtros de la petición
        $fechaInicio = request('fecha_inicio', now()->subMonth()->toDateString());
        $fechaFin = request('fecha_fin', now()->toDateString());
        $idBarbero = request('id_barbero');
        $idServicio = request('id_servicio');
        $metodoPago = request('metodo_pago');
        $estadoReserva = request('estado_reserva');
        
        $año = now()->year;
        $mes = now()->month;

        // Query base para pagos con filtros
        $pagosQuery = Pago::where('pago.estado', 'pagado')
            ->join('reserva', 'pago.id_reserva', '=', 'reserva.id_reserva')
            ->whereBetween('pago.fecha_pago', [$fechaInicio, $fechaFin]);
        
        if ($idBarbero) {
            $pagosQuery->where('reserva.id_barbero', $idBarbero);
        }
        if ($idServicio) {
            $pagosQuery->where('reserva.id_servicio', $idServicio);
        }
        if ($metodoPago) {
            $pagosQuery->where('pago.metodo_pago', $metodoPago);
        }
        if ($estadoReserva) {
            $pagosQuery->where('reserva.estado', $estadoReserva);
        }

        $pagosData = $pagosQuery->orderBy('pago.fecha_pago')
            ->select('pago.id_pago', 'pago.monto_total', 'pago.fecha_pago', 'pago.estado')
            ->get();

        // Query base para reservas con filtros - USAR fecha_reserva en lugar de created_at
        $reservasQuery = Reserva::whereBetween('fecha_reserva', [$fechaInicio, $fechaFin]);
        
        if ($idBarbero) {
            $reservasQuery->where('id_barbero', $idBarbero);
        }
        if ($idServicio) {
            $reservasQuery->where('id_servicio', $idServicio);
        }
        if ($estadoReserva) {
            $reservasQuery->where('estado', $estadoReserva);
        }

        $reservasData = $reservasQuery->orderBy('id_reserva')
            ->get(['id_reserva', 'estado']);
        
        \Log::info('ReporteController - Reservas obtenidas', [
            'filtros' => compact('fechaInicio', 'fechaFin', 'idBarbero', 'idServicio', 'estadoReserva'),
            'total_reservas' => $reservasData->count()
        ]);

        // Ingresos mensuales
        $ingresosMensuales = Pago::query()
            ->join('reserva', 'pago.id_reserva', '=', 'reserva.id_reserva')
            ->whereYear('reserva.created_at', $año)
            ->whereMonth('reserva.created_at', $mes)
            ->selectRaw('
                COUNT(DISTINCT reserva.id_reserva) as total_reservas,
                COUNT(DISTINCT CASE WHEN reserva.estado = \'completada\' THEN reserva.id_reserva END) as reservas_completadas,
                COALESCE(SUM(CASE WHEN pago.estado = \'pagado\' THEN pago.monto_total ELSE 0 END), 0) as ingresos_totales,
                COALESCE(SUM(CASE WHEN pago.tipo_pago = \'anticipo\' AND pago.estado = \'pagado\' THEN pago.monto_total ELSE 0 END), 0) as anticipos_recibidos,
                COALESCE(SUM(CASE WHEN pago.tipo_pago = \'pago_completo\' AND pago.estado = \'pagado\' THEN pago.monto_total ELSE 0 END), 0) as pagos_finales,
                COALESCE(SUM(CASE WHEN pago.estado = \'pendiente\' THEN pago.monto_total ELSE 0 END), 0) as pagos_pendientes
            ')
            ->first();
        //dd($ingresosMensuales);    

        // Ranking de barberos con filtros
        $rankingBarberosaRaw = Barbero::query()
            ->leftJoin('reserva', 'barbero.id_barbero', '=', 'reserva.id_barbero')
            ->leftJoin('pago', 'reserva.id_reserva', '=', 'pago.id_reserva')
            ->whereBetween('reserva.fecha_reserva', [$fechaInicio, $fechaFin]);
        
        if ($idBarbero) {
            $rankingBarberosaRaw->where('barbero.id_barbero', $idBarbero);
        }
        if ($idServicio) {
            $rankingBarberosaRaw->where('reserva.id_servicio', $idServicio);
        }
        if ($estadoReserva) {
            $rankingBarberosaRaw->where('reserva.estado', $estadoReserva);
        }
        
        $rankingBarberosaRaw = $rankingBarberosaRaw->select('barbero.id_barbero', 'barbero.especialidad')
            ->selectRaw('
                COUNT(DISTINCT reserva.id_reserva) as total_servicios,
                COUNT(DISTINCT CASE WHEN reserva.estado = \'completada\' THEN reserva.id_reserva END) as servicios_completados,
                COALESCE(SUM(CASE WHEN pago.estado = \'pagado\' THEN pago.monto_total ELSE 0 END), 0) as ingresos_generados,
                COALESCE(AVG(CASE WHEN pago.estado = \'pagado\' THEN pago.monto_total END), 0) as promedio_por_servicio
            ')
            ->groupBy('barbero.id_barbero', 'barbero.especialidad')
            ->orderByDesc('servicios_completados')
            ->orderByDesc('ingresos_generados')
            ->get();

        $rankingBarberos = $rankingBarberosaRaw->map(function ($barbero) {
            $user = Barbero::find($barbero->id_barbero)->user;
            return [
                'id_barbero' => $barbero->id_barbero,
                'nombre' => $user->name ?? 'N/A',
                'especialidad' => $barbero->especialidad,
                'total_servicios' => $barbero->total_servicios ?? 0,
                'servicios_completados' => $barbero->servicios_completados ?? 0,
                'ingresos_generados' => floatval($barbero->ingresos_generados ?? 0),
                'promedio_por_servicio' => floatval($barbero->promedio_por_servicio ?? 0),
            ];
        });

        // Servicios más populares con filtros
        $serviciosMasPopulares = Servicio::query()
            ->leftJoin('reserva', 'servicio.id_servicio', '=', 'reserva.id_servicio')
            ->leftJoin('pago', 'reserva.id_reserva', '=', 'pago.id_reserva')
            ->whereBetween('reserva.fecha_reserva', [$fechaInicio, $fechaFin])
            ->where('servicio.estado', 'activo');
        
        if ($idBarbero) {
            $serviciosMasPopulares->where('reserva.id_barbero', $idBarbero);
        }
        if ($idServicio) {
            $serviciosMasPopulares->where('servicio.id_servicio', $idServicio);
        }
        if ($estadoReserva) {
            $serviciosMasPopulares->where('reserva.estado', $estadoReserva);
        }
        
        $serviciosMasPopulares = $serviciosMasPopulares->selectRaw('
                servicio.id_servicio,
                servicio.nombre,
                servicio.precio,
                servicio.duracion_minutos,
                COUNT(reserva.id_reserva) as veces_solicitado,
                COUNT(CASE WHEN reserva.estado = \'completada\' THEN 1 END) as veces_completado,
                COALESCE(SUM(CASE WHEN pago.estado = \'pagado\' THEN pago.monto_total ELSE 0 END), 0) as ingresos_generados
            ')
            ->groupBy('servicio.id_servicio', 'servicio.nombre', 'servicio.precio', 'servicio.duracion_minutos')
            ->orderByDesc('veces_completado')
            ->orderByDesc('ingresos_generados')
            ->limit(5)
            ->get()
            ->map(function ($servicio) {
                return [
                    'id_servicio' => $servicio->id_servicio,
                    'nombre' => $servicio->nombre,
                    'precio' => floatval($servicio->precio),
                    'duracion_minutos' => $servicio->duracion_minutos,
                    'veces_solicitado' => $servicio->veces_solicitado ?? 0,
                    'veces_completado' => $servicio->veces_completado ?? 0,
                    'ingresos_generados' => floatval($servicio->ingresos_generados ?? 0),
                ];
            });

        // Clientes frecuentes - CORREGIDO
        $clientesFrecuentesRaw = Cliente::query()
            ->join('reserva', 'cliente.id_cliente', '=', 'reserva.id_cliente')
            ->leftJoin('pago', 'reserva.id_reserva', '=', 'pago.id_reserva')
            ->whereBetween('reserva.fecha_reserva', [$fechaInicio, $fechaFin]);
        
        if ($idBarbero) {
            $clientesFrecuentesRaw->where('reserva.id_barbero', $idBarbero);
        }
        if ($idServicio) {
            $clientesFrecuentesRaw->where('reserva.id_servicio', $idServicio);
        }
        if ($estadoReserva) {
            $clientesFrecuentesRaw->where('reserva.estado', $estadoReserva);
        }
        
        $clientesFrecuentesRaw = $clientesFrecuentesRaw->select('cliente.id_cliente')
            ->selectRaw('
                COUNT(DISTINCT reserva.id_reserva) as total_reservas,
                COUNT(DISTINCT CASE WHEN reserva.estado = \'completada\' THEN reserva.id_reserva END) as reservas_completadas,
                COALESCE(SUM(CASE WHEN pago.estado = \'pagado\' THEN pago.monto_total ELSE 0 END), 0) as gasto_total,
                COALESCE(AVG(CASE WHEN pago.estado = \'pagado\' THEN pago.monto_total END), 0) as promedio_por_visita,
                MAX(reserva.created_at) as ultima_visita
            ')
            ->groupBy('cliente.id_cliente')
            ->havingRaw('COUNT(DISTINCT reserva.id_reserva) > 0')
            ->havingRaw('COALESCE(SUM(CASE WHEN pago.estado = \'pagado\' THEN pago.monto_total ELSE 0 END), 0) > 0')
            ->orderByDesc('gasto_total')
            ->orderByDesc('reservas_completadas')
            ->limit(10)
            ->get();

        // Cargar los usuarios para los clientes
        $clienteIds = $clientesFrecuentesRaw->pluck('id_cliente');
        $usuarios = \App\Models\User::whereIn('id', function($query) use ($clienteIds) {
            $query->select('id_usuario')
                  ->from('cliente')
                  ->whereIn('id_cliente', $clienteIds);
        })->get()->keyBy('id');

        $clientesFrecuentes = $clientesFrecuentesRaw->map(function ($cliente) use ($clienteIds) {
            $user = Cliente::find($cliente->id_cliente)->user;
            return [
                'id_cliente' => $cliente->id_cliente,
                'nombre' => $user->name ?? 'N/A',
                'email' => $user->email ?? 'N/A',
                'telefono' => $user->phone ?? 'N/A',
                'total_reservas' => $cliente->total_reservas ?? 0,
                'reservas_completadas' => $cliente->reservas_completadas ?? 0,
                'gasto_total' => floatval($cliente->gasto_total ?? 0),
                'promedio_por_visita' => floatval($cliente->promedio_por_visita ?? 0),
                'ultima_visita' => $cliente->ultima_visita,
            ];
        });

        // Distribución de estados con filtros
        $distribucionEstadosQuery = Reserva::query()
            ->whereBetween('fecha_reserva', [$fechaInicio, $fechaFin]);
        
        if ($idBarbero) {
            $distribucionEstadosQuery->where('id_barbero', $idBarbero);
        }
        if ($idServicio) {
            $distribucionEstadosQuery->where('id_servicio', $idServicio);
        }
        if ($estadoReserva) {
            $distribucionEstadosQuery->where('estado', $estadoReserva);
        }
        
        $distribucionEstados = $distribucionEstadosQuery->selectRaw('
                COUNT(*) as total_reservas,
                COUNT(CASE WHEN estado = \'completada\' THEN 1 END) as completadas,
                COUNT(CASE WHEN estado = \'cancelada\' THEN 1 END) as canceladas,
                COUNT(CASE WHEN estado = \'no_asistio\' THEN 1 END) as no_asistio,
                COUNT(CASE WHEN estado = \'confirmada\' THEN 1 END) as confirmadas,
                COUNT(CASE WHEN estado = \'en_proceso\' THEN 1 END) as en_proceso,
                COUNT(CASE WHEN estado = \'pendiente_pago\' THEN 1 END) as pendiente_pago
            ')
            ->first();

        // Distribución de métodos de pago con filtros
        $distribucionMetodosPagoQuery = Pago::query()
            ->join('reserva', 'pago.id_reserva', '=', 'reserva.id_reserva')
            ->whereBetween('reserva.fecha_reserva', [$fechaInicio, $fechaFin])
            ->where('pago.estado', 'pagado');
        
        if ($idBarbero) {
            $distribucionMetodosPagoQuery->where('reserva.id_barbero', $idBarbero);
        }
        if ($idServicio) {
            $distribucionMetodosPagoQuery->where('reserva.id_servicio', $idServicio);
        }
        if ($metodoPago) {
            $distribucionMetodosPagoQuery->where('pago.metodo_pago', $metodoPago);
        }
        if ($estadoReserva) {
            $distribucionMetodosPagoQuery->where('reserva.estado', $estadoReserva);
        }
        
        $distribucionMetodosPago = $distribucionMetodosPagoQuery->selectRaw('
                pago.metodo_pago,
                COUNT(*) as cantidad_pagos,
                COALESCE(SUM(pago.monto_total), 0) as monto_total,
                COALESCE(AVG(pago.monto_total), 0) as promedio_por_pago
            ')
            ->groupBy('pago.metodo_pago')
            ->orderByDesc('monto_total')
            ->get()
            ->map(function ($metodo) {
                return [
                    'metodo_pago' => $metodo->metodo_pago,
                    'cantidad_pagos' => $metodo->cantidad_pagos ?? 0,
                    'monto_total' => floatval($metodo->monto_total ?? 0),
                    'promedio_por_pago' => floatval($metodo->promedio_por_pago ?? 0),
                ];
            });

        // Obtener lista de barberos y servicios para los filtros
        $barberos = Barbero::with('user:id,name')
            ->get()
            ->map(function ($barbero) {
                return [
                    'id_barbero' => $barbero->id_barbero,
                    'nombre' => $barbero->user->name ?? 'N/A',
                ];
            });

        $servicios = Servicio::where('estado', 'activo')
            ->get(['id_servicio', 'nombre']);

        return Inertia::render('Reportes/Index', [
            'pagosData' => $pagosData,
            'reservasData' => $reservasData,
            'ingresosMensuales' => $ingresosMensuales,
            'rankingBarberos' => $rankingBarberos,
            'serviciosMasPopulares' => $serviciosMasPopulares,
            'clientesFrecuentes' => $clientesFrecuentes,
            'distribucionEstados' => $distribucionEstados,
            'distribucionMetodosPago' => $distribucionMetodosPago,
            'barberos' => $barberos,
            'servicios' => $servicios,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'id_barbero' => $idBarbero,
                'id_servicio' => $idServicio,
                'metodo_pago' => $metodoPago,
                'estado_reserva' => $estadoReserva,
            ],
        ]);
    }
}
