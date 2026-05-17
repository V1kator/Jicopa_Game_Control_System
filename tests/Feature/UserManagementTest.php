<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure roles exist
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'professor', 'guard_name' => 'web']);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create([
            'email' => 'admin@jicopa.local',
            'active' => true,
        ]);
        $admin->assignRole('admin');
        return $admin;
    }

    private function makeProfessor(): User
    {
        $professor = User::factory()->create([
            'email' => 'professor@jicopa.local',
            'active' => true,
        ]);
        $professor->assignRole('professor');
        return $professor;
    }

    public function test_admin_can_list_professors(): void
    {
        $admin = $this->makeAdmin();
        $this->makeProfessor();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_professor(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name'                  => 'Novo Professor',
            'email'                 => 'novoprof@jicopa.local',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'novoprof@jicopa.local')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->active);
        $this->assertTrue($user->hasRole('professor'));
    }

    public function test_professor_creation_requires_valid_data(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name'     => 'Missing Email Professor',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_can_deactivate_professor(): void
    {
        $admin = $this->makeAdmin();
        $professor = $this->makeProfessor();

        $this->assertTrue($professor->active);

        $response = $this->actingAs($admin)->delete('/admin/users/' . $professor->id);

        $response->assertRedirect(route('admin.users.index'));

        $professor->refresh();
        $this->assertFalse($professor->active);
        $this->assertDatabaseHas('users', ['id' => $professor->id]);
    }

    public function test_professor_cannot_access_user_management(): void
    {
        $professor = $this->makeProfessor();

        $response = $this->actingAs($professor)->get('/admin/users');

        $response->assertStatus(403);
    }
}
