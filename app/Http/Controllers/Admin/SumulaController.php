<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jogo;
use Barryvdh\DomPDF\Facade\Pdf;

class SumulaController extends Controller
{
    /**
     * Export game sumula as PDF
     */
    public function show(Jogo $jogo)
    {
        // Validate that game is not cancelled
        if ($jogo->cancelado) {
            return back()->withErrors([
                'error' => 'Não é possível gerar súmula de jogo cancelado.',
            ]);
        }

        // Load all necessary relationships
        $jogo->load([
            'categoria',
            'esporte',
            'time1',
            'time2',
            'vencedor',
            'presencas.aluno.turma',
            'resultadosIndividuais.aluno.turma',
        ]);

        // Separate presencas by team for organized display
        // Includes substitutos from other turmas that were assigned to each time
        $presencasTime1 = $jogo->presencas->filter(function ($presenca) use ($jogo) {
            return $presenca->aluno->turma_id === $jogo->time1_id
                || $presenca->substituto_de_time_id === $jogo->time1_id;
        });

        $presencasTime2 = $jogo->presencas->filter(function ($presenca) use ($jogo) {
            return $presenca->aluno->turma_id === $jogo->time2_id
                || $presenca->substituto_de_time_id === $jogo->time2_id;
        });

        // Generate PDF
        $pdf = Pdf::loadView('pdf.sumula', compact('jogo', 'presencasTime1', 'presencasTime2'));

        // Download PDF with descriptive filename
        return $pdf->download("sumula-jogo-{$jogo->id}.pdf");
    }
}
