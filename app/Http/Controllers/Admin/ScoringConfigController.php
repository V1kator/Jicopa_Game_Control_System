<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScoringConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScoringConfigController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/scoring-config/Index', [
            'config' => ScoringConfig::firstOrCreate([], [
                'points_per_win'   => 3,
                'points_per_draw'  => 1,
                'points_per_extra' => 1,
            ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'points_per_win'   => 'required|integer|min:1|max:100',
            'points_per_draw'  => 'required|integer|min:1|max:100',
            'points_per_extra' => 'required|integer|min:1|max:100',
        ]);

        $config = ScoringConfig::first();
        $config->update($data);

        return redirect()->route('admin.scoring-config.index')
            ->with('success', 'Configuração de pontuação atualizada.');
    }
}
