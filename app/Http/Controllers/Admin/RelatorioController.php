<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Turma;
use App\Models\Penalidade;
use App\Services\ScoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class RelatorioController extends Controller
{
    protected $scoringService;

    public function __construct(ScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Show reports index page
     */
    public function index()
    {
        $categorias = Categoria::where('active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('relatorios/Index', [
            'categorias' => $categorias,
        ]);
    }

    /**
     * Generate general report PDF (all categories ranking)
     */
    public function geral()
    {
        $categorias = Categoria::where('active', true)
            ->with(['turmas' => function ($query) {
                $query->where('active', true);
            }])
            ->orderBy('name')
            ->get();

        $rankingData = [];
        $totais = [
            'total_jogos' => 0,
            'total_turmas' => 0,
            'total_penalidades' => 0,
        ];

        foreach ($categorias as $categoria) {
            $ranking = [];
            foreach ($categoria->turmas as $turma) {
                $score = $this->scoringService->calculateTurmaScore($turma, $categoria->id);
                $ranking[] = [
                    'turma' => $turma,
                    'score' => $score,
                ];
            }

            // Sort by total points desc, then by saldo_gols desc
            usort($ranking, function ($a, $b) {
                if ($b['score']['total'] !== $a['score']['total']) {
                    return $b['score']['total'] <=> $a['score']['total'];
                }
                return $b['score']['saldo_gols'] <=> $a['score']['saldo_gols'];
            });

            $rankingData[] = [
                'categoria' => $categoria,
                'ranking' => $ranking,
            ];

            $totais['total_turmas'] += count($categoria->turmas);
        }

        $totais['total_jogos'] = \App\Models\Jogo::whereNotNull('vencedor_id')
            ->orWhereNotNull('placar_time1')
            ->count();
        $totais['total_penalidades'] = Penalidade::count();

        $pdf = Pdf::loadView('pdf.relatorio-geral', [
            'rankingData' => $rankingData,
            'totais' => $totais,
            'data_geracao' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('relatorio-geral-jicopa.pdf');
    }

    /**
     * Generate category-specific report PDF
     */
    public function porCategoria(Categoria $categoria)
    {
        $categoria->load(['turmas' => function ($query) {
            $query->where('active', true);
        }]);

        $ranking = [];
        foreach ($categoria->turmas as $turma) {
            $score = $this->scoringService->calculateTurmaScore($turma, $categoria->id);
            $ranking[] = [
                'turma' => $turma,
                'score' => $score,
            ];
        }

        // Sort by total points desc, then by saldo_gols desc
        usort($ranking, function ($a, $b) {
            if ($b['score']['total'] !== $a['score']['total']) {
                return $b['score']['total'] <=> $a['score']['total'];
            }
            return $b['score']['saldo_gols'] <=> $a['score']['saldo_gols'];
        });

        // Get penalties for this category
        $penalidades = Penalidade::whereHas('turma.categorias', function ($query) use ($categoria) {
            $query->where('categorias.id', $categoria->id);
        })
        ->with('turma')
        ->orderBy('created_at', 'desc')
        ->get();

        $pdf = Pdf::loadView('pdf.relatorio-categoria', [
            'categoria' => $categoria,
            'ranking' => $ranking,
            'penalidades' => $penalidades,
            'data_geracao' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download("relatorio-categoria-{$categoria->name}.pdf");
    }
}
