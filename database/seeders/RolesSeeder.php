<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'propietario',
            'barbero',
            'cliente',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }
    }
}
