<?php

namespace App\Services;

use App\Models\Turma;
use App\Models\Categoria;
use App\Models\ScoringConfig;
use App\Models\AvaliacaoConfig;
use App\Models\ResultadoIndividual;
use App\Models\Penalidade;
use Illuminate\Support\Collection;

class ScoringService
{
    /**
     * Calculate total score for a turma in a specific categoria
     *
     * @param Turma $turma
     * @param int $categoriaId
     * @return array
     */
    public function calculateTurmaScore(Turma $turma, int $categoriaId): array
    {
        $config = ScoringConfig::first();

        // Calculate victory count and points
        $vitorias = $this->calculateVictoryCount($turma, $categoriaId);
        $pontosVitorias = $vitorias * $config->points_per_win;

        // Calculate draw count and points
        $empates = $this->calculateDrawCount($turma, $categoriaId);
        $pontosEmpates = $empates * $config->points_per_draw;

        // Calculate loss count
        $derrotas = $this->calculateLossCount($turma, $categoriaId);

        // Calculate points from individual sports
        $pontosIndividuais = $this->calculateIndividualSportsPoints($turma, $categoriaId);

        // Calculate points from evaluation (base score)
        $pontosAvaliacaoBase = $turma->avaliacaoNotas()
            ->where('categoria_id', $categoriaId)
            ->sum('nota');

        // Calculate bonus for best evaluation (only the bonus enters the total, not the raw sum)
        $bonusAvaliacao = $this->calculateEvaluationBonus($turma, $categoriaId);

        // Calculate penalty deductions
        $penalidades = $this->calculatePenalties($turma);

        // Calculate goal differential
        $saldoGols = $this->calculateGoalDifferential($turma, $categoriaId);

        // Calculate points from games (victories + draws + individual sports)
        $pontosJogos = $pontosVitorias + $pontosEmpates + $pontosIndividuais;

        // Calculate total — pontos_avaliacao_base is informative only, NOT added to total
        $total = $pontosJogos + $bonusAvaliacao - $penalidades;

        return [
            'vitorias' => $vitorias,
            'empates' => $empates,
            'derrotas' => $derrotas,
            'pontos_vitorias' => $pontosVitorias,
            'pontos_empates' => $pontosEmpates,
            'pontos_individuais' => $pontosIndividuais,
            'pontos_jogos' => $pontosJogos,
            'pontos_avaliacao_base' => $pontosAvaliacaoBase, // Informativo — não entra no total
            'bonus_avaliacao' => $bonusAvaliacao,
            'penalidades' => $penalidades,
            'total' => $total,
            'saldo_gols' => $saldoGols,
            'saldo' => $saldoGols, // Alias for backward compatibility
            'pontos_totais' => $total, // Alias for backward compatibility
        ];
    }

    /**
     * Get ranking for a specific categoria
     *
     * @param int $categoriaId
     * @return Collection
     */
    public function getRankingByCategoria(int $categoriaId): Collection
    {
        $categoria = Categoria::with('turmas')->find($categoriaId);

        if (!$categoria) {
            return collect([]);
        }

        // Calculate scores for all turmas
        $ranking = $categoria->turmas->map(function ($turma) use ($categoriaId) {
            return [
                'turma' => $turma,
                'score' => $this->calculateTurmaScore($turma, $categoriaId),
            ];
        });

        // Sort by total DESC, saldo_gols DESC, turma name ASC
        $ranking = $ranking->sortBy([
            fn($a, $b) => $b['score']['total'] <=> $a['score']['total'],
            fn($a, $b) => $b['score']['saldo_gols'] <=> $a['score']['saldo_gols'],
            fn($a, $b) => $a['turma']['name'] <=> $b['turma']['name'],
        ]);

        // Add position field
        $posicao = 1;
        $ranking = $ranking->map(function ($item) use (&$posicao) {
            $item['posicao'] = $posicao++;
            return $item;
        });

        return $ranking->values();
    }

    /**
     * Calculate points from victories
     *
     * @param Turma $turma
     * @param int $categoriaId
     * @param ScoringConfig $config
     * @return int
     */
    private function calculateVictoryPoints(Turma $turma, int $categoriaId, ScoringConfig $config): int
    {
        $vitoriasComoTime1 = $turma->jogosComoTime1()
            ->where('vencedor_id', $turma->id)
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->count();

        $vitoriasComoTime2 = $turma->jogosComoTime2()
            ->where('vencedor_id', $turma->id)
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->count();

        $totalVitorias = $vitoriasComoTime1 + $vitoriasComoTime2;

        return $totalVitorias * $config->points_per_win;
    }

    /**
     * Calculate points from draws
     *
     * @param Turma $turma
     * @param int $categoriaId
     * @param ScoringConfig $config
     * @return int
     */
    private function calculateDrawPoints(Turma $turma, int $categoriaId, ScoringConfig $config): int
    {
        $empatesComoTime1 = $turma->jogosComoTime1()
            ->whereNull('vencedor_id')
            ->whereNotNull('placar_time1')
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->count();

        $empatesComoTime2 = $turma->jogosComoTime2()
            ->whereNull('vencedor_id')
            ->whereNotNull('placar_time2')
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->count();

        $totalEmpates = $empatesComoTime1 + $empatesComoTime2;

        return $totalEmpates * $config->points_per_draw;
    }

