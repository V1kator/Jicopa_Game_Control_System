<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JicopaPlanilhasSeeder extends Seeder
{
    public function run(): void
    {
        // Esportes
        $esportes = [
            ['name' => 'Futebol',        'type' => 'coletivo'],
            ['name' => 'Futsal',         'type' => 'coletivo'],
            ['name' => 'Queimada',       'type' => 'coletivo'],
            ['name' => 'Rouba Bandeira', 'type' => 'coletivo'],
            ['name' => 'Basquete',       'type' => 'coletivo'],
            ['name' => 'Vôlei',          'type' => 'coletivo'],
            ['name' => 'Taekwondo',      'type' => 'individual'],
            ['name' => 'Ginástica',      'type' => 'individual'],
        ];

        foreach ($esportes as $e) {
            DB::table('esportes')->insertOrIgnore(array_merge($e, [
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $esporteIds = DB::table('esportes')->pluck('id', 'name');

        // Turmas (nome + período)
        $turmas = [
            ['name' => 'Turma A',  'period' => 'Matutino'],
            ['name' => 'Turma A',  'period' => 'Vespertino'],
            ['name' => 'Turma B1', 'period' => 'Matutino'],
            ['name' => 'Turma B1', 'period' => 'Vespertino'],
            ['name' => 'Turma B2', 'period' => 'Matutino'],
            ['name' => 'Turma B2', 'period' => 'Vespertino'],
            ['name' => 'Turma C',  'period' => 'Matutino'],
            ['name' => 'Turma C',  'period' => 'Vespertino'],
            ['name' => 'Turma D',  'period' => 'Matutino'],
            ['name' => 'Turma D',  'period' => 'Vespertino'],
            ['name' => 'Turma E',  'period' => 'Matutino'],
            ['name' => 'Turma E',  'period' => 'Vespertino'],
            ['name' => 'Turma GR', 'period' => 'Vespertino'],
        ];

        foreach ($turmas as $t) {
            DB::table('turmas')->insertOrIgnore(array_merge($t, [
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Cache turma_id por (name, period)
        $turmaRows = DB::table('turmas')->get();
        $turmaIds  = [];
        foreach ($turmaRows as $row) {
            $turmaIds["{$row->name}|{$row->period}"] = $row->id;
        }

        // Alunos com seus esportes
        // Formato: [nome, turma, período, [esportes...]]
        $alunos = [
            ['ADILSON EVÊNCIO DA SILVA JUNIOR', 'Turma E',  'Matutino',   ['Futebol','Futsal','Queimada','Rouba Bandeira','Basquete','Vôlei']],
            ['Ágata Emanuelli Rocha Antônio',   'Turma C',  'Matutino',   []],
            ['AGATA LORRAINE ARAÚJO DA COSTA',  'Turma A',  'Vespertino', ['Rouba Bandeira','Taekwondo','Ginástica']],
            ['ÁGATA LORRAINE MARQUES MIRANDA',  'Turma C',  'Vespertino', []],
            ['AGATHA GEOVANNA CAMARGO FEITOSA', 'Turma A',  'Vespertino', []],
            ['Ágatha Sophhie Da Silva Basílio', 'Turma A',  'Matutino',   ['Queimada','Rouba Bandeira','Ginástica']],
            ['ALICE EMANUELLY GONZAGA SILVA',   'Turma B1', 'Vespertino', []],
            ['ALIPHER GABRIEL LOPES FERREIRA',  'Turma B2', 'Vespertino', ['Futebol','Futsal']],
            ['ALLAN ALVES RIBEIRO',             'Turma E',  'Vespertino', ['Futebol','Futsal','Queimada','Rouba Bandeira','Basquete','Vôlei','Taekwondo']],
            ['ALLANA FERREIRA DA SILVA',        'Turma B1', 'Vespertino', ['Queimada','Rouba Bandeira','Taekwondo','Ginástica']],
            ['Allana Gonçalves Da Silva',       'Turma C',  'Matutino',   ['Futebol','Queimada','Rouba Bandeira','Taekwondo']],
            ['Ana Beatriz Alves De Souza',      'Turma D',  'Vespertino', []],
            ['ANA BEATRIZ FLAUZINO DOS SANTOS', 'Turma A',  'Vespertino', ['Queimada','Rouba Bandeira']],
            ['Ana Carolina Ventura Dos Santos', 'Turma D',  'Vespertino', []],
            ['ANA CLARA DE FREITAS TEIXEIRA',   'Turma GR', 'Vespertino', ['Futebol','Futsal','Queimada','Rouba Bandeira','Basquete','Vôlei','Ginástica']],
            ['ANA CLARA VIEIRA DA SILVA',       'Turma GR', 'Vespertino', []],
            ['ANA CRISTINA SOUZA LOBAK',        'Turma E',  'Vespertino', ['Futebol','Futsal','Vôlei']],
            ['Ana Julia Da Cruz Oliveira',      'Turma A',  'Matutino',   ['Futebol','Queimada','Rouba Bandeira','Taekwondo','Ginástica']],
            ['Ana Julia Da Silva Souza',        'Turma A',  'Matutino',   []],
        ];

        foreach ($alunos as [$nome, $turma, $periodo, $esportesAluno]) {
            $turmaKey = "{$turma}|{$periodo}";
            $turmaId  = $turmaIds[$turmaKey] ?? null;

            if (! $turmaId) {
                $this->command->warn("Turma não encontrada: {$turmaKey}");
                continue;
            }

            $alunoId = DB::table('alunos')->insertGetId([
                'name'       => $nome,
                'turma_id'   => $turmaId,
                'period'     => $periodo,
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($esportesAluno as $esporteNome) {
                $esporteId = $esporteIds[$esporteNome] ?? null;
                if ($esporteId) {
                    DB::table('aluno_esporte')->insertOrIgnore([
                        'aluno_id'   => $alunoId,
                        'esporte_id' => $esporteId,
                    ]);
                }
            }
        }
    }
}
