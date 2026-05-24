<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Categoria;
use App\Models\Esporte;
use App\Models\Jogo;
use App\Models\Penalidade;
use App\Models\ResultadoIndividual;
use App\Models\ScoringConfig;
use App\Models\Turma;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'professor', 'guard_name' => 'web']);
        ScoringConfig::create([
            'points_per_win'   => 3,
            'points_per_draw'  => 1,
            'points_per_extra' => 1,
        ]);
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

    private function makeCategoriaComTurmas(string $catName, array $turmaNames): array
    {
        $categoria = Categoria::create(['name' => $catName, 'active' => true]);
        $turmas = collect($turmaNames)->mapWithKeys(function ($name) {
            return [$name => Turma::create(['name' => $name, 'period' => 'Matutino', 'active' => true])];
        });
        $categoria->turmas()->attach($turmas->pluck('id')->toArray());
        return ['categoria' => $categoria, 'turmas' => $turmas];
    }

    private function jogoColetivoFinalizado(Categoria $cat, Esporte $esp, Turma $t1, Turma $t2, int $p1, int $p2, string $hora = '14:00', string $local = 'Quadra A'): Jogo
    {
        $vencedor = null;
        if ($p1 > $p2) $vencedor = $t1->id;
        elseif ($p2 > $p1) $vencedor = $t2->id;

        return Jogo::create([
            'categoria_id' => $cat->id,
            'esporte_id'   => $esp->id,
            'time1_id'     => $t1->id,
            'time2_id'     => $t2->id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => $hora,
            'local'        => $local,
            'placar_time1' => $p1,
            'placar_time2' => $p2,
            'vencedor_id'  => $vencedor,
        ]);
    }

    public function test_ranking_consolida_vitorias_e_empates_em_jogos_coletivos(): void
    {
        $admin = $this->makeAdmin();
        $futsal = Esporte::create(['name' => 'Futsal', 'type' => 'coletivo', 'active' => true]);
        $cenario = $this->makeCategoriaComTurmas('Mirim', ['A', 'B']);
        $cat = $cenario['categoria'];
        $A = $cenario['turmas']['A'];
        $B = $cenario['turmas']['B'];

        $this->jogoColetivoFinalizado($cat, $futsal, $A, $B, 3, 1); // A vence

        $response = $this->actingAs($admin)->get('/ranking?categoria_id=' . $cat->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ranking/Index')
            ->where('ranking.0.turma.id', $A->id)
            ->where('ranking.0.score.total', 3)
            ->where('ranking.0.score.vitorias', 1)
            ->where('ranking.1.turma.id', $B->id)
            ->where('ranking.1.score.total', 0)
            ->where('ranking.1.score.derrotas', 1)
        );
    }

    public function test_ranking_ordena_por_total_e_desempata_por_saldo_de_gols(): void
    {
        $admin = $this->makeAdmin();
        $futsal = Esporte::create(['name' => 'Futsal', 'type' => 'coletivo', 'active' => true]);
        $cenario = $this->makeCategoriaComTurmas('Mirim', ['A', 'B', 'C']);
        $cat = $cenario['categoria'];
        $A = $cenario['turmas']['A'];
        $B = $cenario['turmas']['B'];
        $C = $cenario['turmas']['C'];

        // A e B vencem 1 jogo cada (3 pts cada), mas A tem saldo melhor
        $this->jogoColetivoFinalizado($cat, $futsal, $A, $C, 5, 0, '14:00', 'Quadra A');
        $this->jogoColetivoFinalizado($cat, $futsal, $B, $C, 2, 1, '16:00', 'Quadra B');

        $response = $this->actingAs($admin)->get('/ranking?categoria_id=' . $cat->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('ranking.0.turma.id', $A->id)
            ->where('ranking.0.score.saldo_gols', 5)
            ->where('ranking.1.turma.id', $B->id)
            ->where('ranking.1.score.saldo_gols', 1)
            ->where('ranking.2.turma.id', $C->id)
        );
    }

    public function test_ranking_subtrai_penalidades_de_turma(): void
    {
        $admin = $this->makeAdmin();
        $futsal = Esporte::create(['name' => 'Futsal', 'type' => 'coletivo', 'active' => true]);
        $cenario = $this->makeCategoriaComTurmas('Mirim', ['A', 'B']);
        $cat = $cenario['categoria'];
        $A = $cenario['turmas']['A'];
        $B = $cenario['turmas']['B'];

        $this->jogoColetivoFinalizado($cat, $futsal, $A, $B, 3, 1); // A vence -> 3 pts

        Penalidade::create([
            'tipo'           => 'turma',
            'turma_id'       => $A->id,
            'motivo'         => 'Atraso',
            'pontos'         => 1,
            'registrado_por' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/ranking?categoria_id=' . $cat->id);

        $response->assertInertia(fn ($page) => $page
            ->where('ranking.0.turma.id', $A->id)
            ->where('ranking.0.score.penalidades', 1)
            ->where('ranking.0.score.total', 2)
        );
    }

    public function test_ranking_inclui_pontos_de_esportes_individuais(): void
    {
        $admin = $this->makeAdmin();
        $atletismo = Esporte::create(['name' => 'Atletismo', 'type' => 'individual', 'active' => true]);
        $cenario = $this->makeCategoriaComTurmas('Infantil', ['A', 'B']);
        $cat = $cenario['categoria'];
        $A = $cenario['turmas']['A'];

        $atleta = Aluno::create(['name' => 'Ana', 'turma_id' => $A->id, 'period' => 'Matutino', 'active' => true]);
        $atleta->esportes()->attach($atletismo->id);

        $jogo = Jogo::create([
            'categoria_id' => $cat->id,
            'esporte_id'   => $atletismo->id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '10:00',
            'local'        => 'Pista',
        ]);
        ResultadoIndividual::create([
            'jogo_id'  => $jogo->id,
            'aluno_id' => $atleta->id,
            'posicao'  => 1,
        ]);

        $response = $this->actingAs($admin)->get('/ranking?categoria_id=' . $cat->id);

        $response->assertInertia(fn ($page) => $page
            ->where('ranking.0.turma.id', $A->id)
            ->where('ranking.0.score.pontos_individuais', 3)
            ->where('ranking.0.score.total', 3)
        );
    }

    public function test_ranking_default_categoria_quando_param_ausente(): void
    {
        $admin = $this->makeAdmin();
        $primeira = Categoria::create(['name' => 'Alfa', 'active' => true]);
        $segunda  = Categoria::create(['name' => 'Beta', 'active' => true]);

        $response = $this->actingAs($admin)->get('/ranking');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('categoriaAtual', $primeira->id)
        );
    }

    public function test_ranking_retorna_vazio_quando_nao_ha_categoria_ativa(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/ranking');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ranking/Index')
            ->where('ranking', [])
            ->where('categoriaAtual', null)
        );
    }

    public function test_ranking_expoe_last_updated_em_iso8601(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeCategoriaComTurmas('Mirim', ['A']);

        $response = $this->actingAs($admin)->get('/ranking?categoria_id=' . $cenario['categoria']->id);

        $response->assertInertia(fn ($page) => $page
            ->has('lastUpdated')
            ->where('lastUpdated', fn ($value) => is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T/', $value) === 1)
        );
    }

    public function test_professor_pode_acessar_ranking(): void
    {
        $professor = $this->makeProfessor();
        $cenario = $this->makeCategoriaComTurmas('Mirim', ['A']);

        $response = $this->actingAs($professor)->get('/ranking?categoria_id=' . $cenario['categoria']->id);

        $response->assertStatus(200);
    }
}
