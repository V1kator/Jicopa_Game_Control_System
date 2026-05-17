<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'professor', 'guard_name' => 'web']);
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'email'    => 'admin@jicopa.local',
            'password' => bcrypt('password'),
            'active'   => true,
        ]);
        $admin->assignRole('admin');

        $response = $this->post('/login', [
            'email'    => 'admin@jicopa.local',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email'    => 'admin@jicopa.local',
            'password' => bcrypt('password'),
            'active'   => true,
        ]);

        $response = $this->post('/login', [
            'email'    => 'admin@jicopa.local',
            'password' => 'wrong-password',
        ]);

        // Breeze session-based auth: returns redirect back with session errors
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
