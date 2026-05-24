<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Categoria;
use App\Models\Esporte;
use App\Models\Jogo;
use App\Models\Presenca;
use App\Models\ResultadoIndividual;
use App\Models\Turma;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResultadoTest extends TestCase
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

    /**
     * Cenário coletivo: 1 jogo Futsal com 2 turmas.
     */
    private function makeJogoColetivo(): array
    {
        $categoria = Categoria::create(['name' => 'Mirim', 'active' => true]);
        $esporte   = Esporte::create(['name' => 'Futsal', 'type' => 'coletivo', 'active' => true]);
        $time1     = Turma::create(['name' => '6A', 'period' => 'Matutino', 'active' => true]);
        $time2     = Turma::create(['name' => '6B', 'period' => 'Matutino', 'active' => true]);
        $jogo = Jogo::create([
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'time1_id'     => $time1->id,
            'time2_id'     => $time2->id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '14:00',
            'local'        => 'Quadra Coberta',
        ]);
        return compact('jogo', 'esporte', 'categoria', 'time1', 'time2');
    }

    /**
     * Cenário individual: 1 jogo Atletismo com 3 atletas, todos com presença marcada como presente.
     */
    private function makeJogoIndividual(): array
    {
        $categoria = Categoria::create(['name' => 'Infantil', 'active' => true]);
        $esporte   = Esporte::create(['name' => 'Atletismo', 'type' => 'individual', 'active' => true]);
        $turma     = Turma::create(['name' => '7A', 'period' => 'Matutino', 'active' => true]);
        $jogo = Jogo::create([
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '10:00',
            'local'        => 'Pista',
        ]);
        $atletas = collect([
            Aluno::create(['name' => 'Ana',   'turma_id' => $turma->id, 'period' => 'Matutino', 'active' => true]),
            Aluno::create(['name' => 'Bruno', 'turma_id' => $turma->id, 'period' => 'Matutino', 'active' => true]),
            Aluno::create(['name' => 'Carla', 'turma_id' => $turma->id, 'period' => 'Matutino', 'active' => true]),
        ]);
        foreach ($atletas as $aluno) {
            $aluno->esportes()->attach($esporte->id);
            Presenca::create(['jogo_id' => $jogo->id, 'aluno_id' => $aluno->id, 'presente' => true]);
        }
        return compact('jogo', 'esporte', 'categoria', 'turma', 'atletas');
    }

    public function test_resultado_coletivo_infere_vitoria_de_time1(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoColetivo();

        $response = $this->actingAs($admin)->put('/jogos/' . $cenario['jogo']->id . '/resultado', [
            'placar_time1' => 3,
            'placar_time2' => 1,
        ]);

        $response->assertRedirect(route('calendario.index'));
        $this->assertDatabaseHas('jogos', [
            'id'           => $cenario['jogo']->id,
            'placar_time1' => 3,
            'placar_time2' => 1,
            'vencedor_id'  => $cenario['time1']->id,
        ]);
    }

    public function test_resultado_coletivo_infere_vitoria_de_time2(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoColetivo();

        $this->actingAs($admin)->put('/jogos/' . $cenario['jogo']->id . '/resultado', [
            'placar_time1' => 1,
            'placar_time2' => 3,
        ]);

        $this->assertDatabaseHas('jogos', [
            'id'          => $cenario['jogo']->id,
            'vencedor_id' => $cenario['time2']->id,
        ]);
    }

    public function test_resultado_coletivo_infere_empate_com_vencedor_null(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoColetivo();

        $this->actingAs($admin)->put('/jogos/' . $cenario['jogo']->id . '/resultado', [
            'placar_time1' => 2,
            'placar_time2' => 2,
        ]);

        $this->assertDatabaseHas('jogos', [
            'id'           => $cenario['jogo']->id,
            'placar_time1' => 2,
            'placar_time2' => 2,
            'vencedor_id'  => null,
        ]);
    }

    public function test_resultado_coletivo_rejeita_placar_negativo(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoColetivo();

        $response = $this->actingAs($admin)->put('/jogos/' . $cenario['jogo']->id . '/resultado', [
            'placar_time1' => -1,
            'placar_time2' => 0,
        ]);

        $response->assertSessionHasErrors(['placar_time1']);
    }

    public function test_resultado_individual_persiste_posicoes(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoIndividual();
        $jogoId = $cenario['jogo']->id;

        $response = $this->actingAs($admin)->put('/jogos/' . $jogoId . '/resultado', [
            'resultados' => [
                ['aluno_id' => $cenario['atletas'][0]->id, 'posicao' => 1],
                ['aluno_id' => $cenario['atletas'][1]->id, 'posicao' => 2],
                ['aluno_id' => $cenario['atletas'][2]->id, 'posicao' => 3],
            ],
        ]);

        $response->assertRedirect(route('calendario.index'));
        $this->assertEquals(3, ResultadoIndividual::where('jogo_id', $jogoId)->count());
        $this->assertDatabaseHas('resultado_individual', [
            'jogo_id'  => $jogoId,
            'aluno_id' => $cenario['atletas'][0]->id,
            'posicao'  => 1,
        ]);
    }

    public function test_resultado_individual_substitui_registros_anteriores(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoIndividual();
        $jogoId = $cenario['jogo']->id;

        // Primeiro envio: 3 atletas
        $this->actingAs($admin)->put('/jogos/' . $jogoId . '/resultado', [
            'resultados' => [
                ['aluno_id' => $cenario['atletas'][0]->id, 'posicao' => 1],
                ['aluno_id' => $cenario['atletas'][1]->id, 'posicao' => 2],
                ['aluno_id' => $cenario['atletas'][2]->id, 'posicao' => 3],
            ],
        ]);
        $this->assertEquals(3, ResultadoIndividual::where('jogo_id', $jogoId)->count());

        // Segundo envio: troca o pódio (apenas 2 atletas; o terceiro deve ser apagado)
        $this->actingAs($admin)->put('/jogos/' . $jogoId . '/resultado', [
            'resultados' => [
                ['aluno_id' => $cenario['atletas'][2]->id, 'posicao' => 1],
                ['aluno_id' => $cenario['atletas'][0]->id, 'posicao' => 2],
            ],
        ]);

        $this->assertEquals(2, ResultadoIndividual::where('jogo_id', $jogoId)->count());
        $this->assertDatabaseHas('resultado_individual', [
            'jogo_id'  => $jogoId,
            'aluno_id' => $cenario['atletas'][2]->id,
            'posicao'  => 1,
        ]);
        $this->assertDatabaseMissing('resultado_individual', [
            'jogo_id'  => $jogoId,
            'aluno_id' => $cenario['atletas'][1]->id,
        ]);
    }

    public function test_edit_individual_carrega_apenas_atletas_presentes(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoIndividual();
        $jogoId = $cenario['jogo']->id;

        // Marca o terceiro atleta como ausente
        Presenca::where('jogo_id', $jogoId)
            ->where('aluno_id', $cenario['atletas'][2]->id)
            ->update(['presente' => false]);

        $response = $this->actingAs($admin)->get('/jogos/' . $jogoId . '/resultado');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('resultados/Edit')
            ->has('atletas', 2)
        );
    }

    public function test_resultado_individual_rejeita_aluno_inexistente(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoIndividual();
        $jogoId = $cenario['jogo']->id;

        $response = $this->actingAs($admin)->put('/jogos/' . $jogoId . '/resultado', [
            'resultados' => [
                ['aluno_id' => 999999, 'posicao' => 1],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals(0, ResultadoIndividual::where('jogo_id', $jogoId)->count());
    }

    public function test_professor_pode_registrar_resultado(): void
    {
        $professor = $this->makeProfessor();
        $cenario = $this->makeJogoColetivo();

        $response = $this->actingAs($professor)->put('/jogos/' . $cenario['jogo']->id . '/resultado', [
            'placar_time1' => 4,
            'placar_time2' => 2,
        ]);

        $response->assertRedirect(route('calendario.index'));
        $this->assertDatabaseHas('jogos', [
            'id'           => $cenario['jogo']->id,
            'placar_time1' => 4,
            'vencedor_id'  => $cenario['time1']->id,
        ]);
    }
}