    /**
     * Calculate points from individual sports
     *
     * @param Turma $turma
     * @param int $categoriaId
     * @return int
     */
    private function calculateIndividualSportsPoints(Turma $turma, int $categoriaId): int
    {
        $resultados = ResultadoIndividual::whereHas('aluno', function ($query) use ($turma) {
            $query->where('turma_id', $turma->id);
        })
        ->whereHas('jogo', function ($query) use ($categoriaId) {
            $query->where('categoria_id', $categoriaId)
                ->where('cancelado', false)
                ->whereHas('esporte', function ($q) {
                    $q->where('type', 'individual');
                });
        })
        ->get();

        $totalPontos = 0;

        foreach ($resultados as $resultado) {
            $totalPontos += match ($resultado->posicao) {
                1 => 3,
                default => 0,
            };
        }

        return $totalPontos;
    }

    /**
     * Calculate penalty deductions
     *
     * @param Turma $turma
     * @return int
     */
    private function calculatePenalties(Turma $turma): int
    {
        // Turma-level penalties
        $penalidadesTurma = Penalidade::where('tipo', 'turma')
            ->where('turma_id', $turma->id)
            ->sum('pontos');

        // Aluno-level penalties
        $penalidadesAluno = Penalidade::where('tipo', 'aluno')
            ->whereHas('aluno', function ($query) use ($turma) {
                $query->where('turma_id', $turma->id);
            })
            ->sum('pontos');

        return $penalidadesTurma + $penalidadesAluno;
    }

    /**
     * Calculate goal differential (tiebreaker)
     *
     * @param Turma $turma
     * @param int $categoriaId
     * @return int
     */
    private function calculateGoalDifferential(Turma $turma, int $categoriaId): int
    {
        $golsMarcados = 0;
        $golsSofridos = 0;

        // As time1
        $jogosComoTime1 = $turma->jogosComoTime1()
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->whereNotNull('placar_time1')
            ->get();

        foreach ($jogosComoTime1 as $jogo) {
            $golsMarcados += $jogo->placar_time1;
            $golsSofridos += $jogo->placar_time2;
        }

        // As time2
        $jogosComoTime2 = $turma->jogosComoTime2()
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->whereNotNull('placar_time2')
            ->get();

        foreach ($jogosComoTime2 as $jogo) {
            $golsMarcados += $jogo->placar_time2;
            $golsSofridos += $jogo->placar_time1;
        }

        return $golsMarcados - $golsSofridos;
    }

    /**
     * Calculate bonus for best evaluation in categoria
     * 
     * @param Turma $turma
     * @param int $categoriaId
     * @return int
     */
    private function calculateEvaluationBonus(Turma $turma, int $categoriaId): int
    {
        $avaliacaoConfig = AvaliacaoConfig::first();
        
        if (!$avaliacaoConfig || $avaliacaoConfig->pontos_bonus_melhor <= 0) {
            return 0;
        }
        
        $categoria = Categoria::with('turmas')->find($categoriaId);
        
        if (!$categoria) {
            return 0;
        }
        
        // Calculate total evaluation score for each turma in this categoria
        $scores = [];
        foreach ($categoria->turmas as $t) {
            $totalNota = $t->avaliacaoNotas()
                ->where('categoria_id', $categoriaId)
                ->sum('nota');
            
            if ($totalNota > 0) {
                $scores[$t->id] = $totalNota;
            }
        }
        
        if (empty($scores)) {
            return 0;
        }
        
        // Find the highest score
        $maxScore = max($scores);
        
        // Find all turmas with the highest score (handles ties)
        $leaders = array_keys(array_filter($scores, fn($score) => $score == $maxScore));
        
        // Check if current turma is a leader
        if (!in_array($turma->id, $leaders)) {
            return 0;
        }
        
        // If only one leader: full bonus
        if (count($leaders) === 1) {
            return $avaliacaoConfig->pontos_bonus_melhor;
        }
        
        // If multiple leaders (tie): 1 point each
        return 1;
    }

    /**
     * Calculate victory count
     *
     * @param Turma $turma
     * @param int $categoriaId
     * @return int
     */
    private function calculateVictoryCount(Turma $turma, int $categoriaId): int
    {
        $vitoriasComoTime1 = $turma->jogosComoTime1()
            ->where('vencedor_id', $turma->id)
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->count();

        $vitoriasComoTime2 = $turma->jogosComoTime2()
            ->where('vencedor_id', $turma->id)
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->count();

        return $vitoriasComoTime1 + $vitoriasComoTime2;
    }

    /**
     * Calculate draw count
     *
     * @param Turma $turma
     * @param int $categoriaId
     * @return int
     */
    private function calculateDrawCount(Turma $turma, int $categoriaId): int
    {
        $empatesComoTime1 = $turma->jogosComoTime1()
            ->whereNull('vencedor_id')
            ->whereNotNull('placar_time1')
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->count();

        $empatesComoTime2 = $turma->jogosComoTime2()
            ->whereNull('vencedor_id')
            ->whereNotNull('placar_time2')
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->count();

        return $empatesComoTime1 + $empatesComoTime2;
    }

    /**
     * Calculate loss count
     *
     * @param Turma $turma
     * @param int $categoriaId
     * @return int
     */
    private function calculateLossCount(Turma $turma, int $categoriaId): int
    {
        $derrotasComoTime1 = $turma->jogosComoTime1()
            ->whereNotNull('vencedor_id')
            ->where('vencedor_id', '!=', $turma->id)
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->count();

        $derrotasComoTime2 = $turma->jogosComoTime2()
            ->whereNotNull('vencedor_id')
            ->where('vencedor_id', '!=', $turma->id)
            ->where('categoria_id', $categoriaId)
            ->where('cancelado', false)
            ->count();

        return $derrotasComoTime1 + $derrotasComoTime2;
    }
}
