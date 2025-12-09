<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('→ Creando categorías...');
        
        $categorias = [
            [
                'nombre' => 'Cuidado del Cabello',
                'descripcion' => 'Productos para el cuidado y estilo del cabello.',
                'estado' => 'activa'
            ],
            [
                'nombre' => 'Afeitado Clásico',
                'descripcion' => 'Productos para afeitado tradicional con navaja.',
                'estado' => 'activa'
            ],
            [
                'nombre' => 'Cuidado de la Barba',
                'descripcion' => 'Productos para el mantenimiento y estilo de la barba.',
                'estado' => 'activa'
            ],
            [
                'nombre' => 'Productos Desechables',
                'descripcion' => 'Artículos de un solo uso para higiene y protección.',
                'estado' => 'activa'
            ]
        ];

        $count = 0;
        foreach ($categorias as $categoria) {
            try {
                Categoria::create($categoria);
                $count++;
                $this->command->info("✓ Categoría creada: {$categoria['nombre']}");
            } catch (\Exception $e) {
                $this->command->error("✗ Error creando categoría {$categoria['nombre']}: " . $e->getMessage());
            }
        }

        $this->command->info("→ Total de categorías creadas: {$count}");
    }
}
