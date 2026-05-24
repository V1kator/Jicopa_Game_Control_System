<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Esporte;
use App\Models\Jogo;
use App\Models\Turma;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JogoManagementTest extends TestCase
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

    private function makeCategoria(string $name = 'Mirim'): Categoria
    {
        return Categoria::create(['name' => $name, 'active' => true]);
    }

    private function makeEsporte(string $type = 'coletivo', string $name = 'Futsal'): Esporte
    {
        return Esporte::create(['name' => $name, 'type' => $type, 'active' => true]);
    }

    private function makeTurma(string $name = '7A', string $period = 'Matutino'): Turma
    {
        return Turma::create(['name' => $name, 'period' => $period, 'active' => true]);
    }

    private function makeJogo(array $overrides = []): Jogo
    {
        $categoria = $overrides['categoria'] ?? $this->makeCategoria();
        $esporte = $overrides['esporte'] ?? $this->makeEsporte();
        $time1 = $overrides['time1'] ?? $this->makeTurma('7A', 'Matutino');
        $time2 = $overrides['time2'] ?? $this->makeTurma('7B', 'Matutino');

        return Jogo::create([
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'time1_id'     => $time1->id,
            'time2_id'     => $time2->id,
            'data'         => $overrides['data'] ?? Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => $overrides['hora'] ?? '14:00',
            'local'        => $overrides['local'] ?? 'Quadra Coberta',
            'cancelado'    => $overrides['cancelado'] ?? false,
        ]);
    }

    public function test_calendario_lista_todos_os_jogos_para_admin(): void
    {
        $admin = $this->makeAdmin();
        $this->makeJogo();
        $this->makeJogo([
            'time1' => $this->makeTurma('8A', 'Vespertino'),
            'time2' => $this->makeTurma('8B', 'Vespertino'),
            'hora'  => '16:00',
        ]);

        $response = $this->actingAs($admin)->get('/calendario');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('shared/Calendario')
            ->has('jogos', 2)
            ->has('categorias')
            ->has('esportes')
        );
    }

    public function test_calendario_filtra_por_data_categoria_esporte_e_cancelado(): void
    {
        $admin = $this->makeAdmin();
        $catA = $this->makeCategoria('Mirim');
        $catB = $this->makeCategoria('Infantil');
        $futsal = $this->makeEsporte('coletivo', 'Futsal');
        $volei  = $this->makeEsporte('coletivo', 'Vôlei');

        $jogoAlvo = $this->makeJogo([
            'categoria' => $catA,
            'esporte'   => $futsal,
            'data'      => Carbon::tomorrow()->format('Y-m-d'),
        ]);
        $this->makeJogo([
            'categoria' => $catB,
            'esporte'   => $volei,
            'data'      => Carbon::tomorrow()->addDay()->format('Y-m-d'),
            'time1'     => $this->makeTurma('9A', 'Matutino'),
            'time2'     => $this->makeTurma('9B', 'Matutino'),
        ]);

        $response = $this->actingAs($admin)->get(
            '/calendario?data=' . $jogoAlvo->data->format('Y-m-d')
            . '&categoria_id=' . $catA->id
            . '&esporte_id=' . $futsal->id
            . '&cancelado=false'
        );

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('shared/Calendario')
            ->has('jogos', 1)
            ->where('jogos.0.id', $jogoAlvo->id)
        );
    }

    public function test_calendario_acessivel_a_professor(): void
    {
        $professor = $this->makeProfessor();
        $this->makeJogo();

        $response = $this->actingAs($professor)->get('/calendario');

        $response->assertStatus(200);
    }

    public function test_admin_pode_criar_jogo_em_horario_livre(): void
    {
        $admin = $this->makeAdmin();
        $categoria = $this->makeCategoria();
        $esporte   = $this->makeEsporte();
        $time1     = $this->makeTurma('6A');
        $time2     = $this->makeTurma('6B');

        $response = $this->actingAs($admin)->post('/admin/jogos', [
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'time1_id'     => $time1->id,
            'time2_id'     => $time2->id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '14:00',
            'local'        => 'Quadra Coberta',
        ]);

        $response->assertRedirect(route('calendario.index'));
        $this->assertDatabaseHas('jogos', [
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'local'        => 'Quadra Coberta',
        ]);
    }

    public function test_criar_jogo_rejeita_data_no_passado(): void
    {
        $admin = $this->makeAdmin();
        $categoria = $this->makeCategoria();
        $esporte   = $this->makeEsporte();
        $time1     = $this->makeTurma('6A');
        $time2     = $this->makeTurma('6B');

        $response = $this->actingAs($admin)->post('/admin/jogos', [
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'time1_id'     => $time1->id,
            'time2_id'     => $time2->id,
            'data'         => Carbon::yesterday()->format('Y-m-d'),
            'hora'         => '14:00',
            'local'        => 'Quadra Coberta',
        ]);

        $response->assertSessionHasErrors(['data']);
        $this->assertDatabaseCount('jogos', 0);
    }

    public function test_criar_jogo_detecta_conflito_no_mesmo_horario_e_local(): void
    {
        $admin = $this->makeAdmin();
        $categoria = $this->makeCategoria();
        $esporte   = $this->makeEsporte();
        $time1     = $this->makeTurma('6A');
        $time2     = $this->makeTurma('6B');
        $time3     = $this->makeTurma('7A');
        $time4     = $this->makeTurma('7B');

        $existente = $this->makeJogo([
            'categoria' => $categoria,
            'esporte'   => $esporte,
            'time1'     => $time1,
            'time2'     => $time2,
            'data'      => Carbon::tomorrow()->format('Y-m-d'),
            'hora'      => '14:00',
            'local'     => 'Quadra Coberta',
        ]);

        $response = $this->actingAs($admin)->post('/admin/jogos', [
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'time1_id'     => $time3->id,
            'time2_id'     => $time4->id,
            'data'         => $existente->data->format('Y-m-d'),
            'hora'         => '14:30',
            'local'        => 'Quadra Coberta',
        ]);

        $response->assertSessionHasErrors(['conflict']);
        $this->assertDatabaseCount('jogos', 1);
    }

    public function test_force_create_aceita_jogo_em_conflito(): void
    {
        $admin = $this->makeAdmin();
        $categoria = $this->makeCategoria();
        $esporte   = $this->makeEsporte();
        $time1     = $this->makeTurma('6A');
        $time2     = $this->makeTurma('6B');
        $time3     = $this->makeTurma('7A');
        $time4     = $this->makeTurma('7B');

        $this->makeJogo([
            'categoria' => $categoria,
            'esporte'   => $esporte,
            'time1'     => $time1,
            'time2'     => $time2,
            'data'      => Carbon::tomorrow()->format('Y-m-d'),
            'hora'      => '14:00',
            'local'     => 'Quadra Coberta',
        ]);

        $response = $this->actingAs($admin)->post('/admin/jogos', [
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'time1_id'     => $time3->id,
            'time2_id'     => $time4->id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '14:30',
            'local'        => 'Quadra Coberta',
            'force_create' => true,
        ]);

        $response->assertRedirect(route('calendario.index'));
        $this->assertDatabaseCount('jogos', 2);
    }

    public function test_professor_pode_criar_jogo(): void
    {
        $professor = $this->makeProfessor();
        $categoria = $this->makeCategoria();
        $esporte   = $this->makeEsporte();
        $time1     = $this->makeTurma('6A');
        $time2     = $this->makeTurma('6B');

        $response = $this->actingAs($professor)->post('/admin/jogos', [
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'time1_id'     => $time1->id,
            'time2_id'     => $time2->id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '15:00',
            'local'        => 'Quadra Externa',
        ]);

        $response->assertRedirect(route('calendario.index'));
        $this->assertDatabaseCount('jogos', 1);
    }

    public function test_visitante_anonimo_nao_acessa_calendario(): void
    {
        $response = $this->get('/calendario');
        $response->assertRedirect('/login');
    }

    public function test_admin_pode_editar_jogo(): void
    {
        $admin = $this->makeAdmin();
        $jogo = $this->makeJogo();
        $novoLocal = 'Ginásio Municipal';

        $response = $this->actingAs($admin)->put('/admin/jogos/' . $jogo->id, [
            'categoria_id' => $jogo->categoria_id,
            'esporte_id'   => $jogo->esporte_id,
            'time1_id'     => $jogo->time1_id,
            'time2_id'     => $jogo->time2_id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '15:00',
            'local'        => $novoLocal,
            'cancelado'    => false,
        ]);

        $response->assertRedirect(route('calendario.index'));
        $this->assertDatabaseHas('jogos', [
            'id'    => $jogo->id,
            'local' => $novoLocal,
        ]);
    }

    public function test_admin_pode_excluir_jogo(): void
    {
        $admin = $this->makeAdmin();
        $jogo = $this->makeJogo();

        $response = $this->actingAs($admin)->delete('/admin/jogos/' . $jogo->id);

        $response->assertRedirect(route('calendario.index'));
        $this->assertDatabaseMissing('jogos', ['id' => $jogo->id]);
    }
}
