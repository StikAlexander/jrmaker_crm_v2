<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
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

        // Crear permisos
        $permissions = [
            'view_activity', 'view_any_activity', 'create_activity', 'update_activity', 
            'restore_activity', 'restore_any_activity', 'replicate_activity', 'reorder_activity', 
            'delete_activity', 'delete_any_activity', 'force_delete_activity', 'force_delete_any_activity',
            'view_admin::user', 'view_any_admin::user', 'create_admin::user', 'update_admin::user',
            'restore_admin::user', 'restore_any_admin::user', 'replicate_admin::user', 'reorder_admin::user',
            'delete_admin::user', 'delete_any_admin::user', 'force_delete_admin::user', 'force_delete_any_admin::user',
            'view_banner', 'view_any_banner', 'create_banner', 'update_banner', 
            'restore_banner', 'restore_any_banner', 'replicate_banner', 'reorder_banner', 
            'delete_banner', 'delete_any_banner', 'force_delete_banner', 'force_delete_any_banner',
            // Más permisos según tu volcado...
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
            'view_banner', 'view_any_banner', 'create_banner', 'update_banner',
            'delete_banner', 'delete_any_banner'
            // Más permisos específicos para el colaborador...
        ];
        $collaboratorRole->syncPermissions($collaboratorPermissions);

        // Asignar permisos específicos al rol client
        $clientRole = Role::findByName('client');
        $clientPermissions = [
            'view_invoice', 'view_any_invoice', 'create_invoice', 'update_invoice',
            'delete_invoice', 'delete_any_invoice'
            // Más permisos específicos para el cliente...
        ];
        $clientRole->syncPermissions($clientPermissions);

        // Asignar permisos específicos al rol panel_user
        $panelUserRole = Role::findByName('panel_user');
        $panelUserPermissions = [
            'view_report', 'view_any_report'
            // Más permisos específicos para panel_user...
        ];
        $panelUserRole->syncPermissions($panelUserPermissions);
    }
}
