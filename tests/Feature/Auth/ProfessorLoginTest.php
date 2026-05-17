<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfessorLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'professor', 'guard_name' => 'web']);
    }

    public function test_professor_can_login_with_valid_credentials(): void
    {
        $professor = User::factory()->create([
            'email'    => 'professor@jicopa.local',
            'password' => bcrypt('password'),
            'active'   => true,
        ]);
        $professor->assignRole('professor');

        $response = $this->post('/login', [
            'email'    => 'professor@jicopa.local',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($professor);
    }

    public function test_professor_login_fails_when_account_is_inactive(): void
    {
        $professor = User::factory()->create([
            'email'    => 'professor@jicopa.local',
            'password' => bcrypt('password'),
            'active'   => false,
        ]);
        $professor->assignRole('professor');

        $response = $this->post('/login', [
            'email'    => 'professor@jicopa.local',
            'password' => 'password',
        ]);

        // Breeze session-based auth: returns redirect back with session errors
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // Verify the error message contains "desativada"
        $errors = session('errors');
        $emailErrors = $errors ? $errors->get('email') : [];
        $this->assertNotEmpty($emailErrors);
        $this->assertStringContainsString('desativada', $emailErrors[0]);
    }
}
