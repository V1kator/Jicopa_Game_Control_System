<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvaliacaoConfig;
use App\Models\AvaliacaoNota;
use App\Models\Categoria;
use App\Models\Turma;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AvaliacaoNotaController extends Controller
{
    /**
     * Show score grid page
     */
    public function index(Request $request)
    {
        $config = AvaliacaoConfig::first();

        if (!$config) {
            return redirect()->route('admin.avaliacao-config.index')
                ->with('error', 'Configure a avaliação antes de registrar notas.');
        }

        $categoriaId = $request->input('categoria_id');
        $categorias = Categoria::where('active', true)->get();

        $turmas = collect();
        $notas = [];

        if ($categoriaId) {
            $turmas = Turma::whereHas('categorias', function ($query) use ($categoriaId) {
                $query->where('categorias.id', $categoriaId);
            })
                ->where('active', true)
                ->get();

            // Load existing notas for this category
            $notasExistentes = AvaliacaoNota::where('categoria_id', $categoriaId)
                ->get()
                ->groupBy('turma_id');

            foreach ($notasExistentes as $turmaId => $turmaNotas) {
                $notas[$turmaId] = $turmaNotas->pluck('nota', 'jurado_num')->toArray();
            }
        }

        return Inertia::render('admin/avaliacao/Notas', [
            'config' => $config,
            'categorias' => $categorias,
            'turmas' => $turmas,
            'notas' => $notas,
            'categoriaId' => $categoriaId ? (int) $categoriaId : null,
        ]);
    }

    /**
     * Store or update scores for a category
     */
    public function store(Request $request)
    {
        $config = AvaliacaoConfig::first();

        if (!$config) {
            return back()->with('error', 'Configuração de avaliação não encontrada.');
        }

        $validated = $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'notas' => 'required|array',
            'notas.*.turma_id' => 'required|exists:turmas,id',
            'notas.*.jurado_num' => 'required|integer|min:1|max:' . $config->num_jurados,
            'notas.*.nota' => 'required|numeric|min:' . $config->nota_min . '|max:' . $config->nota_max,
        ]);

        foreach ($validated['notas'] as $notaData) {
            AvaliacaoNota::updateOrCreate(
                [
                    'turma_id' => $notaData['turma_id'],
                    'categoria_id' => $validated['categoria_id'],
                    'jurado_num' => $notaData['jurado_num'],
                ],
                [
                    'nota' => $notaData['nota'],
                ]
            );
        }

        return redirect()->route('admin.avaliacao-notas.index', ['categoria_id' => $validated['categoria_id']])
            ->with('success', 'Notas salvas com sucesso.');
    }
}
