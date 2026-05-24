<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Categoria;
use App\Models\Esporte;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ActiveFilterDefaultTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
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

    // ===================================================================
    // Turmas
    // ===================================================================

    private function seedTurmasMistas(): void
    {
        Turma::create(['name' => 'T1', 'period' => 'Matutino', 'active' => true]);
        Turma::create(['name' => 'T2', 'period' => 'Matutino', 'active' => false]);
    }

    public function test_turmas_index_default_oculta_inativas(): void
    {
        $this->seedTurmasMistas();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/turmas');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/turmas/Index')
            ->has('turmas', 1)
            ->where('turmas.0.name', 'T1')
            ->where('filters.active', 'true'));
    }

    public function test_turmas_index_active_false_mostra_apenas_inativas(): void
    {
        $this->seedTurmasMistas();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/turmas?active=false');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('turmas', 1)
            ->where('turmas.0.name', 'T2')
            ->where('filters.active', 'false'));
    }

    public function test_turmas_index_active_all_mostra_ativas_e_inativas(): void
    {
        $this->seedTurmasMistas();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/turmas?active=all');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('turmas', 2)
            ->where('filters.active', 'all'));
    }

    // ===================================================================
    // Alunos
    // ===================================================================

    private function seedAlunosMistos(): array
    {
        $turma = Turma::create(['name' => 'TA', 'period' => 'Matutino', 'active' => true]);
        Aluno::create(['name' => 'Ana', 'turma_id' => $turma->id, 'period' => 'Matutino', 'active' => true]);
        Aluno::create(['name' => 'Beto', 'turma_id' => $turma->id, 'period' => 'Matutino', 'active' => false]);
        return [$turma];
    }

    public function test_alunos_index_default_oculta_inativos(): void
    {
        $this->seedAlunosMistos();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/alunos');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/alunos/Index')
            ->has('alunos', 1)
            ->where('alunos.0.name', 'Ana')
            ->where('filters.active', 'true'));
    }

    public function test_alunos_index_active_false_mostra_apenas_inativos(): void
    {
        $this->seedAlunosMistos();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/alunos?active=false');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('alunos', 1)
            ->where('alunos.0.name', 'Beto')
            ->where('filters.active', 'false'));
    }

    public function test_alunos_index_active_all_mostra_ativos_e_inativos(): void
    {
        $this->seedAlunosMistos();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/alunos?active=all');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('alunos', 2)
            ->where('filters.active', 'all'));
    }

    // ===================================================================
    // Esportes
    // ===================================================================

    private function seedEsportesMistos(): void
    {
        Esporte::create(['name' => 'Futebol', 'type' => 'coletivo', 'active' => true]);
        Esporte::create(['name' => 'Xadrez', 'type' => 'individual', 'active' => false]);
    }

    public function test_esportes_index_default_oculta_inativos(): void
    {
        $this->seedEsportesMistos();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/esportes');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/esportes/Index')
            ->has('esportes', 1)
            ->where('esportes.0.name', 'Futebol')
            ->where('filters.active', 'true'));
    }

    public function test_esportes_index_active_false_mostra_apenas_inativos(): void
    {
        $this->seedEsportesMistos();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/esportes?active=false');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('esportes', 1)
            ->where('esportes.0.name', 'Xadrez')
            ->where('filters.active', 'false'));
    }

    public function test_esportes_index_active_all_mostra_ativos_e_inativos(): void
    {
        $this->seedEsportesMistos();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/esportes?active=all');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('esportes', 2)
            ->where('filters.active', 'all'));
    }

    // ===================================================================
    // Categorias
    // ===================================================================

    private function seedCategoriasMistas(): void
    {
        Categoria::create(['name' => 'Sub-15', 'active' => true]);
        Categoria::create(['name' => 'Sub-99', 'active' => false]);
    }

    public function test_categorias_index_default_oculta_inativas(): void
    {
        $this->seedCategoriasMistas();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/categorias');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/categorias/Index')
            ->has('categorias', 1)
            ->where('categorias.0.name', 'Sub-15')
            ->where('filters.active', 'true'));
    }

    public function test_categorias_index_active_false_mostra_apenas_inativas(): void
    {
        $this->seedCategoriasMistas();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/categorias?active=false');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('categorias', 1)
            ->where('categorias.0.name', 'Sub-99')
            ->where('filters.active', 'false'));
    }

    public function test_categorias_index_active_all_mostra_ativas_e_inativas(): void
    {
        $this->seedCategoriasMistas();
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/categorias?active=all');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('categorias', 2)
            ->where('filters.active', 'all'));
    }
}
