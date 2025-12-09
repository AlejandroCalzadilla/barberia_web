<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('→ Creando productos...');
        
        // Obtener categorías
        $cuidadoCabello = Categoria::where('nombre', 'Cuidado del Cabello')->first();
        $afeitado = Categoria::where('nombre', 'Afeitado Clásico')->first();
        $cuidadoBarba = Categoria::where('nombre', 'Cuidado de la Barba')->first();
        $desechables = Categoria::where('nombre', 'Productos Desechables')->first();
        
        if (!$cuidadoCabello || !$afeitado || !$cuidadoBarba || !$desechables) {
            $this->command->error('✗ No se encontraron todas las categorías necesarias. Ejecute CategoriaSeeder primero.');
            return;
        }
        
        $productos = [
            // Cuidado del Cabello
            [
                'id_categoria' => $cuidadoCabello->id_categoria,
                'codigo' => 'GEL001',
                'nombre' => 'Gel fijador',
                'descripcion' => 'Gel fijador de alta duración para peinados.',
                'precio_compra' => 3.50,
                'precio_venta' => 7.00,
                'stock_actual' => 100,
                'stock_minimo' => 5,
                'unidad_medida' => 'unidad',
                'estado' => 'activo',
            ],
            [
                'id_categoria' => $cuidadoCabello->id_categoria,
                'codigo' => 'SHM001',
                'nombre' => 'Shampoo nutritivo',
                'descripcion' => 'Shampoo nutritivo para todo tipo de cabello.',
                'precio_compra' => 4.00,
                'precio_venta' => 8.50,
                'stock_actual' => 50,
                'stock_minimo' => 5,
                'unidad_medida' => 'botella',
                'estado' => 'activo',
            ],
            
            // Cuidado de la Barba
            [
                'id_categoria' => $cuidadoBarba->id_categoria,
                'codigo' => 'ACE001',
                'nombre' => 'Aceite para barba',
                'descripcion' => 'Aceite hidratante para barba con aroma a madera de cedro.',
                'precio_compra' => 5.00,
                'precio_venta' => 12.00,
                'stock_actual' => 30,
                'stock_minimo' => 3,
                'unidad_medida' => 'frasco',
                'estado' => 'activo',
            ],
            
            // Afeitado Clásico
            [
                'id_categoria' => $afeitado->id_categoria,
                'codigo' => 'NAV001',
                'nombre' => 'Navaja de afeitar',
                'descripcion' => 'Navaja de afeitar profesional de acero inoxidable.',
                'precio_compra' => 15.00,
                'precio_venta' => 30.00,
                'stock_actual' => 20,
                'stock_minimo' => 2,
                'unidad_medida' => 'unidad',
                'estado' => 'activo',
            ],
            
            // Productos Desechables
            [
                'id_categoria' => $desechables->id_categoria,
                'codigo' => 'TOA001',
                'nombre' => 'Toallas desechables',
                'descripcion' => 'Paquete con 50 toallas desechables para uso en barbería.',
                'precio_compra' => 3.00,
                'precio_venta' => 6.00,
                'stock_actual' => 200,
                'stock_minimo' => 20,
                'unidad_medida' => 'paquete',
                'estado' => 'activo',
            ]
        ];
        
        $count = 0;
        foreach ($productos as $index => $producto) {
            try {
                // Descargar imagen de Picsum
                $imagenUrl = null;
                try {
                    $imageUrl = 'https://picsum.photos/400/300?random=' . (300 + $index);
                    $imageContent = Http::timeout(10)->get($imageUrl)->body();
                    
                    // Guardar localmente
                    $filename = "producto_{$index}.jpg";
                    Storage::disk('public')->put("productos/{$filename}", $imageContent);
                    $imagenUrl = "/storage/productos/{$filename}";
                } catch (\Exception $e) {
                    \Log::warning("Error descargando imagen para producto: " . $e->getMessage());
                }

                Producto::create([
                    'id_categoria' => $producto['id_categoria'],
                    'codigo' => $producto['codigo'],
                    'nombre' => $producto['nombre'],
                    'descripcion' => $producto['descripcion'],
                    'precio_compra' => $producto['precio_compra'],
                    'precio_venta' => $producto['precio_venta'],
                    'stock_actual' => $producto['stock_actual'],
                    'stock_minimo' => $producto['stock_minimo'],
                    'unidad_medida' => $producto['unidad_medida'],
                    'imagenurl' => $imagenUrl,
                    'estado' => $producto['estado'],
                ]);
                $count++;
                $this->command->info("✓ Producto creado: {$producto['nombre']}");
            } catch (\Exception $e) {
                $this->command->error("✗ Error creando producto {$producto['nombre']}: " . $e->getMessage());
            }
        }
        
        $this->command->info("→ Total de productos creados: {$count}");
    }
}
