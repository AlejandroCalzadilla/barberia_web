<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Servicio;
use App\Models\ServicioProducto;
use Illuminate\Database\Seeder;

class ServicioProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void

    {
        $this->command->info('→ Creando relaciones entre servicios y productos...');
        
        // Obtener servicios y productos
        $servicios = Servicio::all();
        $productos = Producto::all();
        
        if ($servicios->isEmpty() || $productos->isEmpty()) {
            $this->command->warn('No hay suficientes servicios o productos. Ejecute ServicioSeeder y ProductoSeeder primero.');
            return;
        }
        
        $count = 0;
        
        // Asignar productos a servicios de manera aleatoria
        foreach ($servicios as $servicio) {
            // Cada servicio tendrá entre 1 y 3 productos asociados
            $numProductos = rand(1, min(3, $productos->count()));
            $productosAleatorios = $productos->random($numProductos);
            
            foreach ($productosAleatorios as $producto) {
                try {
                    // Verificar si ya existe esta relación para evitar duplicados
                    $existe = ServicioProducto::where('id_servicio', $servicio->id_servicio)
                        ->where('id_producto', $producto->id_producto)
                        ->exists();
                    
                    if (!$existe) {
                        ServicioProducto::create([
                            'id_servicio' => $servicio->id_servicio,
                            'id_producto' => $producto->id_producto,
                            'cantidad' => rand(1, 3), // Cantidad aleatoria entre 1 y 3
                           ]);
                        $count++;
                        $this->command->info("✓ Relación creada: Servicio '{$servicio->nombre}' con producto '{$producto->nombre}'");
                    }
                } catch (\Exception $e) {
                    $this->command->error("✗ Error creando relación: " . $e->getMessage());
                }
            }
        }
        
        $this->command->info("→ Total de relaciones creadas: {$count}");
    }
}
