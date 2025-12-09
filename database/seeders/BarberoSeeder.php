<?php

namespace Database\Seeders;

use App\Models\Barbero;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BarberoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos de ejemplo para barberos
        $barberos = [
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@barberia.com',
                'telefono' => '70000003',
                'direccion' => 'Av. Principal 456',
                'especialidad' => 'Cortes clásicos y afeitados tradicionales',
                'estado' => 'disponible'
            ],
            [
                'name' => 'Luis Rojas',
                'email' => 'luis.rojas@barberia.com',
                'telefono' => '70000004',
                'direccion' => 'Calle 10 #25-30',
                'especialidad' => 'Estilos modernos y diseños creativos',
                'estado' => 'disponible'
            ],
            [
                'name' => 'Miguel Ángel',
                'email' => 'miguel.angel@barberia.com',
                'telefono' => '70000005',
                'direccion' => 'Carrera 15 #20-45',
                'especialidad' => 'Especialista en barba y bigote',
                'estado' => 'disponible'
            ]
        ];

        foreach ($barberos as $index => $barberoData) {
            // Crear usuario para el barbero
            $user = User::firstOrCreate(
                ['email' => $barberoData['email']],
                [
                    'name' => $barberoData['name'],
                    'password' => Hash::make('password'),
                    'telefono' => $barberoData['telefono'],
                    'direccion' => $barberoData['direccion'],
                    'estado' => 'activo',
                    'tipo_usuario' => 'barbero',
                    'is_propietario' => false,
                    'is_barbero' => true,
                    'is_cliente' => false,
                    'remember_token' => Str::random(10),
                ]
            );

            // Descargar imagen de Picsum
            try {
                $imageUrl = 'https://picsum.photos/400/400?random=' . (100 + $index);
                $imageContent = Http::timeout(10)->get($imageUrl)->body();
                
                // Guardar localmente
                $filename = "barbero_{$user->id}.jpg";
                Storage::disk('public')->put("barberos/{$filename}", $imageContent);
                $fotoPerfil = "/storage/barberos/{$filename}";
            } catch (\Exception $e) {
                \Log::warning("Error descargando imagen para barbero: " . $e->getMessage());
                $fotoPerfil = null;
            }

            // Crear registro en la tabla barberos
            Barbero::create([
                'id_usuario' => $user->id,
                'especialidad' => $barberoData['especialidad'],
                'foto_perfil' => $fotoPerfil,
                'estado' => $barberoData['estado'] ?? 'disponible'
            ]);
        }
    }
}
