<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            'categorias',
            'productos',
            'servicios',
            'barberos',
            'clientes',
            'horarios',
            'reservas',
            'pagos',
            'usuarios',
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        $perms = [];
        foreach ($resources as $res) {
            foreach ($actions as $act) {
                $name = $res . '.' . $act;
                $perms[$name] = Permission::findOrCreate($name);
            }
        }

        $propietario = Role::findOrCreate('propietario');
        $barbero = Role::findOrCreate('barbero');
        $cliente = Role::findOrCreate('cliente');

        // propietario: todos los permisos
        $propietario->givePermissionTo(array_keys($perms));

        // barbero: ver servicios, ver/crear/actualizar reservas, ver horarios, ver/crear pagos
        $barberoPerms = [
            'servicios.view',
            'reservas.view',
            'horarios.view',
            'horarios.create',
            'horarios.update',
            'horarios.delete',
            'productos.view', 
        ];
        $barbero->givePermissionTo($barberoPerms);

        // cliente: ver/crear reservas, ver pagos
        $clientePerms = [
            'reservas.view','reservas.create',
            'pagos.view',
        ];
        $cliente->givePermissionTo($clientePerms);
    }
}
