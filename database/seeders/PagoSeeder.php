<?php

namespace Database\Seeders;

use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Database\Seeder;

class PagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('→ Creando pagos...');
        
        // Obtener reservas existentes
        $reservas = Reserva::all();
        
        if ($reservas->isEmpty()) {
            $this->command->warn('No hay reservas disponibles. Ejecute ReservaSeeder primero.');
            return;
        }
        
        $metodosPago = ['efectivo', 'tarjeta', 'transferencia', 'otro'];
        $estadosPago = ['pendiente', 'pagado', 'cancelado', 'reembolsado'];
        $tiposPago = ['anticipo', 'pago_final', 'pago_completo'];
        
        $count = 0;
        
        foreach ($reservas as $reserva) {
            try {
                // Crear un pago para cada reserva
                Pago::create([
                    'id_reserva' => $reserva->id_reserva,
                    'monto_total' => $reserva->total,
                    'metodo_pago' => $metodosPago[array_rand($metodosPago)],
                    'tipo_pago' => $tiposPago[array_rand($tiposPago)],
                    'estado' => $estadosPago[array_rand($estadosPago)],
                    'fecha_pago' => now()->subDays(rand(1, 30)),
                    
                ]);
                $count++;
                $this->command->info("✓ Pago creado para la reserva ID: {$reserva->id_reserva}");
            } catch (\Exception $e) {
                $this->command->error("✗ Error creando pago para reserva ID {$reserva->id_reserva}: " . $e->getMessage());
            }
        }
        
        $this->command->info("→ Total de pagos creados: {$count}");
    }
}
