<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Jogo;
use App\Models\Turma;
use App\Models\User;
use Database\Seeders\Jicopa2025Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Smoke tests da reconstrução retrospectiva JiCopa 2025 (R1-08 do artigo).
 *
 * Validam que, com o dataset real anonimizado de 2025 carregado pelo
 * Jicopa2025Seeder, os quatro tipos de relatório do sistema (súmula de
 * jogo, boletim de turma, ranking geral, ranking por categoria) são
 * gerados com sucesso e retornam respostas válidas.
 *
 * Estes testes funcionam como evidência empírica de que o Jicopa absorve
 * o volume e a estrutura da JiCopa 2025 — 275 alunos, 13 turmas, 8 esportes,
 * 16 jogos, 15 penalidades, 13 bandeiras — sem regressões observáveis.
 */
class Jicopa2025SmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'professor', 'guard_name' => 'web']);

        // Usuário admin (necessário para autenticação + para o campo
        // `registrado_por` das penalidades).
        $this->admin = User::factory()->create(['active' => true]);
        $this->admin->assignRole('admin');

        // Carrega o dataset 2025 real anonimizado.
        $this->seed(Jicopa2025Seeder::class);
    }

    public function test_dataset_2025_populou_volumes_esperados(): void
    {
        $this->assertSame(13, Turma::count(), 'Turmas');
        $this->assertSame(3,  Categoria::count(), 'Categorias');
        $this->assertSame(8,  \App\Models\Esporte::count(), 'Esportes');
        $this->assertSame(275, \App\Models\Aluno::count(), 'Alunos');
        $this->assertSame(16, Jogo::count(), 'Jogos');
        $this->assertSame(15, \App\Models\Penalidade::count(), 'Penalidades');
        $this->assertSame(52, \App\Models\AvaliacaoNota::count(),
            'Bandeiras (13 turmas × 4 jurados)');
        $this->assertGreaterThan(0, \App\Models\Presenca::count(),
            'Presenças');
    }

    public function test_sumula_pdf_gera_para_primeiro_jogo_de_futebol(): void
    {
        // G4 do PLAN: primeiro jogo Futebol Infantil (A v × B2 m).
        $jogo = Jogo::query()
            ->whereHas('esporte', fn($q) => $q->where('name', 'Futebol'))
            ->orderBy('id')
            ->first();
        $this->assertNotNull($jogo, 'Primeiro jogo de Futebol não encontrado');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.jogos.sumula', $jogo->id));

        $response->assertStatus(200);
        $this->assertSame('application/pdf',
            $response->headers->get('Content-Type'),
            'Resposta deve ser PDF');
        $this->assertGreaterThan(1000, strlen($response->getContent()),
            'PDF deve ter conteúdo (>1KB)');
    }

    public function test_boletim_pdf_gera_para_turma_a_matutino(): void
    {
        // Boletim da Turma A Matutino — categoria Infantil
        $turma = Turma::where('name', 'Turma A')
            ->where('period', 'Matutino')
            ->first();
        $this->assertNotNull($turma);
        $cat = Categoria::where('name', 'Infantil')->first();
        $this->assertNotNull($cat);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.turmas.boletim', $turma->id) .
                  '?categoria_id=' . $cat->id);

        $response->assertStatus(200);
        $this->assertSame('application/pdf',
            $response->headers->get('Content-Type'));
        $this->assertGreaterThan(1000, strlen($response->getContent()));
    }

    public function test_ranking_geral_responde_com_dataset_2025(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.relatorios.geral'));

        $response->assertStatus(200);
        // O ranking geral é Inertia view; o conteúdo é HTML/JSON inertia.
        $this->assertNotEmpty($response->getContent());
    }

    public function test_ranking_por_categoria_responde_para_infantil(): void
    {
        $cat = Categoria::where('name', 'Infantil')->first();
        $this->assertNotNull($cat);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.relatorios.categoria', $cat->id));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->getContent());
    }
}
