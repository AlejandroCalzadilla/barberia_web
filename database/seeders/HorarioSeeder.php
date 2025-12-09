<?php

namespace Database\Seeders;

use App\Models\Barbero;
use App\Models\Horario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('→ Creando horarios...');
        
        // Obtener todos los barberos
        $barberos = Barbero::all();
        
        if ($barberos->isEmpty()) {
            $this->command->warn('No hay barberos disponibles. Ejecute BarberoSeeder primero.');
            return;
        }
        
        $diasSemana = [
            'lunes' => 'lunes',
            'martes' => 'martes',
            'miércoles' => 'miercoles',
            'jueves' => 'jueves',
            'viernes' => 'viernes',
            'sábado' => 'sabado',
        ];
        
        $horariosBase = [
            ['hora_inicio' => '09:00:00', 'hora_fin' => '13:00:00'],
            ['hora_inicio' => '14:00:00', 'hora_fin' => '18:00:00']
        ];
        
        $count = 0;
        
        foreach ($barberos as $barbero) {
            foreach ($diasSemana as $dia => $diaNumero) {
                foreach ($horariosBase as $horario) {
                    try {
                        Horario::create([
                            'id_barbero' => $barbero->id_barbero,
                            'dia_semana' => $diaNumero,
                            'hora_inicio' => $horario['hora_inicio'],
                            'hora_fin' => $horario['hora_fin'],
                            
                        ]);
                        $count++;
                    } catch (\Exception $e) {
                        $this->command->error("✗ Error creando horario para barbero ID {$barbero->id_barbero}: " . $e->getMessage());
                    }
                }
            }
            
            // Agregar un horario especial para el domingo (cerrado)
            try {
                // Para domingo, usamos un rango de tiempo válido (ejemplo: 1 minuto)
                // ya que la base de datos requiere que hora_fin > hora_inicio
                Horario::create([
                    'id_barbero' => $barbero->id_barbero,
                    'dia_semana' => 'domingo',
                    'hora_inicio' => '00:00:00',
                    'hora_fin' => '00:01:00',
                    'estado' => 'inactivo'  // Marcamos como inactivo para indicar que está cerrado
                ]);
                $count++;
            } catch (\Exception $e) {
                $this->command->error("✗ Error creando horario de domingo para barbero ID {$barbero->id_barbero}: " . $e->getMessage());
            }
        }
        
        $this->command->info("→ Total de horarios creados: {$count}");
    }
}
