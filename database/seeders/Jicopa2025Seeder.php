<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jicopa2025Seeder
 *
 * Popula o banco com a reconstrução retrospectiva da JiCopa 2025 a partir
 * de dados reais anonimizados (PII removida — ver fase R1-08 do artigo).
 *
 * Origem dos dados: Documentacao/TCC/artigo/revisao-r1/dados/jicopa-2025-dados.json
 * (gerado a partir de jicopa-2025-anonimizada.xlsx pelo script
 * exportar-json-seeder.py).
 *
 * Estratégia não-destrutiva: trunca apenas tabelas de domínio JiCopa,
 * preservando users / roles / permissions / cache.
 *
 * Uso:
 *   cd jicopa && php artisan db:seed --class=Jicopa2025Seeder
 */
class Jicopa2025Seeder extends Seeder
{
    private array $turmaIds   = [];
    private array $categoriaIds = [];
    private array $esporteIds = [];
    private array $alunoIds   = [];
    private array $jogoIds    = [];

    /** Códigos da planilha (ex.: "A m") → ("Turma A", "Matutino"). */
    private const TURMA_CODIGO_PARA_NOME = [
        'A m'  => ['Turma A',  'Matutino'],
        'A v'  => ['Turma A',  'Vespertino'],
        'B1 m' => ['Turma B1', 'Matutino'],
        'B1 v' => ['Turma B1', 'Vespertino'],
        'B2 m' => ['Turma B2', 'Matutino'],
        'B2 v' => ['Turma B2', 'Vespertino'],
        'C m'  => ['Turma C',  'Matutino'],
        'C v'  => ['Turma C',  'Vespertino'],
        'D m'  => ['Turma D',  'Matutino'],
        'D v'  => ['Turma D',  'Vespertino'],
        'E m'  => ['Turma E',  'Matutino'],
        'E v'  => ['Turma E',  'Vespertino'],
        'GR'   => ['Turma GR', 'Vespertino'],
    ];

    public function run(): void
    {
        $jsonPath = base_path('../Documentacao/TCC/artigo/revisao-r1/dados/jicopa-2025-dados.json');
        if (! is_file($jsonPath)) {
            $this->command->error("Arquivo de dados não encontrado: {$jsonPath}");
            $this->command->error('Rode primeiro: cd Documentacao/TCC/artigo/revisao-r1/dados && python3 exportar-json-seeder.py');
            return;
        }

        $dados = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($dados)) {
            $this->command->error('JSON inválido.');
            return;
        }

        $this->command->info('Iniciando Jicopa2025Seeder (reconstrução retrospectiva JiCopa 2025).');
        $this->command->info(sprintf(
            '  Origem: %s | Geração: %s',
            $dados['meta']['origem'] ?? '?',
            $dados['meta']['gerado_em'] ?? '?'
        ));

        $this->truncarTabelas();

