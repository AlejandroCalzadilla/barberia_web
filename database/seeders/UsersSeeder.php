<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Barbero;
use App\Models\Cliente;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Propietario
        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Propietario Demo',
                'password' => Hash::make('password'),
                'telefono' => '70000001',
                'direccion' => 'Calle Principal 123',
                'estado' => 'activo',
                'tipo_usuario' => 'propietario',
                'is_propietario' => true,
                'is_barbero' => false,
                'is_cliente' => false,
                'remember_token' => Str::random(10),
            ]
        );

        // Clientes
        $clientes = [
            ['name' => 'Juan Pérez', 'email' => 'juan.perez@example.com', 'telefono' => '70000006', 'direccion' => 'Calle 1 #2-3'],
            ['name' => 'María Gómez', 'email' => 'maria.gomez@example.com', 'telefono' => '70000007', 'direccion' => 'Carrera 4 #5-6'],
            ['name' => 'Carlos López', 'email' => 'carlos.lopez@example.com', 'telefono' => '70000008', 'direccion' => 'Av. 7 #8-9'],
            ['name' => 'Ana Torres', 'email' => 'ana.torres@example.com', 'telefono' => '70000009', 'direccion' => 'Calle 10 #11-12'],
            ['name' => 'Pedro Ramírez', 'email' => 'pedro.ramirez@example.com', 'telefono' => '70000010', 'direccion' => 'Carrera 13 #14-15'],
            ['name' => 'Laura Díaz', 'email' => 'laura.diaz@example.com', 'telefono' => '70000011', 'direccion' => 'Av. 16 #17-18'],
            ['name' => 'Andrés Sánchez', 'email' => 'andres.sanchez@example.com', 'telefono' => '70000012', 'direccion' => 'Calle 19 #20-21'],
            ['name' => 'Sofía Martínez', 'email' => 'sofia.martinez@example.com', 'telefono' => '70000013', 'direccion' => 'Carrera 22 #23-24'],
            ['name' => 'Diego Herrera', 'email' => 'diego.herrera@example.com', 'telefono' => '70000014', 'direccion' => 'Av. 25 #26-27'],
            ['name' => 'Valentina Rojas', 'email' => 'valentina.rojas@example.com', 'telefono' => '70000015', 'direccion' => 'Calle 28 #29-30'],
            ['name' => 'Jorge Castro', 'email' => 'jorge.castro@example.com', 'telefono' => '70000016', 'direccion' => 'Carrera 31 #32-33'],
            ['name' => 'Camila Vargas', 'email' => 'camila.vargas@example.com', 'telefono' => '70000017', 'direccion' => 'Av. 34 #35-36'],
            ['name' => 'Sebastián Ríos', 'email' => 'sebastian.rios@example.com', 'telefono' => '70000018', 'direccion' => 'Calle 37 #38-39'],
            ['name' => 'Isabella Gutiérrez', 'email' => 'isabella.gutierrez@example.com', 'telefono' => '70000019', 'direccion' => 'Carrera 40 #41-42'],
            ['name' => 'Daniel Morales', 'email' => 'daniel.morales@example.com', 'telefono' => '70000020', 'direccion' => 'Av. 43 #44-45'],
            ['name' => 'Valeria Castro', 'email' => 'valeria.castro@example.com', 'telefono' => '70000021', 'direccion' => 'Calle 46 #47-48'],
            ['name' => 'Alejandro Ruiz', 'email' => 'alejandro.ruiz@example.com', 'telefono' => '70000022', 'direccion' => 'Carrera 49 #50-51'],
            ['name' => 'Mariana Ortiz', 'email' => 'mariana.ortiz@example.com', 'telefono' => '70000023', 'direccion' => 'Av. 52 #53-54'],
            ['name' => 'Gabriel Méndez', 'email' => 'gabriel.mendez@example.com', 'telefono' => '70000024', 'direccion' => 'Calle 55 #56-57'],
            ['name' => 'Carolina Silva', 'email' => 'carolina.silva@example.com', 'telefono' => '70000025', 'direccion' => 'Carrera 58 #59-60']
        ];

        foreach ($clientes as $cliente) {
            $user = User::firstOrCreate(
                ['email' => $cliente['email']],
                [
                    'name' => $cliente['name'],
                    'password' => Hash::make('password'),
                    'telefono' => $cliente['telefono'],
                    'direccion' => $cliente['direccion'],
                    'estado' => 'activo',
                    'tipo_usuario' => 'cliente',
                    'is_propietario' => false,
                    'is_barbero' => false,
                    'is_cliente' => true,
                    'remember_token' => Str::random(10),
                ]
            );
            
            Cliente::firstOrCreate(
                ['id_usuario' => $user->id],
                [
                    
                    'fecha_nacimiento' => now()->subYears(rand(18, 60))->format('Y-m-d'),
                    'ci'=> rand(1000000, 9999999),
                ]
            );
        }

    }
}
