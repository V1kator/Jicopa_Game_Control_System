<?php

namespace App\Http\Controllers;

use App\Models\Jogo;
use App\Models\Categoria;
use App\Models\Penalidade;
use App\Services\ScoringService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $scoringService;

    public function __construct(ScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Display dashboard with metrics and charts
     */
    public function index()
    {
        // Total de jogos por status
        $jogosAgendados = Jogo::where('cancelado', false)
            ->whereNull('vencedor_id')
            ->whereNull('placar_time1')
            ->whereDoesntHave('resultadosIndividuais')
            ->count();

        $jogosRealizados = Jogo::where('cancelado', false)
            ->where(function ($query) {
                $query->whereNotNull('vencedor_id')
                    ->orWhereNotNull('placar_time1')
                    ->orWhereHas('resultadosIndividuais');
            })
            ->count();

        $jogosCancelados = Jogo::where('cancelado', true)->count();

        // Top 3 turmas por categoria
        $categorias = Categoria::where('active', true)
            ->with(['turmas' => function ($query) {
                $query->where('active', true);
            }])
            ->orderBy('name')
            ->get();

        $topTurmasPorCategoria = [];
        foreach ($categorias as $categoria) {
            $ranking = [];
            foreach ($categoria->turmas as $turma) {
                $score = $this->scoringService->calculateTurmaScore($turma, $categoria->id);
                $ranking[] = [
                    'turma_id' => $turma->id,
                    'turma_name' => $turma->name,
                    'turma_period' => $turma->period,
                    'pontos_totais' => $score['total'],
                ];
            }

            // Sort by total points desc
            usort($ranking, function ($a, $b) {
                return $b['pontos_totais'] <=> $a['pontos_totais'];
            });

            // Take top 3
            $topTurmasPorCategoria[] = [
                'categoria_id' => $categoria->id,
                'categoria_name' => $categoria->name,
                'top3' => array_slice($ranking, 0, 3),
            ];
        }

        // Total de penalidades
        $totalPenalidades = Penalidade::count();

        // Penalidades por turma (para gráfico de pizza)
        $penalidadesPorTurma = Penalidade::selectRaw('turma_id, COUNT(*) as total')
            ->where('tipo', 'turma')
            ->whereNotNull('turma_id')
            ->groupBy('turma_id')
            ->with('turma:id,name,period')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->turma->name . ' (' . substr($item->turma->period, 0, 1) . ')',
                    'value' => $item->total,
                ];
            });

        // Próximos 5 jogos
        $proximosJogos = Jogo::where('cancelado', false)
            ->whereNull('vencedor_id')
            ->whereNull('placar_time1')
            ->whereDoesntHave('resultadosIndividuais')
            ->with(['categoria', 'esporte', 'time1', 'time2'])
            ->orderBy('data')
            ->orderBy('hora')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'jogos_agendados' => $jogosAgendados,
                'jogos_realizados' => $jogosRealizados,
                'jogos_cancelados' => $jogosCancelados,
                'total_penalidades' => $totalPenalidades,
            ],
            'topTurmasPorCategoria' => $topTurmasPorCategoria,
            'penalidadesPorTurma' => $penalidadesPorTurma,
            'proximosJogos' => $proximosJogos,
        ]);
    }
}
