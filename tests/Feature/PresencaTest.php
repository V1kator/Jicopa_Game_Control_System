<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Categoria;
use App\Models\Esporte;
use App\Models\Jogo;
use App\Models\Presenca;
use App\Models\Turma;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PresencaTest extends TestCase
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
     * Cria um jogo coletivo com 2 times, com 2 alunos por turma vinculados ao esporte.
     * Retorna ['jogo', 'esporte', 'categoria', 'time1', 'time2', 'alunosTime1', 'alunosTime2'].
     */
    private function makeJogoColetivoCompleto(): array
    {
        $categoria = Categoria::create(['name' => 'Mirim', 'active' => true]);
        $esporte   = Esporte::create(['name' => 'Futsal', 'type' => 'coletivo', 'active' => true]);
        $time1     = Turma::create(['name' => '6A', 'period' => 'Matutino', 'active' => true]);
        $time2     = Turma::create(['name' => '6B', 'period' => 'Matutino', 'active' => true]);

        $categoria->turmas()->attach([$time1->id, $time2->id]);
        $categoria->esportes()->attach($esporte->id);

        $alunosTime1 = collect([
            Aluno::create(['name' => 'Ana', 'turma_id' => $time1->id, 'period' => 'Matutino', 'active' => true]),
            Aluno::create(['name' => 'Bruno', 'turma_id' => $time1->id, 'period' => 'Matutino', 'active' => true]),
        ]);
        $alunosTime2 = collect([
            Aluno::create(['name' => 'Carla', 'turma_id' => $time2->id, 'period' => 'Matutino', 'active' => true]),
            Aluno::create(['name' => 'Diego', 'turma_id' => $time2->id, 'period' => 'Matutino', 'active' => true]),
        ]);

        foreach ($alunosTime1->merge($alunosTime2) as $aluno) {
            $aluno->esportes()->attach($esporte->id);
        }

        $jogo = Jogo::create([
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'time1_id'     => $time1->id,
            'time2_id'     => $time2->id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '14:00',
            'local'        => 'Quadra Coberta',
        ]);

        return compact('jogo', 'esporte', 'categoria', 'time1', 'time2', 'alunosTime1', 'alunosTime2');
    }

    public function test_index_para_jogo_coletivo_pre_preenche_atletas_dos_dois_times(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoColetivoCompleto();

        $response = $this->actingAs($admin)->get('/jogos/' . $cenario['jogo']->id . '/presenca');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('presenca/Index')
            ->has('atletasEsperados', 4)
            ->has('todosAlunos', 4)
        );
    }

    public function test_index_para_jogo_individual_pre_preenche_atletas_da_categoria(): void
    {
        $admin = $this->makeAdmin();
        $categoria = Categoria::create(['name' => 'Infantil', 'active' => true]);
        $esporte   = Esporte::create(['name' => 'Atletismo', 'type' => 'individual', 'active' => true]);
        $turmaA    = Turma::create(['name' => '7A', 'period' => 'Matutino', 'active' => true]);
        $turmaB    = Turma::create(['name' => '7B', 'period' => 'Matutino', 'active' => true]);
        $turmaFora = Turma::create(['name' => '8A', 'period' => 'Matutino', 'active' => true]);

        $categoria->turmas()->attach([$turmaA->id, $turmaB->id]);
        $categoria->esportes()->attach($esporte->id);

        $alunoA = Aluno::create(['name' => 'Eva',   'turma_id' => $turmaA->id,    'period' => 'Matutino', 'active' => true]);
        $alunoB = Aluno::create(['name' => 'Felipe','turma_id' => $turmaB->id,    'period' => 'Matutino', 'active' => true]);
        $alunoFora = Aluno::create(['name' => 'Gabi','turma_id' => $turmaFora->id,'period' => 'Matutino', 'active' => true]);
        $alunoA->esportes()->attach($esporte->id);
        $alunoB->esportes()->attach($esporte->id);
        $alunoFora->esportes()->attach($esporte->id);

        $jogo = Jogo::create([
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '10:00',
            'local'        => 'Pista',
        ]);

        $response = $this->actingAs($admin)->get('/jogos/' . $jogo->id . '/presenca');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('presenca/Index')
            ->has('atletasEsperados', 2)
        );
    }

    public function test_admin_pode_registrar_presencas(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoColetivoCompleto();
        $jogo = $cenario['jogo'];

        $payload = [
            'presencas' => [
                ['aluno_id' => $cenario['alunosTime1'][0]->id, 'presente' => true,  'is_substituto' => false],
                ['aluno_id' => $cenario['alunosTime1'][1]->id, 'presente' => false, 'is_substituto' => false],
                ['aluno_id' => $cenario['alunosTime2'][0]->id, 'presente' => true,  'is_substituto' => false],
            ],
        ];

        $response = $this->actingAs($admin)->post('/jogos/' . $jogo->id . '/presenca', $payload);

        $response->assertRedirect(route('calendario.index'));
        $this->assertDatabaseHas('presencas', ['jogo_id' => $jogo->id, 'aluno_id' => $cenario['alunosTime1'][0]->id, 'presente' => true]);
        $this->assertDatabaseHas('presencas', ['jogo_id' => $jogo->id, 'aluno_id' => $cenario['alunosTime1'][1]->id, 'presente' => false]);
        $this->assertEquals(3, Presenca::where('jogo_id', $jogo->id)->count());
    }

    public function test_registrar_presencas_remove_alunos_omitidos_no_segundo_envio(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoColetivoCompleto();
        $jogo = $cenario['jogo'];

        // Primeiro envio: 3 presenças
        $this->actingAs($admin)->post('/jogos/' . $jogo->id . '/presenca', [
            'presencas' => [
                ['aluno_id' => $cenario['alunosTime1'][0]->id, 'presente' => true,  'is_substituto' => false],
                ['aluno_id' => $cenario['alunosTime1'][1]->id, 'presente' => true,  'is_substituto' => false],
                ['aluno_id' => $cenario['alunosTime2'][0]->id, 'presente' => true,  'is_substituto' => false],
            ],
        ]);
        $this->assertEquals(3, Presenca::where('jogo_id', $jogo->id)->count());

        // Segundo envio: omite o aluno alunosTime2[0] — controller deve apagá-lo
        $this->actingAs($admin)->post('/jogos/' . $jogo->id . '/presenca', [
            'presencas' => [
                ['aluno_id' => $cenario['alunosTime1'][0]->id, 'presente' => true,  'is_substituto' => false],
                ['aluno_id' => $cenario['alunosTime1'][1]->id, 'presente' => false, 'is_substituto' => false],
            ],
        ]);

        $this->assertEquals(2, Presenca::where('jogo_id', $jogo->id)->count());
        $this->assertDatabaseMissing('presencas', ['jogo_id' => $jogo->id, 'aluno_id' => $cenario['alunosTime2'][0]->id]);
        $this->assertDatabaseHas('presencas', ['jogo_id' => $jogo->id, 'aluno_id' => $cenario['alunosTime1'][1]->id, 'presente' => false]);
    }

    public function test_registrar_presencas_rejeita_aluno_inexistente(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->makeJogoColetivoCompleto();

        $response = $this->actingAs($admin)->post('/jogos/' . $cenario['jogo']->id . '/presenca', [
            'presencas' => [
                ['aluno_id' => 999999, 'presente' => true, 'is_substituto' => false],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals(0, Presenca::where('jogo_id', $cenario['jogo']->id)->count());
    }

    public function test_professor_pode_acessar_e_registrar_presencas(): void
    {
        $professor = $this->makeProfessor();
        $cenario = $this->makeJogoColetivoCompleto();
        $jogo = $cenario['jogo'];

        $get = $this->actingAs($professor)->get('/jogos/' . $jogo->id . '/presenca');
        $get->assertStatus(200);

        $post = $this->actingAs($professor)->post('/jogos/' . $jogo->id . '/presenca', [
            'presencas' => [
                ['aluno_id' => $cenario['alunosTime1'][0]->id, 'presente' => true, 'is_substituto' => false],
            ],
        ]);
        $post->assertRedirect(route('calendario.index'));
        $this->assertDatabaseHas('presencas', ['jogo_id' => $jogo->id, 'aluno_id' => $cenario['alunosTime1'][0]->id]);
    }

    public function test_visitante_anonimo_nao_acessa_presenca(): void
    {
        $cenario = $this->makeJogoColetivoCompleto();
        $response = $this->get('/jogos/' . $cenario['jogo']->id . '/presenca');
        $response->assertRedirect('/login');
    }
}
