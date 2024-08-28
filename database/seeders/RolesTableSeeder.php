<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ["super_admin", "admin", "collaborator", "client", "panel_user"];

        foreach ($roles as $role) {
            // Verifica si el rol ya existe antes de insertarlo
            if (!Role::where('name', $role)->where('guard_name', 'web')->exists()) {
                Role::create([
                    'name' => $role,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}