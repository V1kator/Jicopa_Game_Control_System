<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Turma;
use App\Models\Categoria;
use App\Models\Jogo;
use App\Services\ScoringService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class BoletimController extends Controller
{
    public function __construct(private ScoringService $scoringService)
    {
    }

    public function show($turmaId, Request $request)
    {
        // Validate categoria_id is provided
        $categoriaId = $request->input('categoria_id');
        if (!$categoriaId) {
            return back()->with('error', 'Categoria é obrigatória para gerar boletim');
        }

        // Load turma
        $turma = Turma::findOrFail($turmaId);

        // Load categoria
        $categoria = Categoria::findOrFail($categoriaId);

        // Calculate score for turma
        $score = $this->scoringService->calculateTurmaScore($turma, $categoriaId);

        // Get ranking to determine position
        $ranking = $this->scoringService->getRankingByCategoria($categoriaId);
        $posicao = $ranking->search(fn($item) => $item['turma']->id === $turma->id) + 1;

        // Load game history for turma in categoria
        $jogos = Jogo::where('categoria_id', $categoriaId)
            ->where(fn($q) => $q->where('time1_id', $turma->id)->orWhere('time2_id', $turma->id))
            ->where('cancelado', false)
            ->whereNotNull('vencedor_id') // only finalized games
            ->with(['esporte', 'time1', 'time2', 'vencedor'])
            ->orderBy('data')
            ->get();

        // Generate PDF
        $pdf = PDF::loadView('pdf.boletim', compact('turma', 'categoria', 'score', 'posicao', 'jogos', 'ranking'));
        return $pdf->download("boletim-{$turma->name}-{$categoria->name}.pdf");
    }
}
