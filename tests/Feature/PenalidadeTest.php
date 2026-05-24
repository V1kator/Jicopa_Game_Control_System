<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Penalidade;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PenalidadeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'professor', 'guard_name' => 'web']);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->assignRole('admin');
        return $admin;
    }

    private function makeProfessor(): User
    {
        $professor = User::factory()->create(['active' => true]);
        $professor->assignRole('professor');
        return $professor;
    }

    private function makeTurma(string $name = '6A'): Turma
    {
        return Turma::create(['name' => $name, 'period' => 'Matutino', 'active' => true]);
    }

    private function makeAluno(Turma $turma, string $name = 'Ana'): Aluno
    {
        return Aluno::create([
            'name'     => $name,
            'turma_id' => $turma->id,
            'period'   => 'Matutino',
            'active'   => true,
        ]);
    }

    public function test_admin_pode_aplicar_penalidade_a_turma(): void
    {
        $admin = $this->makeAdmin();
        $turma = $this->makeTurma();

        $response = $this->actingAs($admin)->post('/penalidades', [
            'tipo'     => 'turma',
            'turma_id' => $turma->id,
            'motivo'   => 'Atraso no início do jogo',
            'pontos'   => 2,
        ]);

        $response->assertRedirect(route('penalidades.index'));
        $this->assertDatabaseHas('penalidades', [
            'tipo'           => 'turma',
            'turma_id'       => $turma->id,
            'pontos'         => 2,
            'registrado_por' => $admin->id,
        ]);
    }

    public function test_admin_pode_aplicar_penalidade_a_aluno(): void
    {
        $admin = $this->makeAdmin();
        $turma = $this->makeTurma();
        $aluno = $this->makeAluno($turma);

        $response = $this->actingAs($admin)->post('/penalidades', [
            'tipo'     => 'aluno',
            'aluno_id' => $aluno->id,
            'motivo'   => 'Conduta antidesportiva',
            'pontos'   => 1,
        ]);

        $response->assertRedirect(route('penalidades.index'));
        $this->assertDatabaseHas('penalidades', [
            'tipo'           => 'aluno',
            'aluno_id'       => $aluno->id,
            'pontos'         => 1,
            'registrado_por' => $admin->id,
        ]);
    }

    public function test_penalidade_exige_motivo_e_pontos(): void
    {
        $admin = $this->makeAdmin();
        $turma = $this->makeTurma();

        $response = $this->actingAs($admin)->post('/penalidades', [
            'tipo'     => 'turma',
            'turma_id' => $turma->id,
        ]);

        $response->assertSessionHasErrors(['motivo', 'pontos']);
        $this->assertDatabaseCount('penalidades', 0);
    }

    public function test_penalidade_rejeita_pontos_zero_ou_negativos(): void
    {
        $admin = $this->makeAdmin();
        $turma = $this->makeTurma();

        $response = $this->actingAs($admin)->post('/penalidades', [
            'tipo'     => 'turma',
            'turma_id' => $turma->id,
            'motivo'   => 'Teste',
            'pontos'   => 0,
        ]);

        $response->assertSessionHasErrors(['pontos']);
    }

    public function test_penalidade_turma_exige_turma_id(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post('/penalidades', [
            'tipo'   => 'turma',
            'motivo' => 'Teste',
            'pontos' => 1,
        ]);

        $response->assertSessionHasErrors(['turma_id']);
    }

    public function test_penalidade_aluno_exige_aluno_id(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post('/penalidades', [
            'tipo'   => 'aluno',
            'motivo' => 'Teste',
            'pontos' => 1,
        ]);

        $response->assertSessionHasErrors(['aluno_id']);
    }

    public function test_request_nao_pode_sobrescrever_registrado_por(): void
    {
        $admin = $this->makeAdmin();
        $outro = User::factory()->create(['active' => true]);
        $turma = $this->makeTurma();

        $this->actingAs($admin)->post('/penalidades', [
            'tipo'           => 'turma',
            'turma_id'       => $turma->id,
            'motivo'         => 'Atraso',
            'pontos'         => 1,
            'registrado_por' => $outro->id, // tentativa de spoofing
        ]);

        $this->assertDatabaseHas('penalidades', [
            'turma_id'       => $turma->id,
            'registrado_por' => $admin->id, // forçado pelo controller via Auth::id()
        ]);
        $this->assertDatabaseMissing('penalidades', [
            'turma_id'       => $turma->id,
            'registrado_por' => $outro->id,
        ]);
    }

    public function test_admin_pode_atualizar_penalidade_sem_trocar_registrado_por(): void
    {
        $admin = $this->makeAdmin();
        $outroAdmin = User::factory()->create(['active' => true]);
        $outroAdmin->assignRole('admin');
        $turma = $this->makeTurma();

        $penalidade = Penalidade::create([
            'tipo'           => 'turma',
            'turma_id'       => $turma->id,
            'motivo'         => 'Motivo original',
            'pontos'         => 1,
            'registrado_por' => $outroAdmin->id,
        ]);

        $response = $this->actingAs($admin)->put('/penalidades/' . $penalidade->id, [
            'tipo'     => 'turma',
            'turma_id' => $turma->id,
            'motivo'   => 'Motivo corrigido',
            'pontos'   => 3,
        ]);

        $response->assertRedirect(route('penalidades.index'));
        $this->assertDatabaseHas('penalidades', [
            'id'             => $penalidade->id,
            'motivo'         => 'Motivo corrigido',
            'pontos'         => 3,
            'registrado_por' => $outroAdmin->id, // não muda
        ]);
    }

    public function test_admin_pode_remover_penalidade(): void
    {
        $admin = $this->makeAdmin();
        $turma = $this->makeTurma();

        $penalidade = Penalidade::create([
            'tipo'           => 'turma',
            'turma_id'       => $turma->id,
            'motivo'         => 'X',
            'pontos'         => 1,
            'registrado_por' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete('/penalidades/' . $penalidade->id);

        $response->assertRedirect(route('penalidades.index'));
        $this->assertDatabaseMissing('penalidades', ['id' => $penalidade->id]);
    }

    public function test_professor_pode_aplicar_penalidade(): void
    {
        $professor = $this->makeProfessor();
        $turma = $this->makeTurma();

        $response = $this->actingAs($professor)->post('/penalidades', [
            'tipo'     => 'turma',
            'turma_id' => $turma->id,
            'motivo'   => 'Atraso',
            'pontos'   => 1,
        ]);

        $response->assertRedirect(route('penalidades.index'));
        $this->assertDatabaseHas('penalidades', [
            'turma_id'       => $turma->id,
            'registrado_por' => $professor->id,
        ]);
    }
}
