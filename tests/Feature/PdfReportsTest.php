<?php

namespace Tests\Feature;

use App\Models\Aluno;
use App\Models\Categoria;
use App\Models\Esporte;
use App\Models\Jogo;
use App\Models\Penalidade;
use App\Models\Presenca;
use App\Models\ScoringConfig;
use App\Models\Turma;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PdfReportsTest extends TestCase
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

    /**
     * Cenário mínimo com 1 categoria, 2 turmas, 1 esporte coletivo, 1 jogo finalizado.
     */
    private function cenarioBasico(): array
    {
        $categoria = Categoria::create(['name' => 'Mirim', 'active' => true]);
        $esporte   = Esporte::create(['name' => 'Futsal', 'type' => 'coletivo', 'active' => true]);
        $time1     = Turma::create(['name' => '6A', 'period' => 'Matutino', 'active' => true]);
        $time2     = Turma::create(['name' => '6B', 'period' => 'Matutino', 'active' => true]);
        $categoria->turmas()->attach([$time1->id, $time2->id]);

        $jogo = Jogo::create([
            'categoria_id' => $categoria->id,
            'esporte_id'   => $esporte->id,
            'time1_id'     => $time1->id,
            'time2_id'     => $time2->id,
            'data'         => Carbon::tomorrow()->format('Y-m-d'),
            'hora'         => '14:00',
            'local'        => 'Quadra Coberta',
            'placar_time1' => 3,
            'placar_time2' => 1,
            'vencedor_id'  => $time1->id,
        ]);

        return compact('categoria', 'esporte', 'time1', 'time2', 'jogo');
    }

    private function assertResponseIsPdfDownload($response): void
    {
        $response->assertStatus(200);
        $contentType = $response->headers->get('Content-Type');
        $disposition = $response->headers->get('Content-Disposition') ?? '';
        $this->assertTrue(
            str_contains((string) $contentType, 'application/pdf') || str_contains($disposition, '.pdf'),
            'Esperava content-type application/pdf ou content-disposition com .pdf. Recebido: ' . $contentType . ' / ' . $disposition
        );
        $this->assertGreaterThan(1000, strlen($response->getContent()), 'PDF parece vazio ou truncado.');
    }

    // ---------- RF20: súmula ----------

    public function test_admin_pode_gerar_sumula_pdf_de_jogo(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->cenarioBasico();

        $response = $this->actingAs($admin)->get('/admin/jogos/' . $cenario['jogo']->id . '/sumula');

        $this->assertResponseIsPdfDownload($response);
    }

    public function test_sumula_bloqueia_jogo_cancelado(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->cenarioBasico();
        $cenario['jogo']->update(['cancelado' => true]);

        $response = $this->actingAs($admin)->get('/admin/jogos/' . $cenario['jogo']->id . '/sumula');

        $response->assertSessionHasErrors(['error']);
    }

    // ---------- RF21: boletim de turma ----------

    public function test_admin_pode_gerar_boletim_pdf_de_turma_com_categoria(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->cenarioBasico();

        $response = $this->actingAs($admin)
            ->get('/admin/turmas/' . $cenario['time1']->id . '/boletim?categoria_id=' . $cenario['categoria']->id);

        $this->assertResponseIsPdfDownload($response);
    }

    public function test_boletim_redireciona_com_erro_quando_categoria_ausente(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->cenarioBasico();

        $response = $this->actingAs($admin)->get('/admin/turmas/' . $cenario['time1']->id . '/boletim');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ---------- RF22: relatório geral ----------

    public function test_admin_pode_gerar_relatorio_geral_pdf(): void
    {
        $admin = $this->makeAdmin();
        $this->cenarioBasico();

        $response = $this->actingAs($admin)->get('/admin/relatorios/geral');

        $this->assertResponseIsPdfDownload($response);
    }

    public function test_relatorio_geral_funciona_mesmo_sem_jogos(): void
    {
        $admin = $this->makeAdmin();
        Categoria::create(['name' => 'Vazia', 'active' => true]);

        $response = $this->actingAs($admin)->get('/admin/relatorios/geral');

        $this->assertResponseIsPdfDownload($response);
    }

    // ---------- RF23: relatório por categoria ----------

    public function test_admin_pode_gerar_relatorio_por_categoria_pdf(): void
    {
        $admin = $this->makeAdmin();
        $cenario = $this->cenarioBasico();

        // Adiciona uma penalidade para exercitar a seção de penalidades do template
        Penalidade::create([
            'tipo'           => 'turma',
            'turma_id'       => $cenario['time1']->id,
            'motivo'         => 'Atraso',
            'pontos'         => 1,
            'registrado_por' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/relatorios/categoria/' . $cenario['categoria']->id);

        $this->assertResponseIsPdfDownload($response);
    }

    // ---------- RBAC ----------

    public function test_professor_pode_gerar_sumula_e_boletim(): void
    {
        $professor = $this->makeProfessor();
        $cenario = $this->cenarioBasico();

        $sumula = $this->actingAs($professor)
            ->get('/admin/jogos/' . $cenario['jogo']->id . '/sumula');
        $this->assertResponseIsPdfDownload($sumula);

        $boletim = $this->actingAs($professor)
            ->get('/admin/turmas/' . $cenario['time1']->id . '/boletim?categoria_id=' . $cenario['categoria']->id);
        $this->assertResponseIsPdfDownload($boletim);
    }

    public function test_professor_pode_gerar_relatorios(): void
    {
        $professor = $this->makeProfessor();
        $cenario = $this->cenarioBasico();

        $geral = $this->actingAs($professor)->get('/admin/relatorios/geral');
        $this->assertResponseIsPdfDownload($geral);

        $porCat = $this->actingAs($professor)
            ->get('/admin/relatorios/categoria/' . $cenario['categoria']->id);
        $this->assertResponseIsPdfDownload($porCat);
    }

    public function test_visitante_anonimo_nao_acessa_relatorios(): void
    {
        $cenario = $this->cenarioBasico();

        $this->get('/admin/jogos/' . $cenario['jogo']->id . '/sumula')->assertRedirect('/login');
        $this->get('/admin/relatorios/geral')->assertRedirect('/login');
    }
}
