<?php

namespace Database\Seeders;

use App\Models\Barbero;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('→ Creando reservas...');
        
        // Obtener clientes, barberos y servicios
        $clientes = Cliente::all();
        $barberos = Barbero::all();
        $servicios = Servicio::all();
        
        if ($clientes->isEmpty() || $barberos->isEmpty() || $servicios->isEmpty()) {
            $this->command->warn('No hay suficientes clientes, barberos o servicios. Ejecute los seeders correspondientes primero.');
            return;
        }
        
        $estados = ['pendiente_pago', 'confirmada', 'en_proceso', 'completada', 'cancelada', 'no_asistio'];
        $count = 0;
        
        // Crear reservas para las próximas 2 semanas
        for ($i = 0; $i < 20; $i++) {
            try {
                $fecha = Carbon::now()->addDays(rand(0, 14));
                $horaInicio = Carbon::createFromTime(rand(9, 16), rand(0, 1) * 30, 0);
                
                $cliente = $clientes->random();
                $barbero = $barberos->random();
                $servicio = $servicios->random();
                $estado = $estados[array_rand($estados)];
                
                // Calcular hora de fin basada en la duración del servicio
                $horaFin = (clone $horaInicio)->addMinutes($servicio->duracion_minutos);
                

                $reserva = Reserva::create([
                    'id_cliente' => $cliente->id_cliente,
                    'id_barbero' => $barbero->id_barbero,
                    'id_servicio' => $servicio->id_servicio,
                    'fecha_reserva' => $fecha->format('Y-m-d'),
                    'hora_inicio' => $horaInicio->format('H:i:s'),
                    'hora_fin' => $horaFin->format('H:i:s'),
                    'estado' => $estado,
                    'notas' => rand(0, 1) ? 'Notas adicionales para la reserva' : null,
                    'total' => $servicio->precio,
                    'monto_anticipo' =>  round($servicio->precio * 0.5, 2) 
                    
                ]);
                
                $count++;
                $this->command->info("✓ Reserva #{$count} creada para el cliente ID {$cliente->id_cliente} con el barbero ID {$barbero->id_barbero}");
                
            } catch (\Exception $e) {
                $this->command->error("✗ Error creando reserva: " . $e->getMessage());
            }
        }
        
        $this->command->info("→ Total de reservas creadas: {$count}");
    }
}
