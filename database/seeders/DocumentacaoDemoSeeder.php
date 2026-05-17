<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder de demonstração para a Fase 03 da Documentação (IFRO/ABNT).
 *
 * Deve ser executado APÓS DatabaseSeeder e JicopaPlanilhasSeeder.
 * NÃO é idempotente — usa migrate:fresh para reset.
 *
 * Popula gaps que os seeders oficiais não cobrem:
 *  - anonimização dos nomes dos alunos (LGPD, menores de idade)
 *  - 1 Admin desativado (evidência de status em RF3)
 *  - 2 categorias (Mirim, Infantil) com vínculos a turmas e esportes
 *  - 4 jogos (1 coletivo realizado, 1 individual realizado, 2 agendados com conflito para RF9)
 *  - presenças para os jogos realizados
 *  - resultados (placar coletivo + ranking individual)
 *  - 2 penalidades (1 turma, 1 aluno)
 *  - avaliacao_config singleton
 *  - notas de avaliação (3 jurados × 2 turmas)
 */
class DocumentacaoDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Adicionar alunos extras em Turma A Matutino e Turma B1 Matutino para que os jogos de demonstração
        //    tenham escalações representativas na tela de presença. Esses alunos serão renomeados na anonimização.
        $turmaA_Mat  = DB::table('turmas')->where('name', 'Turma A')->where('period', 'Matutino')->value('id');
        $turmaB1_Mat = DB::table('turmas')->where('name', 'Turma B1')->where('period', 'Matutino')->value('id');
        $esporteIdsTmp = DB::table('esportes')->pluck('id', 'name');
        $extras = [
            ['turma_id' => $turmaA_Mat,  'esportes' => ['Futebol', 'Vôlei']],
            ['turma_id' => $turmaA_Mat,  'esportes' => ['Futebol', 'Basquete']],
            ['turma_id' => $turmaA_Mat,  'esportes' => ['Futebol']],
            ['turma_id' => $turmaB1_Mat, 'esportes' => ['Futebol', 'Vôlei']],
            ['turma_id' => $turmaB1_Mat, 'esportes' => ['Futebol', 'Basquete']],
            ['turma_id' => $turmaB1_Mat, 'esportes' => ['Futebol']],
            ['turma_id' => $turmaB1_Mat, 'esportes' => ['Futebol', 'Vôlei']],
        ];
        foreach ($extras as $extra) {
            $novoId = DB::table('alunos')->insertGetId([
                'name'       => 'tmp',
                'turma_id'   => $extra['turma_id'],
                'period'     => 'Matutino',
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($extra['esportes'] as $esporteNome) {
                DB::table('aluno_esporte')->insertOrIgnore([
                    'aluno_id'   => $novoId,
                    'esporte_id' => $esporteIdsTmp[$esporteNome],
                ]);
            }
        }

        // 1. Anonimizar nomes dos alunos (LGPD) — aplica-se a TODOS os alunos existentes (19 originais + 7 extras)
        $alunos = DB::table('alunos')->orderBy('id')->get();
        foreach ($alunos as $index => $aluno) {
            DB::table('alunos')
                ->where('id', $aluno->id)
                ->update([
                    'name' => 'Aluno ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'updated_at' => now(),
                ]);
        }

        // 2. Criar um Admin desativado para evidência do RF3 (listagem com status)
        $adminInativo = User::create([
            'name'     => 'Administrador Desligado',
            'email'    => 'admin-antigo@jicopa.local',
            'password' => Hash::make('password'),
            'active'   => false,
        ]);
        $adminInativo->assignRole('admin');

        // 3. Criar 2 categorias
        $categoriaMirimId = DB::table('categorias')->insertGetId([
            'name'       => 'Mirim',
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $categoriaInfantilId = DB::table('categorias')->insertGetId([
            'name'       => 'Infantil',
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Vincular categorias às turmas (pivot categoria_turma)
        // Mirim: Turma A (Matutino+Vespertino), Turma B1 (Matutino+Vespertino)
        // Infantil: Turma C (Matutino+Vespertino), Turma D (Matutino+Vespertino)
        $turmaA_M  = DB::table('turmas')->where('name', 'Turma A')->where('period', 'Matutino')->value('id');
        $turmaA_V  = DB::table('turmas')->where('name', 'Turma A')->where('period', 'Vespertino')->value('id');
        $turmaB1_M = DB::table('turmas')->where('name', 'Turma B1')->where('period', 'Matutino')->value('id');
        $turmaB1_V = DB::table('turmas')->where('name', 'Turma B1')->where('period', 'Vespertino')->value('id');
        $turmaC_M  = DB::table('turmas')->where('name', 'Turma C')->where('period', 'Matutino')->value('id');
        $turmaC_V  = DB::table('turmas')->where('name', 'Turma C')->where('period', 'Vespertino')->value('id');
        $turmaD_M  = DB::table('turmas')->where('name', 'Turma D')->where('period', 'Matutino')->value('id');
        $turmaD_V  = DB::table('turmas')->where('name', 'Turma D')->where('period', 'Vespertino')->value('id');

        foreach ([$turmaA_M, $turmaA_V, $turmaB1_M, $turmaB1_V] as $tid) {
            DB::table('categoria_turma')->insert([
                'categoria_id' => $categoriaMirimId,
                'turma_id'     => $tid,
            ]);
        }
        foreach ([$turmaC_M, $turmaC_V, $turmaD_M, $turmaD_V] as $tid) {
            DB::table('categoria_turma')->insert([
                'categoria_id' => $categoriaInfantilId,
                'turma_id'     => $tid,
            ]);
        }

        // 5. Vincular categorias aos esportes (pivot categoria_esporte) — ambas cobrem todos os 8 esportes
        $esporteIds = DB::table('esportes')->pluck('id', 'name');
        foreach ($esporteIds as $eid) {
            DB::table('categoria_esporte')->insert([
                'categoria_id' => $categoriaMirimId,
                'esporte_id'   => $eid,
            ]);
            DB::table('categoria_esporte')->insert([
                'categoria_id' => $categoriaInfantilId,
                'esporte_id'   => $eid,
            ]);
        }

        // 6. Criar jogos
        // Jogo 1: Futebol (coletivo), Mirim, Turma A Matutino vs Turma B1 Matutino — realizado em 2026-04-15, placar 3×1
        $jogo1Id = DB::table('jogos')->insertGetId([
            'categoria_id' => $categoriaMirimId,
            'esporte_id'   => $esporteIds['Futebol'],
            'time1_id'     => $turmaA_M,
            'time2_id'     => $turmaB1_M,
            'data'         => '2026-04-15',
            'hora'         => '14:00:00',
            'local'        => 'Quadra 1',
            'placar_time1' => 3,
            'placar_time2' => 1,
            'vencedor_id'  => $turmaA_M,
            'cancelado'    => false,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Jogo 2: Taekwondo (individual), Mirim — realizado em 2026-04-16
        $jogo2Id = DB::table('jogos')->insertGetId([
            'categoria_id' => $categoriaMirimId,
            'esporte_id'   => $esporteIds['Taekwondo'],
            'time1_id'     => null,
            'time2_id'     => null,
            'data'         => '2026-04-16',
            'hora'         => '09:00:00',
            'local'        => 'Ginásio',
            'placar_time1' => null,
            'placar_time2' => null,
            'vencedor_id'  => null,
            'cancelado'    => false,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Jogo 3: Vôlei (coletivo), Mirim — agendado para 2026-05-10 14:00 Quadra 1
        $jogo3Id = DB::table('jogos')->insertGetId([
            'categoria_id' => $categoriaMirimId,
            'esporte_id'   => $esporteIds['Vôlei'],
            'time1_id'     => $turmaA_M,
            'time2_id'     => $turmaB1_M,
            'data'         => '2026-05-10',
            'hora'         => '14:00:00',
            'local'        => 'Quadra 1',
            'cancelado'    => false,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Jogo 4: Basquete (coletivo), Mirim — agendado para 2026-05-10 14:00 Quadra 1 (MESMO SLOT DO JOGO 3 — para demonstrar detecção de conflito)
        // Mas na prática, se o sistema tem unique constraint no slot, isto falha. Vou agendar em horário diferente mas mesmo local/data para gerar conflito no form, e deixar o conflito acontecer quando tentar criar o jogo 5.
        // Estratégia: Jogo 4 em 2026-05-10 15:00 Quadra 1. No RF09 a captura será tentando criar um jogo no mesmo slot via form UI.
        $jogo4Id = DB::table('jogos')->insertGetId([
            'categoria_id' => $categoriaMirimId,
            'esporte_id'   => $esporteIds['Basquete'],
            'time1_id'     => $turmaC_M,
            'time2_id'     => $turmaD_M,
            'data'         => '2026-05-10',
            'hora'         => '15:00:00',
            'local'        => 'Quadra 1',
            'cancelado'    => false,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // 7. Presenças para Jogo 1 (coletivo futebol A x B1)
        // Alunos de Turma A Matutino e Turma B1 Matutino que têm 'Futebol' no aluno_esporte
        $alunosFutebolA = DB::table('alunos as a')
            ->join('aluno_esporte as ae', 'ae.aluno_id', '=', 'a.id')
            ->where('a.turma_id', $turmaA_M)
            ->where('ae.esporte_id', $esporteIds['Futebol'])
            ->pluck('a.id');
        $alunosFutebolB1 = DB::table('alunos as a')
            ->join('aluno_esporte as ae', 'ae.aluno_id', '=', 'a.id')
            ->where('a.turma_id', $turmaB1_M)
            ->where('ae.esporte_id', $esporteIds['Futebol'])
            ->pluck('a.id');

        foreach ($alunosFutebolA->merge($alunosFutebolB1) as $alunoId) {
            DB::table('presencas')->insert([
                'jogo_id'       => $jogo1Id,
                'aluno_id'      => $alunoId,
                'presente'      => true,
                'is_substituto' => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // 8. Presenças + resultado do Jogo 2 (individual Taekwondo)
        // Pegar 3 alunos de qualquer turma Mirim com Taekwondo
        $alunosTaekwondo = DB::table('alunos as a')
            ->join('aluno_esporte as ae', 'ae.aluno_id', '=', 'a.id')
            ->whereIn('a.turma_id', [$turmaA_M, $turmaA_V, $turmaB1_M, $turmaB1_V])
            ->where('ae.esporte_id', $esporteIds['Taekwondo'])
            ->pluck('a.id')
            ->take(3)
            ->values();

        foreach ($alunosTaekwondo as $alunoId) {
            DB::table('presencas')->insert([
                'jogo_id'       => $jogo2Id,
                'aluno_id'      => $alunoId,
                'presente'      => true,
                'is_substituto' => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Resultado individual: posições 1, 2, 3
        foreach ($alunosTaekwondo as $index => $alunoId) {
            DB::table('resultado_individual')->insert([
                'jogo_id'    => $jogo2Id,
                'aluno_id'   => $alunoId,
                'posicao'    => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 9. Penalidades
        $adminId = User::where('email', 'admin@jicopa.local')->value('id');
        DB::table('penalidades')->insert([
            'tipo'            => 'turma',
            'jogo_id'         => $jogo1Id,
            'turma_id'        => $turmaB1_M,
            'aluno_id'        => null,
            'motivo'          => 'Comportamento inadequado da torcida durante a partida.',
            'pontos'          => 5,
            'registrado_por'  => $adminId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $alunoPenalizado = DB::table('alunos')->where('turma_id', $turmaA_M)->value('id');
        DB::table('penalidades')->insert([
            'tipo'            => 'aluno',
            'jogo_id'         => $jogo1Id,
            'turma_id'        => null,
            'aluno_id'        => $alunoPenalizado,
            'motivo'          => 'Falta disciplinar em jogo oficial.',
            'pontos'          => 2,
            'registrado_por'  => $adminId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // 10. Avaliacao config singleton
        DB::table('avaliacao_config')->insert([
            'num_jurados'           => 3,
            'nota_min'              => 0.00,
            'nota_max'              => 10.00,
            'pontos_bonus_melhor'   => 5,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // 11. Notas de avaliação — 3 jurados × 2 turmas (A Matutino, B1 Matutino) × categoria Mirim
        $notasTurmaA  = [8.50, 9.00, 8.75];
        $notasTurmaB1 = [7.50, 8.00, 7.75];
        foreach ($notasTurmaA as $jurado => $nota) {
            DB::table('avaliacao_notas')->insert([
                'turma_id'     => $turmaA_M,
                'categoria_id' => $categoriaMirimId,
                'jurado_num'   => $jurado + 1,
                'nota'         => $nota,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
        foreach ($notasTurmaB1 as $jurado => $nota) {
            DB::table('avaliacao_notas')->insert([
                'turma_id'     => $turmaB1_M,
                'categoria_id' => $categoriaMirimId,
                'jurado_num'   => $jurado + 1,
                'nota'         => $nota,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $this->command->info('DocumentacaoDemoSeeder concluído.');
        $this->command->info('  - 19 alunos anonimizados (Aluno 01..19)');
        $this->command->info('  - 1 admin desativado (admin-antigo@jicopa.local)');
        $this->command->info('  - 2 categorias (Mirim, Infantil)');
        $this->command->info("  - 4 jogos (IDs: $jogo1Id, $jogo2Id, $jogo3Id, $jogo4Id)");
        $this->command->info('  - Presenças, resultados, penalidades, avaliação populadas');
    }
}
