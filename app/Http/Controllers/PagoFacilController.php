<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Reserva;
use App\Models\Pago;
use App\Models\Servicio;
use App\Models\Barbero;
use App\Models\Cliente;
use Inertia\Inertia;
use GuzzleHttp\Client;
use Carbon\Carbon;

class PagoFacilController extends Controller
{
    /**
     * Estados de pago según PagoFácil
     * Estos valores pueden variar, ajusta según tu documentación específica
     */
    private const PAYMENT_STATUS_PENDING = 0;
    private const PAYMENT_STATUS_COMPLETED = 2;
    private const PAYMENT_STATUS_REJECTED = 3;

    /**
     * Mostrar la página de pago para una reserva
     */
    public function index(Request $request)
    {
        $reservaId = $request->query('reserva_id');
        if (!$reservaId) {    return redirect()->route('servicios.catalogo')->with('error', 'ID de reserva requerido.');
        }
        $reserva = Reserva::with(['cliente.user', 'barbero.user', 'servicio'])->findOrFail($reservaId);
        return Inertia::render('PagoFacil/Index', [
            'reserva' => $reserva
        ]);
    }

    /**
     * Generar QR para pago de reserva
     * Maneja tanto reservas nuevas como pagos finales pendientes
     */
    public function generarQR(Request $request)
    {
        try {
            Log::info('Inicio del método generarQR', ['request' => $request->all()]);
            
            $idPago = $request->integer('id_pago');
            $esPagoFinal = $request->boolean('es_pago_final', false);
            
            // ESCENARIO 1: Pago final pendiente (ya existe reserva y pago)
            if ($esPagoFinal && $idPago) {
                return $this->generarQRPagoFinal($request, $idPago);
            }
            
            // ESCENARIO 2: Nueva reserva desde catálogo (flujo original)
            $request->validate([
                'id_servicio' => 'required|exists:servicio,id_servicio',
                'id_barbero' => 'required|exists:barbero,id_barbero',
                'fecha_reserva' => 'required|date',
                'hora_inicio' => 'required|date_format:H:i',
                'metodo_pago' => 'required|in:qr,tigo_money',
                'tipo_pago' => 'required|in:pago_completo,anticipo',
                'monto' => 'required|numeric|min:0.01',
            ]);
            $servicio = Servicio::findOrFail($request->id_servicio);
            $barbero = Barbero::with('user')->findOrFail($request->id_barbero);
            $cliente = auth()->user()->cliente;
            if (!$cliente) {
                return response()->json(['success' => false, 'message' => 'No tienes un perfil de cliente asociado'], 400);
            }
            
            $tipoPago = $request->input('tipo_pago'); // 'pago_completo' o 'anticipo'
            $monto = $request->float('monto');
            
            Log::info('Datos de reserva cargados', [
                'servicio' => $servicio->nombre, 
                'barbero' => $barbero->user->name,
                'tipo_pago' => $tipoPago,
                'monto' => $monto
            ]);
            // Obtener token de autenticación
            $tokenResponse = $this->obtenerToken();
            Log::info('Token obtenido', ['tokenResponse' => $tokenResponse]);
            if (!isset($tokenResponse['values']['accessToken'])) {
                Log::error('No se pudo obtener un token válido', ['response' => $tokenResponse]);
                return response()->json(['success' => false, 'message' => 'No se pudo obtener un token válido'], 500);
            }
            $accessToken = $tokenResponse['values']['accessToken'];
            Log::info('Access token extraído correctamente', ['token' => substr($accessToken, 0, 20) . '...']);
            // Preparar datos de la reserva
            $horaInicio = Carbon::parse($request->hora_inicio);
            $horaFin = $horaInicio->copy()->addMinutes($servicio->duracion_minutos);
            $pedidoDetalle = $this->formatearDetallesReserva($servicio, $barbero, $request->fecha_reserva, $request->hora_inicio, $horaFin->format('H:i'));
            $nroPago = "reserva-" . time();
            $body = [
                "paymentMethod" => 4, // 4 = QR según tu código
                "clientName" => auth()->user()->name,
                "documentType" => 1,
                "documentId" => (string)($request->ci_nit ?? "0"),
                "phoneNumber" => (string)($request->telefono ?? "0"),
                "email" => auth()->user()->email,
                "paymentNumber" => $nroPago,
                "amount" => (float)$monto,
                "currency" => 2, // BOB
                "clientCode" => (string)auth()->user()->id,
                "callbackUrl" => config('pagofacil.callback_url'),
                "orderDetail" => $pedidoDetalle,
            ];
            Log::info('Cuerpo de la solicitud generado', ['body' => $body]);
            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken
            ];
            $client = new Client();
            $url = config('pagofacil.base_url') . '/generate-qr';
            Log::info('Enviando solicitud a PagoFácil', ['url' => $url]);
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $body
            ]);
            $responseContent = $response->getBody()->getContents();
            Log::info('Contenido crudo de la respuesta', ['response' => $responseContent]);
            $result = json_decode($responseContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error al decodificar JSON', [
                    'error_message' => json_last_error_msg(),
                    'response_content' => $responseContent
                ]);
                return response()->json(['success' => false, 'message' => 'Error al procesar la respuesta del servicio'], 500);
            }
            if (!isset($result['values'])) {
                Log::error('El campo values no está presente en la respuesta', ['result' => $result]);
                return response()->json(['success' => false, 'message' => 'Respuesta inesperada del servicio'], 500);
            }
            $values = $result['values'];
            Log::info('Estructura completa de values recibida', [
                'values_keys' => array_keys((array)$values),
                'values_type' => gettype($values),
                'values_content' => $values
            ]);
            $qrBase64 = $values['qrBase64'] ?? null;
            $transactionId = $values['transactionId'] ?? null;
            if (!$qrBase64 || !$transactionId) {
                Log::error('No se encontraron qrBase64 o transactionId en la respuesta', [
                    'values' => $values,
                    'qrBase64_encontrado' => !is_null($qrBase64),
                    'transactionId_encontrado' => !is_null($transactionId),
                    'todas_las_claves' => array_keys((array)$values)
                ]);
                return response()->json(['success' => false, 'message' => 'Error al obtener los datos del QR'], 500);
            }

            $qrImageBase64 = "data:image/png;base64," . $qrBase64;

            // Crear reserva primero (pendiente de pago)
            $reserva = Reserva::create([
                'id_cliente' => $cliente->id_cliente,
                'id_barbero' => $request->id_barbero,
                'id_servicio' => $request->id_servicio,
                'fecha_reserva' => $request->fecha_reserva,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $horaFin->format('H:i'),
                'precio_servicio' => $servicio->precio,
                'total' => $servicio->precio,
                'estado' => 'pendiente_pago',
            ]);

            // Crear registro del pago actual (completo o anticipo)
            $pago = Pago::create([
                'id_reserva' => $reserva->id_reserva,
                'monto_total' => $monto,
                'fecha_pago' => now(),
                'metodo_pago' => 'transferencia',
                'tipo_pago' => $tipoPago,
                'estado' => 'pendiente',
                'notas' => "Pago QR PagoFácil - {$nroPago} - TransactionID: {$transactionId}",
            ]);

            // Si es anticipo, crear el pago final pendiente
            if ($tipoPago === 'anticipo') {
                $montoFinal = $servicio->precio * 0.5;
                Pago::create([
                    'id_reserva' => $reserva->id_reserva,
                    'monto_total' => $montoFinal,
                    'fecha_pago' => now(),
                    'metodo_pago' => 'transferencia',
                    'tipo_pago' => 'pago_final',
                    'estado' => 'pendiente',
                    'notas' => 'Pago final pendiente (50% restante)',
                ]);
                
                Log::info('Pago final creado', [
                    'reserva_id' => $reserva->id_reserva,
                    'monto_final' => $montoFinal
                ]);
            }

            Log::info('QR y transaction ID generados correctamente', [
                'qrBase64' => substr($qrBase64, 0, 50) . '...',
                'transactionId' => $transactionId,
                'pago_id' => $pago->id_pago,
                'reserva_id' => $reserva->id_reserva,
                'tipo_pago' => $tipoPago
            ]);

            return response()->json([
                'success' => true,
                'qr_image' => $qrImageBase64,
                'transaction_id' => $transactionId,
                'nro_pago' => $nroPago,
                'reserva_id' => $reserva->id_reserva,
                'pago_id' => $pago->id_pago
            ]);
        } catch (\Throwable $th) {
            Log::error('Error en generarQR', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Generar QR para pago final pendiente (50% restante)
     */
    private function generarQRPagoFinal(Request $request, int $idPago)
    {
        try {
            Log::info('Generando QR para pago final', ['id_pago' => $idPago]);
            
            $pago = Pago::with(['reserva.servicio', 'reserva.barbero.user', 'reserva.cliente.user'])
                ->findOrFail($idPago);
            
            // Validaciones de seguridad
            $cliente = auth()->user()->cliente;
            if (!$cliente || $pago->reserva->cliente->id_usuario !== auth()->id()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
            }
            
            if ($pago->estado !== 'pendiente' || $pago->tipo_pago !== 'pago_final') {
                return response()->json(['success' => false, 'message' => 'Pago no disponible'], 400);
            }
            
            $reserva = $pago->reserva;
            $servicio = $reserva->servicio;
            $barbero = $reserva->barbero;
            
            // Obtener token
            $tokenResponse = $this->obtenerToken();
            if (!isset($tokenResponse['values']['accessToken'])) {
                Log::error('No se pudo obtener token', ['response' => $tokenResponse]);
                return response()->json(['success' => false, 'message' => 'Error de autenticación'], 500);
            }
            
            $accessToken = $tokenResponse['values']['accessToken'];
            
            // Preparar detalles del pago final
            $horaFin = Carbon::parse($reserva->hora_inicio)->addMinutes($servicio->duracion_minutos)->format('H:i');
            $pedidoDetalle = $this->formatearDetallesReserva(
                $servicio, 
                $barbero, 
                $reserva->fecha_reserva, 
                $reserva->hora_inicio, 
                $horaFin
            );
            
            $nroPago = "pago-final-{$pago->id_pago}-" . time();
            
            $body = [
                "paymentMethod" => 4, // QR
                "clientName" => auth()->user()->name,
                "documentType" => 1,
                "documentId" => (string)($request->ci_nit ?? "0"),
                "phoneNumber" => (string)($request->telefono ?? "0"),
                "email" => auth()->user()->email,
                "paymentNumber" => $nroPago,
                "amount" => (float)$pago->monto_total,
                "currency" => 2, // BOB
                "clientCode" => (string)auth()->user()->id,
                "callbackUrl" => config('pagofacil.callback_url'),
                "orderDetail" => $pedidoDetalle,
            ];
            
            Log::info('Cuerpo de solicitud pago final', ['body' => $body]);
            
            $client = new Client();
            $url = config('pagofacil.base_url') . '/generate-qr';
            
            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'json' => $body
            ]);
            
            $responseContent = $response->getBody()->getContents();
            $result = json_decode($responseContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE || !isset($result['values'])) {
                Log::error('Respuesta inválida', ['content' => $responseContent]);
                return response()->json(['success' => false, 'message' => 'Error al generar QR'], 500);
            }
            
            $values = $result['values'];
            $qrBase64 = $values['qrBase64'] ?? null;
            $transactionId = $values['transactionId'] ?? null;
            
            if (!$qrBase64 || !$transactionId) {
                Log::error('Datos QR incompletos', ['values' => $values]);
                return response()->json(['success' => false, 'message' => 'Error al obtener QR'], 500);
            }
            
            // Actualizar notas del pago con el nro_pago y transactionId
            $pago->update([
                'notas' => "Pago Final QR - {$nroPago} - TransactionID: {$transactionId}"
            ]);
            
            Log::info('QR pago final generado', [
                'pago_id' => $pago->id_pago,
                'transaction_id' => $transactionId
            ]);
            
            return response()->json([
                'success' => true,
                'qr_image' => "data:image/png;base64," . $qrBase64,
                'transaction_id' => $transactionId,
                'nro_pago' => $nroPago,
                'pago_id' => $pago->id_pago,
                'reserva_id' => $reserva->id_reserva
            ]);
            
        } catch (\Throwable $th) {
            Log::error('Error en generarQRPagoFinal', [
                'error' => $th->getMessage(),
                'line' => $th->getLine()
            ]);
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Consultar estado del pago
     * Retorna la estructura correcta según documentación de PagoFácil
     */

    // ...existing code...
    public function consultarEstado(Request $request)
    {
        set_time_limit(120);

        try {
            $transactionId = $request->input('transaction_id');

            if (!$transactionId) {
                return response()->json(['success' => false, 'message' => 'Transaction ID es requerido'], 400);
            }

            // 1. Obtener token con manejo de errores
            try {
                $tokenResponse = $this->obtenerToken();
            } catch (\Exception $e) {
                Log::error('Fallo al obtener token en consultarEstado', ['error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Error de conexión con pasarela'], 500);
            }

            if (!isset($tokenResponse['values']['accessToken'])) {
                return response()->json(['success' => false, 'message' => 'No se pudo autenticar con PagoFácil'], 500);
            }

            $accessToken = $tokenResponse['values']['accessToken'];
            $client = new Client();

            // 2. Realizar la petición con http_errors => false para evitar excepciones fatales
            $response = $client->post(config('pagofacil.base_url') . '/query-transaction', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'json' => [
                    'pagofacilTransactionId' => $transactionId // Asegurar que sea entero
                ],
                'http_errors' => false,
                'timeout' => 90,        // ✅ AUMENTADO: Esperar hasta 90s
                'connect_timeout' => 10 //
                // IMPORTANTE: Evita que Guzzle lance excepción en 4xx/5xx
            ]);

            $responseContent = $response->getBody()->getContents();
            $result = json_decode($responseContent, true);

            Log::info('Respuesta cruda consultarEstado', ['content' => $result]);

            // 3. Validar si la respuesta es válida (JSON mal formado o null)
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['success' => false, 'message' => 'Respuesta inválida del proveedor'], 500);
            }

            // 4. Validar errores lógicos de la API
            if (isset($result['error']) && $result['error'] != 0) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Error en la transacción'
                ], 400);
            }

            if (!isset($result['values'])) {
                return response()->json(['success' => false, 'message' => 'Datos no encontrados'], 404);
            }

            $values = $result['values'];

            // 5. Retornar datos seguros (usando null coalescing operator ??)
            return response()->json([
                'success' => true,
                'data' => [
                    'pagofacilTransactionId' => $values['pagofacilTransactionId'] ?? null,
                    'companyTransactionId' => $values['companyTransactionId'] ?? null,
                    'paymentStatus' => $values['paymentStatus'] ?? null, // Aquí vendrá el 5
                    'paymentDate' => $values['paymentDate'] ?? null,
                    'paymentTime' => $values['paymentTime'] ?? null,
                    // Agregamos descripción para depuración
                    'paymentStatusDescription' => $values['paymentStatusDescription'] ?? ''
                ],
                'message' => $result['message'] ?? 'Consulta realizada'
            ]);
        } catch (\Exception $e) {
            Log::error('Excepción crítica en consultarEstado', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }
// ...existing code...

    /**
     * Callback para notificaciones de Pago Fácil
     * Recibe notificaciones cuando se completa un pago
     */
    public function callback(Request $request)
    {
        try {
            Log::info('Callback recibido de Pago Fácil', ['data' => $request->all()]);

            // Validar que se recibieron todos los datos necesarios
            $pedidoId = $request->input('PedidoID');
            $fecha = $request->input('Fecha');
            $hora = $request->input('Hora');
            $metodoPago = $request->input('MetodoPago');
            $estado = $request->input('Estado');

            if (!$pedidoId) {
                Log::error('Callback sin PedidoID', ['data' => $request->all()]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => "PedidoID es requerido",
                    'values' => false
                ]);
            }

            Log::info('Buscando pago con referencia externa', ['pedido_id' => $pedidoId]);

            // Buscar pago por referencia externa (nro_pago)
            $pago = Pago::whereRaw("notas LIKE ?", ["%{$pedidoId}%"])->first();

            if (!$pago) {
                $todosPagos = Pago::select('id_pago', 'id_reserva', 'notas', 'estado')->get();

                Log::error('Pago no encontrado en base de datos', [
                    'pedido_id_buscado' => $pedidoId,
                    'callback_data' => $request->all(),
                    'pagos_existentes' => $todosPagos->toArray()
                ]);

                if (!$pago) {
                    return response()->json([
                        'error' => 1,
                        'status' => 0,
                        'message' => "Pago no encontrado en el sistema",
                        'values' => false
                    ]);
                }
            }

            // Procesar según el estado del pago
            // Nota: El campo 'Estado' del callback contiene el estado desde PagoFácil
            // Ajusta estos valores según lo que PagoFácil realmente devuelva
            $estadoInterno = $this->mapearEstadoPago($estado);

            Log::info('Estado mapeado', [
                'estado_pagofacil' => $estado,
                'estado_interno' => $estadoInterno
            ]);

            // Actualizar el pago en nuestra base de datos
            $pago->update([
                'estado' => $estadoInterno,
                'fecha_pago' => now(),
                'notas' => $pago->notas . ' | Confirmado: ' . now() . ' | Método PagoFácil: ' . $metodoPago
            ]);

            // Si el pago fue completado, actualizar también el estado de la reserva
            if ($estadoInterno === 'pagado') {
                // Para pago_final: actualizar la reserva a confirmada
                // Para pago_completo: ya se actualiza en el flujo normal
                $this->actualizarEstadoReserva($pago);
                
                Log::info('Reserva actualizada tras pago', [
                    'pago_id' => $pago->id_pago,
                    'tipo_pago' => $pago->tipo_pago,
                    'reserva_id' => $pago->id_reserva
                ]);
            }

            Log::info('Pago actualizado exitosamente desde callback', [
                'pago_id' => $pago->id_pago,
                'reserva_id' => $pago->id_reserva,
                'pedido_id' => $pedidoId,
                'estado_nuevo' => $estadoInterno,
                'metodo_pago' => $metodoPago,
                'fecha_pago' => $fecha . ' ' . $hora
            ]);

            // Respuesta exitosa según especificación de PagoFácil
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => "Pago procesado correctamente",
                'values' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Error en callback de PagoFácil', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => "No se pudo procesar el pago, por favor intente de nuevo",
                'values' => false
            ]);
        }
    }

    /**
     * Mapear estado de PagoFácil a estado interno
     * Estados válidos: pendiente, pagado, cancelado, reembolsado
     */
    private function mapearEstadoPago($estado)
    {
        $estadoLower = strtolower((string)$estado);

        // Estados completados
        if (
            $estadoLower === 'completado' ||
            $estadoLower === 'pagado' ||
            $estado === '1' ||
            $estado === 1 ||
            $estado === self::PAYMENT_STATUS_COMPLETED ||
            str_contains($estadoLower, 'procesado')
        ) {
            return 'pagado';
        }

        // Estados rechazados
        if (
            $estadoLower === 'rechazado' ||
            $estadoLower === 'cancelado' ||
            $estado === '3' ||
            $estado === 3 ||
            $estado === self::PAYMENT_STATUS_REJECTED
        ) {
            return 'cancelado';
        }

        // Estado por defecto: pendiente
        return 'pendiente';
    }

    /**
     * Página de retorno después del pago
     */
    public function return(Request $request)
    {
        $status = $request->query('status', 'pending');
        $nroPago = $request->query('nro_pago');
        $message = '';

        if ($status === 'success') {
            $message = 'Pago completado exitosamente. Tu reserva ha sido confirmada.';
        } elseif ($status === 'error') {
            $message = 'Hubo un error con el pago. Intenta nuevamente.';
        } else {
            $message = 'Pago pendiente de confirmación.';
        }

        return Inertia::render('PagoFacil/Return', [
            'status' => $status,
            'message' => $message,
            'nro_pago' => $nroPago,
            'redirect_url' => route('servicios.catalogo')
        ]);
    }

    /**
     * Obtener token de autenticación de Pago Fácil
     */
    private function obtenerToken()
    {
        try {
            $client = new Client();

            $response = $client->post(config('pagofacil.base_url') . '/login', [
                'headers' => [
                    'Accept' => 'application/json',
                    'tcTokenService' => config('pagofacil.token_service'),
                    'tcTokenSecret' => config('pagofacil.token_secret')
                ],
                'timeout' => config('pagofacil.timeout', 30)
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (config('pagofacil.enable_logs')) {
                Log::info('Token obtenido de Pago Fácil', ['response' => $result]);
            }

            return $result;
        } catch (\Exception $e) {
            if (config('pagofacil.enable_logs')) {
                Log::error('Error al obtener token de Pago Fácil', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
            throw new \Exception("Error al obtener el token: " . $e->getMessage());
        }
    }

    /**
     * Formatear detalles de la reserva para Pago Fácil
     */
    private function formatearDetallesReserva($servicio, $barbero, $fechaReserva, $horaInicio, $horaFin)
    {
        return [
            [
                'serial' => 1,
                'product' => $servicio->nombre . ' - ' . $barbero->user->name,
                'quantity' => 1,
                'price' => $servicio->precio,
                'discount' => 0,
                'total' => $servicio->precio
            ],
            [
                'serial' => 2,
                'product' => 'Fecha: ' . Carbon::parse($fechaReserva)->format('d/m/Y') . ' - Horario: ' . $horaInicio . ' a ' . $horaFin,
                'quantity' => 1,
                'price' => 0,
                'discount' => 0,
                'total' => 0
            ]
        ];
    }

    /**
     * Página de prueba para el callback (solo para desarrollo)
     */
    public function testCallback()
    {
        return Inertia::render('PagoFacil/CallbackTest');
    }

    /**
     * Método de debugging para ver los pagos en la base de datos
     */
    public function debugPagos(Request $request)
    {
        $pagos = Pago::with('reserva.servicio:id_servicio,nombre')
            ->select('id_pago', 'id_reserva', 'notas', 'estado', 'monto_total', 'fecha_pago')
            ->orderBy('id_pago', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'pagos' => $pagos->map(function ($pago) {
                return [
                    'id' => $pago->id_pago,
                    'reserva_id' => $pago->id_reserva,
                    'servicio' => $pago->reserva?->servicio?->nombre,
                    'notas' => $pago->notas,
                    'estado' => $pago->estado,
                    'monto' => $pago->monto_total,
                    'fecha' => $pago->fecha_pago,
                ];
            }),
            'total_pagos' => Pago::count(),
            'pagos_pendientes' => Pago::where('estado', 'pendiente')->count(),
            'pagos_completados' => Pago::where('estado', 'pagado')->count(),
        ]);
    }

    /**
     * Obtener estado de un pago por su transaction_id
     */
    public function obtenerEstadoPago(Request $request)
    {
        try {
            $transactionId = $request->input('transaction_id');

            if (!$transactionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction ID es requerida'
                ], 400);
            }

            // Buscar el pago por transaction_id en notas
            $pago = Pago::whereRaw("notas LIKE ?", ["%TransactionID: {$transactionId}%"])->first();

            if (!$pago) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pago no encontrado'
                ], 404);
            }

            // Consultar también el estado en PagoFácil
            $estadoPagoFacil = null;
            try {
                $tokenResponse = $this->obtenerToken();
                $accessToken = $tokenResponse['values']['accessToken'] ?? null;

                if ($accessToken) {
                    $client = new Client();
                    Log::info('Intentando consultar transacción en PagoFácil', [
                        'transaction_id' => $transactionId
                    ]);
                    $response = $client->post(config('pagofacil.base_url') . '/query-transaction', [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer ' . $accessToken
                        ],
                        'json' => [
                            'pagofacilTransactionId' => (int)$transactionId
                        ],
                        'http_errors' => false,
                        'timeout' => 90,
                        'connect_timeout' => 10
                    ]);
                    $result = json_decode($response->getBody()->getContents(), true);
                    $estadoPagoFacil = $result['values'] ?? null;

                    // Actualizar estado si cambió
                    if ($estadoPagoFacil && $pago->estado !== 'pagado') {
                        $paymentStatus = $estadoPagoFacil['paymentStatus'] ?? null;
                        if ($paymentStatus === 5 || $paymentStatus === '5') {
                            $pago->update(['estado' => 'pagado']);
                            $this->actualizarEstadoReserva($pago);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error consultando estado en PagoFácil', [
                    'transaction_id' => $transactionId,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'pago' => [
                    'id' => $pago->id_pago,
                    'reserva_id' => $pago->id_reserva,
                    'estado' => $pago->estado,
                    'monto' => $pago->monto_total,
                    'fecha' => $pago->fecha_pago,
                    'metodo_pago' => $pago->metodo_pago,
                    'notas' => $pago->notas
                ],
                'estado_pagofacil' => $estadoPagoFacil
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo estado de pago', [
                'transaction_id' => $request->input('transaction_id'),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Actualizar el estado de la reserva cuando se completa un pago
     */
    private function actualizarEstadoReserva($pago)
    {
        try {
            $reserva = Reserva::find($pago->id_reserva);

            if (!$reserva) {
                Log::warning('No se encontró reserva asociada al pago', [
                    'pago_id' => $pago->id_pago,
                    'id_reserva' => $pago->id_reserva
                ]);
                return false;
            }

            // Verificar si la reserva ya está confirmada
            if ($reserva->estado === 'confirmada') {
                Log::info('Reserva ya está marcada como confirmada', [
                    'reserva_id' => $reserva->id_reserva,
                    'pago_id' => $pago->id_pago
                ]);
                return true;
            }

            // Actualizar el estado de la reserva a confirmada
            $reserva->update(['estado' => 'confirmada']);

            Log::info('Reserva actualizada como confirmada', [
                'reserva_id' => $reserva->id_reserva,
                'pago_id' => $pago->id_pago,
                'estado_anterior' => $reserva->getOriginal('estado'),
                'estado_nuevo' => 'confirmada'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de la reserva', [
                'pago_id' => $pago->id_pago,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return false;
        }
    }
}