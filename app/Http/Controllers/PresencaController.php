<?php

namespace App\Http\Controllers;

use App\Models\Jogo;
use App\Models\Presenca;
use App\Models\Aluno;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PresencaController extends Controller
{
    /**
     * Show attendance page with pre-filled expected athletes
     */
    public function index(Jogo $jogo)
    {
        $jogo->load([
            'categoria',
            'esporte',
            'time1.alunos.esportes',
            'time1.alunos.turma',
            'time2.alunos.esportes',
            'time2.alunos.turma',
        ]);

        // Pre-fill expected athletes (per D-12)
        $atletasEsperados = collect();

        if ($jogo->esporte->type === 'coletivo' && $jogo->time1 && $jogo->time2) {
            // For collective sports: athletes from both teams registered in this sport
            $time1Atletas = $jogo->time1->alunos->filter(function ($aluno) use ($jogo) {
                return $aluno->esportes->contains('id', $jogo->esporte_id) && $aluno->active;
            });

            $time2Atletas = $jogo->time2->alunos->filter(function ($aluno) use ($jogo) {
                return $aluno->esportes->contains('id', $jogo->esporte_id) && $aluno->active;
            });

            $atletasEsperados = $time1Atletas->merge($time2Atletas);
        } else {
            // For individual sports: all athletes from category registered in this sport
            $atletasEsperados = Aluno::whereHas('turma.categorias', function ($query) use ($jogo) {
                $query->where('categorias.id', $jogo->categoria_id);
            })
            ->whereHas('esportes', function ($query) use ($jogo) {
                $query->where('esportes.id', $jogo->esporte_id);
            })
            ->where('active', true)
            ->with('turma')
            ->get();
        }

        // Load existing presencas
        $presencas = Presenca::where('jogo_id', $jogo->id)
            ->with('aluno.turma')
            ->get();

        // Get ALL active students for substituto dropdown (any turma)
        $todosAlunos = Aluno::where('active', true)
            ->with('turma')
            ->orderBy('name')
            ->get();

        return Inertia::render('presenca/Index', [
            'jogo' => $jogo,
            'atletasEsperados' => $atletasEsperados->values(),
            'presencas' => $presencas,
            'todosAlunos' => $todosAlunos->values(),
        ]);
    }

    /**
     * Store or update attendance records
     */
    public function store(Request $request, Jogo $jogo)
    {
        $validated = $request->validate([
            'presencas' => 'required|array',
            'presencas.*.aluno_id' => 'required|exists:alunos,id',
            'presencas.*.presente' => 'required|boolean',
            'presencas.*.is_substituto' => 'boolean',
            'presencas.*.substituto_de_time_id' => 'nullable|integer|exists:turmas,id',
        ]);

        // Get list of aluno_ids being sent
        $alunoIds = collect($validated['presencas'])->pluck('aluno_id')->toArray();

        // Delete presencas that are not in the submitted list
        Presenca::where('jogo_id', $jogo->id)
            ->whereNotIn('aluno_id', $alunoIds)
            ->delete();

        // Update or create presencas for submitted alunos
        foreach ($validated['presencas'] as $presencaData) {
            Presenca::updateOrCreate(
                [
                    'jogo_id' => $jogo->id,
                    'aluno_id' => $presencaData['aluno_id'],
                ],
                [
                    'presente' => $presencaData['presente'],
                    'is_substituto' => $presencaData['is_substituto'] ?? false,
                    'substituto_de_time_id' => $presencaData['substituto_de_time_id'] ?? null,
                ]
            );
        }

        return redirect()->route('calendario.index')
            ->with('success', 'Presença registrada com sucesso.');
    }
}
