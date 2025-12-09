<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Barbero;
use App\Models\Servicio;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Horario;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    // ==================== REPORTES DE INGRESOS ====================

    /**
     * Obtiene los ingresos totales por mes
     */
    public function ingresosMensuales(Request $request)
    {
        $año = $request->input('año', now()->year);
        $mes = $request->input('mes', now()->month);

        $resultado = Pago::query()
            ->join('reserva', 'pago.id_reserva', '=', 'reserva.id_reserva')
            ->whereYear('reserva.created_at', $año)
            ->whereMonth('reserva.created_at', $mes)
            ->selectRaw('
                COUNT(DISTINCT reserva.id_reserva) as total_reservas,
                COUNT(DISTINCT CASE WHEN reserva.estado = "completada" THEN reserva.id_reserva END) as reservas_completadas,
                COALESCE(SUM(CASE WHEN pago.estado = "pagado" THEN pago.monto_total ELSE 0 END), 0) as ingresos_totales,
                COALESCE(SUM(CASE WHEN pago.tipo_pago = "anticipo" AND pago.estado = "pagado" THEN pago.monto_total ELSE 0 END), 0) as anticipos_recibidos,
                COALESCE(SUM(CASE WHEN pago.tipo_pago = "pago_completo" AND pago.estado = "pagado" THEN pago.monto_total ELSE 0 END), 0) as pagos_finales,
                COALESCE(SUM(CASE WHEN pago.estado = "pendiente" THEN pago.monto_total ELSE 0 END), 0) as pagos_pendientes
            ')
            ->first();

        // dd($resultado);   
        return response()->json([
            'año' => $año,
            'mes' => $mes,
            'total_reservas' => $resultado->total_reservas ?? 0,
            'reservas_completadas' => $resultado->reservas_completadas ?? 0,
            'ingresos_totales' => floatval($resultado->ingresos_totales ?? 0),
            'anticipos_recibidos' => floatval($resultado->anticipos_recibidos ?? 0),
            'pagos_finales' => floatval($resultado->pagos_finales ?? 0),
            'pagos_pendientes' => floatval($resultado->pagos_pendientes ?? 0),
        ]);
    }

    /**
     * Obtiene el resumen de ingresos de un rango de fechas
     */
    public function ingresosPorRango(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth());
        $fechaFin = $request->input('fecha_fin', now());

        $resultado = Pago::query()
            ->join('reserva', 'pago.id_reserva', '=', 'reserva.id_reserva')
            ->whereBetween('reserva.created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('
                COUNT(DISTINCT reserva.id_reserva) as total_reservas,
                COALESCE(SUM(CASE WHEN pago.estado = "pagado" THEN pago.monto_total ELSE 0 END), 0) as ingresos_totales,
                COALESCE(AVG(CASE WHEN pago.estado = "pagado" THEN pago.monto_total END), 0) as promedio_por_pago,
                COALESCE(MAX(CASE WHEN pago.estado = "pagado" THEN pago.monto_total END), 0) as pago_maximo,
                COALESCE(MIN(CASE WHEN pago.estado = "pagado" THEN pago.monto_total END), 0) as pago_minimo
            ')
            ->first();

        return response()->json([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'total_reservas' => $resultado->total_reservas ?? 0,
            'ingresos_totales' => floatval($resultado->ingresos_totales ?? 0),
            'promedio_por_pago' => floatval($resultado->promedio_por_pago ?? 0),
            'pago_maximo' => floatval($resultado->pago_maximo ?? 0),
            'pago_minimo' => floatval($resultado->pago_minimo ?? 0),
        ]);
    }

    // ==================== REPORTES DE BARBEROS ====================

    /**
     * Obtiene el ranking de barberos por cantidad de servicios realizados
     */
    public function rankingBarberos(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth());
        $fechaFin = $request->input('fecha_fin', now());

        $ranking = Barbero::query()
            ->with('user:id,name')
            ->leftJoin('reserva', 'barbero.id_barbero', '=', 'reserva.id_barbero')
            ->whereBetween('reserva.created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('
                barbero.id_barbero,
                barbero.especialidad,
                COUNT(DISTINCT reserva.id_reserva) as total_servicios,
                COUNT(DISTINCT CASE WHEN reserva.estado = "completada" THEN reserva.id_reserva END) as servicios_completados,
                COALESCE(SUM(CASE WHEN reserva.estado = "completada" THEN reserva.precio_servicio ELSE 0 END), 0) as ingresos_generados,
                COALESCE(AVG(CASE WHEN reserva.estado = "completada" THEN reserva.precio_servicio END), 0) as promedio_por_servicio
            ')
            ->groupBy('barbero.id_barbero', 'barbero.especialidad')
            ->orderByDesc('servicios_completados')
            ->orderByDesc('ingresos_generados')
            ->get()
            ->map(function ($barbero) {
                return [
                    'id_barbero' => $barbero->id_barbero,
                    'nombre' => $barbero->user->name ?? 'N/A',
                    'especialidad' => $barbero->especialidad,
                    'total_servicios' => $barbero->total_servicios ?? 0,
                    'servicios_completados' => $barbero->servicios_completados ?? 0,
                    'ingresos_generados' => floatval($barbero->ingresos_generados ?? 0),
                    'promedio_por_servicio' => floatval($barbero->promedio_por_servicio ?? 0),
                ];
            });

        return response()->json($ranking);
    }

    /**
     * Obtiene estadísticas individuales de un barbero
     */
    public function estadisticasBarbero(Request $request, $idBarbero)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth());
        $fechaFin = $request->input('fecha_fin', now());

        $stats = Reserva::query()
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('id_barbero', $idBarbero)
            ->selectRaw('
                COUNT(*) as total_reservas,
                COUNT(CASE WHEN estado = "completada" THEN 1 END) as completadas,
                COUNT(CASE WHEN estado = "cancelada" THEN 1 END) as canceladas,
                COUNT(CASE WHEN estado = "no_asistio" THEN 1 END) as no_asistio,
                COALESCE(SUM(CASE WHEN estado = "completada" THEN precio_servicio ELSE 0 END), 0) as ingresos_totales,
                COALESCE(AVG(CASE WHEN estado = "completada" THEN precio_servicio END), 0) as promedio_por_servicio,
                COUNT(DISTINCT id_cliente) as clientes_unicos
            ')
            ->first();

        $total = $stats->total_reservas ?? 0;
        $completadas = $stats->completadas ?? 0;

        return response()->json([
            'id_barbero' => $idBarbero,
            'total_reservas' => $total,
            'completadas' => $completadas,
            'canceladas' => $stats->canceladas ?? 0,
            'no_asistio' => $stats->no_asistio ?? 0,
            'ingresos_totales' => floatval($stats->ingresos_totales ?? 0),
            'promedio_por_servicio' => floatval($stats->promedio_por_servicio ?? 0),
            'clientes_unicos' => $stats->clientes_unicos ?? 0,
            'tasa_completadas' => $total > 0 ? number_format(($completadas / $total) * 100, 2) . '%' : '0%',
        ]);
    }

    // ==================== REPORTES DE SERVICIOS ====================

    /**
     * Obtiene los servicios más solicitados
     */
    public function serviciosMasPopulares(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth());
        $fechaFin = $request->input('fecha_fin', now());
        $limit = $request->input('limit', 5);

        $servicios = Servicio::query()
            ->leftJoin('reserva', 'servicio.id_servicio', '=', 'reserva.id_servicio')
            ->whereBetween('reserva.created_at', [$fechaInicio, $fechaFin])
            ->where('servicio.estado', 'activo')
            ->selectRaw('
                servicio.id_servicio,
                servicio.nombre,
                servicio.precio,
                servicio.duracion_minutos,
                COUNT(reserva.id_reserva) as veces_solicitado,
                COUNT(CASE WHEN reserva.estado = "completada" THEN 1 END) as veces_completado,
                COALESCE(SUM(CASE WHEN reserva.estado = "completada" THEN reserva.precio_servicio ELSE 0 END), 0) as ingresos_generados
            ')
            ->groupBy('servicio.id_servicio', 'servicio.nombre', 'servicio.precio', 'servicio.duracion_minutos')
            ->orderByDesc('veces_completado')
            ->orderByDesc('ingresos_generados')
            ->limit($limit)
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

        return response()->json($servicios);
    }

    // ==================== REPORTES DE CLIENTES ====================

    /**
     * Obtiene los clientes más frecuentes
     */
    public function clientesFrecuentes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth());
        $fechaFin = $request->input('fecha_fin', now());
        $limit = $request->input('limit', 10);

        $clientes = Cliente::query()
            ->with('user:id,name,email,phone')
            ->leftJoin('reserva', 'cliente.id_cliente', '=', 'reserva.id_cliente')
            ->whereBetween('reserva.created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('
                cliente.id_cliente,
                COUNT(reserva.id_reserva) as total_reservas,
                COUNT(CASE WHEN reserva.estado = "completada" THEN 1 END) as reservas_completadas,
                COALESCE(SUM(CASE WHEN reserva.estado = "completada" THEN reserva.precio_servicio ELSE 0 END), 0) as gasto_total,
                COALESCE(AVG(CASE WHEN reserva.estado = "completada" THEN reserva.precio_servicio END), 0) as promedio_por_visita,
                MAX(reserva.created_at) as ultima_visita
            ')
            ->groupBy('cliente.id_cliente')
            ->havingRaw('COUNT(reserva.id_reserva) > 0')
            ->orderByDesc('reservas_completadas')
            ->orderByDesc('gasto_total')
            ->limit($limit)
            ->get()
            ->map(function ($cliente) {
                return [
                    'id_cliente' => $cliente->id_cliente,
                    'nombre' => $cliente->user->name ?? 'N/A',
                    'email' => $cliente->user->email ?? 'N/A',
                    'telefono' => $cliente->user->phone ?? 'N/A',
                    'total_reservas' => $cliente->total_reservas ?? 0,
                    'reservas_completadas' => $cliente->reservas_completadas ?? 0,
                    'gasto_total' => floatval($cliente->gasto_total ?? 0),
                    'promedio_por_visita' => floatval($cliente->promedio_por_visita ?? 0),
                    'ultima_visita' => $cliente->ultima_visita,
                ];
            });

        return response()->json($clientes);
    }

    // ==================== REPORTES DE ESTADOS ====================

    /**
     * Obtiene distribución de estados de reservas
     */
    public function distribucionEstados(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth());
        $fechaFin = $request->input('fecha_fin', now());

        $distribucion = Reserva::query()
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('
                COUNT(*) as total_reservas,
                COUNT(CASE WHEN estado = "completada" THEN 1 END) as completadas,
                COUNT(CASE WHEN estado = "cancelada" THEN 1 END) as canceladas,
                COUNT(CASE WHEN estado = "no_asistio" THEN 1 END) as no_asistio,
                COUNT(CASE WHEN estado = "confirmada" THEN 1 END) as confirmadas
            ')
            ->first();

        $total = $distribucion->total_reservas ?? 0;

        return response()->json([
            'total_reservas' => $total,
            'completadas' => $distribucion->completadas ?? 0,
            'canceladas' => $distribucion->canceladas ?? 0,
            'no_asistio' => $distribucion->no_asistio ?? 0,
            'confirmadas' => $distribucion->confirmadas ?? 0,
            'porcentaje_completadas' => $total > 0 ? number_format((($distribucion->completadas ?? 0) / $total) * 100, 2) . '%' : '0%',
            'porcentaje_canceladas' => $total > 0 ? number_format((($distribucion->canceladas ?? 0) / $total) * 100, 2) . '%' : '0%',
            'porcentaje_no_asistio' => $total > 0 ? number_format((($distribucion->no_asistio ?? 0) / $total) * 100, 2) . '%' : '0%',
        ]);
    }

    // ==================== REPORTES DE PAGOS ====================

    /**
     * Obtiene distribución de métodos de pago
     */
    public function distribucionMetodosPago(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth());
        $fechaFin = $request->input('fecha_fin', now());

        $metodos = Pago::query()
            ->join('reserva', 'pago.id_reserva', '=', 'reserva.id_reserva')
            ->whereBetween('reserva.created_at', [$fechaInicio, $fechaFin])
            ->where('pago.estado', 'pagado')
            ->selectRaw('
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

        return response()->json($metodos);
    }

    // ==================== MÉTODOS ORIGINALES ====================

    public function pagosStats()
    {
        $pagos = Pago::where('estado', 'pagado')
            ->orderBy('fecha_pago')
            ->get(['id_pago', 'monto_total', 'fecha_pago', 'estado']);

        return $pagos;
    }

    public function reservasStats()
    {
        $reservas = Reserva::orderBy('id_reserva')
            ->get(['id_reserva', 'estado']);

       // dd($reservas);    
        return $reservas;
    }
}
