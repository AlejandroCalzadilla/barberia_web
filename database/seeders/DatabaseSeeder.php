<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            UsersSeeder::class,
            BarberoSeeder::class,
            //ClienteSeeder::class,
            CategoriaSeeder::class,
            ProductoSeeder::class,
            HorarioSeeder::class,
            ServicioSeeder::class,
            ServicioProductoSeeder::class,
            ReservaSeeder::class,
            PagoSeeder::class,
             


        ]);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
