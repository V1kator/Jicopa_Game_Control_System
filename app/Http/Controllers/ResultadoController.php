<?php

namespace App\Http\Controllers;

use App\Models\Jogo;
use App\Models\ResultadoIndividual;
use App\Models\Aluno;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ResultadoController extends Controller
{
    /**
     * Show form to edit game result (adaptive based on sport type)
     */
    public function edit(Jogo $jogo)
    {
        $jogo->load(['categoria', 'esporte', 'time1', 'time2', 'vencedor', 'resultadosIndividuais.aluno']);

        // For individual sports, load only athletes with attendance marked as present
        $atletas = [];
        if ($jogo->esporte->type === 'individual') {
            $atletas = Aluno::whereHas('presencas', function ($query) use ($jogo) {
                $query->where('jogo_id', $jogo->id)
                      ->where('presente', true);
            })
            ->where('active', true)
            ->with('turma')
            ->get();
        }

        return Inertia::render('resultados/Edit', [
            'jogo' => $jogo,
            'atletas' => $atletas,
        ]);
    }

    /**
     * Update game result (adaptive based on sport type)
     */
    public function update(Request $request, Jogo $jogo)
    {
        $jogo->load('esporte');

        if ($jogo->esporte->type === 'coletivo') {
            return $this->updateCollectiveResult($request, $jogo);
        } else {
            return $this->updateIndividualResult($request, $jogo);
        }
    }

    /**
     * Update collective sport result (placar)
     */
    private function updateCollectiveResult(Request $request, Jogo $jogo)
    {
        $validated = $request->validate([
            'placar_time1' => 'nullable|integer|min:0',
            'placar_time2' => 'nullable|integer|min:0',
        ]);

        // Calculate winner
        $vencedor_id = null;
        if ($validated['placar_time1'] !== null && $validated['placar_time2'] !== null) {
            if ($validated['placar_time1'] > $validated['placar_time2']) {
                $vencedor_id = $jogo->time1_id;
            } elseif ($validated['placar_time2'] > $validated['placar_time1']) {
                $vencedor_id = $jogo->time2_id;
            }
            // else: empate, vencedor_id stays null
        }

        $jogo->update([
            'placar_time1' => $validated['placar_time1'],
            'placar_time2' => $validated['placar_time2'],
            'vencedor_id' => $vencedor_id,
        ]);

        return redirect()->route('calendario.index')
            ->with('success', 'Resultado registrado com sucesso.');
    }

    /**
     * Update individual sport result (positions)
     */
    private function updateIndividualResult(Request $request, Jogo $jogo)
    {
        $validated = $request->validate([
            'resultados' => 'required|array',
            'resultados.*.aluno_id' => 'required|exists:alunos,id',
            'resultados.*.posicao' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($jogo, $validated) {
            // Delete existing results
            $jogo->resultadosIndividuais()->delete();

            // Create new results
            foreach ($validated['resultados'] as $resultado) {
                ResultadoIndividual::create([
                    'jogo_id' => $jogo->id,
                    'aluno_id' => $resultado['aluno_id'],
                    'posicao' => $resultado['posicao'],
                ]);
            }
        });

        return redirect()->route('calendario.index')
            ->with('success', 'Resultado registrado com sucesso.');
    }
}
