<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles si no existen
        $roles = [
            'super_admin',
            'admin',
            'collaborator',
            'client',
            'panel_user'
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        // Crear permisos si no existen
        $permissions = [
            'view_invoice', 'view_any_invoice', 'create_invoice', 'update_invoice',
            'delete_invoice', 'delete_any_invoice', 'force_delete_invoice', 'force_delete_any_invoice',
            'view_activity', 'view_any_activity', 'create_activity', 'update_activity', 
            'delete_activity', 'delete_any_activity', 'force_delete_activity', 'force_delete_any_activity',
            // Añade más permisos aquí según tu aplicación
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Asignar todos los permisos a los roles super_admin y admin
        $superAdminRole = Role::findByName('super_admin');
        $adminRole = Role::findByName('admin');
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
        $adminRole->syncPermissions($allPermissions);

        // Asignar permisos específicos al rol collaborator
        $collaboratorRole = Role::findByName('collaborator');
        $collaboratorPermissions = [
            'view_invoice', 'create_invoice', 'update_invoice', 'delete_invoice'
            // Añade permisos según sea necesario
        ];
        $collaboratorRole->syncPermissions($collaboratorPermissions);

        // Asignar permisos específicos al rol client
        $clientRole = Role::findByName('client');
        $clientPermissions = [
            'view_invoice', 'view_any_invoice'
        ];
        $clientRole->syncPermissions($clientPermissions);

        // Asignar permisos específicos al rol panel_user
        $panelUserRole = Role::findByName('panel_user');
        $panelUserPermissions = [
            'view_invoice', 'view_any_invoice'
        ];
        $panelUserRole->syncPermissions($panelUserPermissions);
    }
}
