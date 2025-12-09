<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('→ Creando servicios...');
        
        $servicios = [
            [
                'nombre' => 'Corte de Cabello Clásico',
                'descripcion' => 'Corte de cabello tradicional con tijera y máquina, incluye lavado y secado.',
                'duracion_minutos' => 30,
                'precio' => 15.00,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Corte Fade/Degradado',
                'descripcion' => 'Corte moderno con degradado (fade) bajo, medio o alto según preferencia del cliente.',
                'duracion_minutos' => 45,
                'precio' => 20.00,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Afeitado Clásico con Navaja',
                'descripcion' => 'Afeitado tradicional con navaja, incluye toallas calientes y bálsamo aftershave.',
                'duracion_minutos' => 30,
                'precio' => 12.00,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Arreglo de Barba',
                'descripcion' => 'Perfilado y recorte de barba, incluye aceite hidratante para barba.',
                'duracion_minutos' => 20,
                'precio' => 10.00,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Corte Infantil',
                'descripcion' => 'Corte de cabello para niños (hasta 12 años), ambiente amigable y rápido.',
                'duracion_minutos' => 25,
                'precio' => 10.00,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Combo Corte + Barba',
                'descripcion' => 'Servicio completo: corte de cabello + arreglo de barba, ideal para un cambio total de look.',
                'duracion_minutos' => 60,
                'precio' => 25.00,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Diseño de Cabello',
                'descripcion' => 'Diseños artísticos con máquina en el cabello (líneas, figuras, patrones).',
                'duracion_minutos' => 40,
                'precio' => 18.00,
                'estado' => 'activo',
            ]
        ];

        $count = 0;
        foreach ($servicios as $index => $servicio) {
            try {
                // Descargar imagen de Picsum
                $imagenUrl = null;
                try {
                    $imageUrl = 'https://picsum.photos/400/300?random=' . (200 + $index);
                    $imageContent = Http::timeout(10)->get($imageUrl)->body();
                    
                    // Guardar localmente
                    $filename = "servicio_{$index}.jpg";
                    Storage::disk('public')->put("servicios/{$filename}", $imageContent);
                    $imagenUrl = "/storage/servicios/{$filename}";
                } catch (\Exception $e) {
                    \Log::warning("Error descargando imagen para servicio: " . $e->getMessage());
                }

                Servicio::create([
                    'nombre' => $servicio['nombre'],
                    'descripcion' => $servicio['descripcion'],
                    'duracion_minutos' => $servicio['duracion_minutos'],
                    'precio' => $servicio['precio'],
                    'imagen' => $imagenUrl,
                    'estado' => $servicio['estado'],
                ]);
                $count++;
                $this->command->info("✓ Servicio creado: {$servicio['nombre']}");
            } catch (\Exception $e) {
                $this->command->error("✗ Error creando servicio {$servicio['nombre']}: " . $e->getMessage());
            }
        }

        $this->command->info("→ Total de servicios creados: {$count}");
    }
}
