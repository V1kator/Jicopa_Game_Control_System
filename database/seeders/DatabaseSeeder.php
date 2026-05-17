<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Create roles
        \Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'professor', 'guard_name' => 'web']);

        // Create admin user
        $admin = User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@jicopa.local',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'active'   => true,
        ]);
        $admin->assignRole('admin');

        // Create test professor
        $professor = User::create([
            'name'     => 'Professor Teste',
            'email'    => 'professor@jicopa.local',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'active'   => true,
        ]);
        $professor->assignRole('professor');

        // Create initial scoring configuration (single row — updated by admin, never re-created)
        \App\Models\ScoringConfig::create([
            'points_per_win'   => 3,
            'points_per_draw'  => 1,
            'points_per_extra' => 1,
        ]);
    }
}