        // ScoringConfig é singleton; se o DatabaseSeeder não foi executado
        // (caso de teste isolado), inicializa com defaults JiCopa.
        if (DB::table('scoring_configs')->count() === 0) {
            DB::table('scoring_configs')->insert([
                'points_per_win'   => 3,
                'points_per_draw'  => 1,
                'points_per_extra' => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        $this->seedEsportes($dados['esportes']);
        $this->seedTurmas($dados['turmas']);
        $this->seedCategorias($dados['categorias']);
        $this->seedAlunos($dados['alunos']);
        $this->seedJogos($dados['jogos']);
        $this->seedPresencas($dados['jogos']);
        $this->seedPenalidades($dados['penalidades']);
        $this->seedBandeiras($dados['bandeiras']);

        $this->command->info('');
        $this->command->info('Reconstrução JiCopa 2025 concluída. Sumário:');
        $this->command->info(sprintf('  Turmas:       %d', DB::table('turmas')->count()));
        $this->command->info(sprintf('  Categorias:   %d', DB::table('categorias')->count()));
        $this->command->info(sprintf('  Esportes:     %d', DB::table('esportes')->count()));
        $this->command->info(sprintf('  Alunos:       %d', DB::table('alunos')->count()));
        $this->command->info(sprintf('  Participação: %d', DB::table('aluno_esporte')->count()));
        $this->command->info(sprintf('  Jogos:        %d', DB::table('jogos')->count()));
        $this->command->info(sprintf('  Presenças:    %d', DB::table('presencas')->count()));
        $this->command->info(sprintf('  Penalidades:  %d', DB::table('penalidades')->count()));
        $this->command->info(sprintf('  Bandeiras:    %d', DB::table('avaliacao_notas')->count()));
    }

    /**
     * Trunca apenas as tabelas de domínio JiCopa.
     * Preserva users, roles, permissions, cache, jobs.
     */
    private function truncarTabelas(): void
    {
        $tabelas = [
            'avaliacao_notas', 'avaliacao_config',
            'penalidades', 'presencas', 'resultado_individual',
            'jogos',
            'aluno_esporte', 'categoria_esporte', 'categoria_turma',
            'alunos', 'categorias', 'esportes', 'turmas',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tabelas as $t) {
            if (Schema::hasTable($t)) {
                DB::table($t)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();
        $this->command->info('Tabelas de domínio truncadas (users/roles preservados).');
    }

    private function seedEsportes(array $esportes): void
    {
        foreach ($esportes as $e) {
            $id = DB::table('esportes')->insertGetId([
                'name'       => $e['name'],
                'type'       => $e['type'],
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->esporteIds[$e['name']] = $id;
        }
        $this->command->info(sprintf('  Esportes inseridos: %d', count($esportes)));
    }

    private function seedTurmas(array $turmas): void
    {
        foreach ($turmas as $t) {
            $id = DB::table('turmas')->insertGetId([
                'name'       => $t['name'],
                'period'     => $t['period'],
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->turmaIds["{$t['name']}|{$t['period']}"] = $id;
        }
        $this->command->info(sprintf('  Turmas inseridas: %d', count($turmas)));
    }

    private function seedCategorias(array $categorias): void
    {
        foreach ($categorias as $c) {
            $catId = DB::table('categorias')->insertGetId([
                'name'       => $c['name'],
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->categoriaIds[$c['name']] = $catId;

            foreach ($c['turmas'] as $turmaCod) {
                [$nome, $per] = self::TURMA_CODIGO_PARA_NOME[$turmaCod] ?? [null, null];
                $turmaId = $this->turmaIds["{$nome}|{$per}"] ?? null;
                if ($turmaId === null) {
                    continue;
                }
                DB::table('categoria_turma')->insert([
                    'categoria_id' => $catId,
                    'turma_id'     => $turmaId,
                ]);
            }
            foreach ($c['esportes'] as $espNome) {
                $espId = $this->esporteIds[$espNome] ?? null;
                if ($espId === null) {
                    continue;
                }
                DB::table('categoria_esporte')->insert([
                    'categoria_id' => $catId,
                    'esporte_id'   => $espId,
                ]);
            }
        }
        $this->command->info(sprintf(
            '  Categorias inseridas: %d (com pivot categoria_turma/esporte)',
            count($categorias)
        ));
    }

    private function seedAlunos(array $alunos): void
    {
        foreach ($alunos as $a) {
            $turmaKey = "{$a['turma']}|{$a['period']}";
            $turmaId = $this->turmaIds[$turmaKey] ?? null;
            if ($turmaId === null) {
                continue;
            }
            $alunoId = DB::table('alunos')->insertGetId([
                'name'       => $a['name'],
                'turma_id'   => $turmaId,
                'period'     => $a['period'],
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // chave de cache: (name, turma, period) — pode haver homônimos
            // em turmas distintas (caso real da planilha 2025).
            $this->alunoIds["{$a['name']}|{$turmaKey}"] = $alunoId;

            foreach ($a['esportes'] as $espNome) {
                $espId = $this->esporteIds[$espNome] ?? null;
                if ($espId === null) {
                    continue;
                }
                DB::table('aluno_esporte')->insertOrIgnore([
                    'aluno_id'   => $alunoId,
                    'esporte_id' => $espId,
                ]);
            }
        }
        $this->command->info(sprintf('  Alunos inseridos: %d', count($alunos)));
    }

    private function seedJogos(array $jogos): void
    {
        $vencedorMap = []; // [jogo_id => 'empate'|turma_id]
        foreach ($jogos as $idx => $j) {
            $catId = $this->categoriaIds[$j['categoria']] ?? null;
            $espId = $this->esporteIds[$j['esporte']] ?? null;
            if ($catId === null || $espId === null) {
                $this->command->warn(sprintf(
                    '  Jogo %d: categoria/esporte não encontrado (%s/%s)',
                    $idx, $j['categoria'], $j['esporte']
                ));
                continue;
            }
            $time1Id = self::turmaIdDeCodigo($j['time1'], $this->turmaIds);
            $time2Id = self::turmaIdDeCodigo($j['time2'], $this->turmaIds);

            $vencedorId = null;
            if (is_string($j['vencedor']) && $j['vencedor'] !== 'empate') {
                $vencedorId = self::turmaIdDeCodigo($j['vencedor'], $this->turmaIds);
            }

            $jogoId = DB::table('jogos')->insertGetId([
                'categoria_id' => $catId,
                'esporte_id'   => $espId,
                'time1_id'     => $time1Id,
                'time2_id'     => $time2Id,
                'data'         => $j['data'],
                'hora'         => $j['hora'],
                'local'        => $j['local'],
                'placar_time1' => $j['placar_time1'],
                'placar_time2' => $j['placar_time2'],
                'vencedor_id'  => $vencedorId,
                'cancelado'    => false,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            $this->jogoIds[] = ['id' => $jogoId, 'jogo' => $j];
        }
        $this->command->info(sprintf('  Jogos inseridos: %d', count($jogos)));
    }

    /**
     * Para cada jogo coletivo: marca presença = true para todos os alunos
     * de time1 e time2 que participam do esporte. Limitação documentada:
     * a planilha original registrava presença geral no campeonato, não
     * por jogo individual.
     */
    private function seedPresencas(array $jogos): void
    {
        $total = 0;
        foreach ($this->jogoIds as $jogo) {
            $j = $jogo['jogo'];
            $espId = $this->esporteIds[$j['esporte']] ?? null;
            if ($espId === null || $j['time1'] === null || $j['time2'] === null) {
                continue; // esporte individual, sem presença por time
            }
            foreach ([$j['time1'], $j['time2']] as $turmaCod) {
                $turmaId = self::turmaIdDeCodigo($turmaCod, $this->turmaIds);
                if ($turmaId === null) {
                    continue;
                }
                $alunos = DB::table('alunos')
                    ->join('aluno_esporte', 'alunos.id', '=', 'aluno_esporte.aluno_id')
                    ->where('alunos.turma_id', $turmaId)
                    ->where('aluno_esporte.esporte_id', $espId)
                    ->select('alunos.id')
                    ->get();
                foreach ($alunos as $aluno) {
                    DB::table('presencas')->insertOrIgnore([
                        'jogo_id'    => $jogo['id'],
                        'aluno_id'   => $aluno->id,
                        'presente'   => true,
                        'is_substituto' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $total++;
                }
            }
        }
        $this->command->info(sprintf('  Presenças inseridas: %d', $total));
    }

    private function seedPenalidades(array $penalidades): void
    {
        $adminId = User::query()->orderBy('id')->value('id');
        if (! $adminId) {
            $this->command->warn('  Nenhum usuário existente — penalidades exigem registrado_por. Pulando.');
            return;
        }
        $count = 0;
        foreach ($penalidades as $p) {
            $turmaId = null;
            if ($p['turma_codigo'] !== null && ! $p['todos']) {
                $turmaId = self::turmaIdDeCodigo($p['turma_codigo'], $this->turmaIds);
            }
            // Para penalidade "Todos" (turma inteira não identificada),
            // aplica a primeira turma como representativa.
            if ($p['todos']) {
                $turmaId = array_values($this->turmaIds)[0];
            }
            if ($turmaId === null) {
                continue;
            }
            DB::table('penalidades')->insert([
                'tipo'           => 'turma',
                'jogo_id'        => null,
                'turma_id'       => $turmaId,
                'aluno_id'       => null,
                'motivo'         => $p['motivo'],
                'pontos'         => $p['pontos'],
                'registrado_por' => $adminId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $count++;
        }
        $this->command->info(sprintf('  Penalidades inseridas: %d', $count));
    }

    private function seedBandeiras(array $bandeiras): void
    {
        $cfg = $bandeiras['config'];
        DB::table('avaliacao_config')->insert([
            'num_jurados'           => $cfg['num_jurados'],
            'nota_min'              => $cfg['nota_min'],
            'nota_max'              => $cfg['nota_max'],
            'pontos_bonus_melhor'   => $cfg['bonus'],
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        $count = 0;
        foreach ($bandeiras['notas'] as $b) {
            $turmaId = self::turmaIdDeCodigo($b['turma_codigo'], $this->turmaIds);
            $catId   = $this->categoriaIds[$b['categoria']] ?? null;
            if ($turmaId === null || $catId === null) {
                continue;
            }
            foreach ($b['notas'] as $idx => $nota) {
                DB::table('avaliacao_notas')->insert([
                    'turma_id'    => $turmaId,
                    'categoria_id'=> $catId,
                    'jurado_num'  => $idx + 1,
                    'nota'        => $nota,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $count++;
            }
        }
        $this->command->info(sprintf('  Bandeiras (notas): %d', $count));
    }

    private static function turmaIdDeCodigo(?string $codigo, array $turmaIds): ?int
    {
        if ($codigo === null) {
            return null;
        }
        [$nome, $per] = self::TURMA_CODIGO_PARA_NOME[$codigo] ?? [null, null];
        if ($nome === null) {
            return null;
        }
        return $turmaIds["{$nome}|{$per}"] ?? null;
    }
}
