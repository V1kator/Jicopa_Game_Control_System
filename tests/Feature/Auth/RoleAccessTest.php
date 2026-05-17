<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'professor', 'guard_name' => 'web']);
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_professor_cannot_access_admin_routes(): void
    {
        $professor = User::factory()->create(['active' => true]);
        $professor->assignRole('professor');

        $response = $this->actingAs($professor)->get('/admin/users');

        $response->assertStatus(403);
    }
}
