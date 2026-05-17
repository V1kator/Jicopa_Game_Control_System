<?php

namespace Tests\Feature;

use App\Models\ScoringConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScoringConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'professor', 'guard_name' => 'web']);
        // Seed initial scoring config row
        ScoringConfig::create([
            'points_per_win'   => 3,
            'points_per_draw'  => 1,
            'points_per_extra' => 1,
        ]);
    }

    public function test_admin_can_view_scoring_config(): void
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/scoring-config');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/scoring-config/Index')
            ->has('config', fn ($config) => $config
                ->where('points_per_win', 3)
                ->where('points_per_draw', 1)
                ->where('points_per_extra', 1)
                ->etc()
            )
        );
    }

    public function test_admin_can_update_scoring_config(): void
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->put('/admin/scoring-config', [
            'points_per_win'   => 5,
            'points_per_draw'  => 2,
            'points_per_extra' => 1,
        ]);

        $response->assertRedirect(route('admin.scoring-config.index'));
        $this->assertDatabaseHas('scoring_configs', ['points_per_win' => 5, 'points_per_draw' => 2]);
    }

    public function test_scoring_config_rejects_zero_values(): void
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->put('/admin/scoring-config', [
            'points_per_win'   => 0,
            'points_per_draw'  => 1,
            'points_per_extra' => 1,
        ]);

        $response->assertSessionHasErrors(['points_per_win']);
    }

    public function test_scoring_config_rejects_negative_values(): void
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->put('/admin/scoring-config', [
            'points_per_win'   => -1,
            'points_per_draw'  => 1,
            'points_per_extra' => 1,
        ]);

        $response->assertSessionHasErrors(['points_per_win']);
    }

    public function test_professor_cannot_access_scoring_config(): void
    {
        $professor = User::factory()->create(['active' => true]);
        $professor->assignRole('professor');

        $response = $this->actingAs($professor)->get('/admin/scoring-config');

        $response->assertStatus(403);
    }
}
