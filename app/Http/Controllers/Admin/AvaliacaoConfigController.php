<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvaliacaoConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AvaliacaoConfigController extends Controller
{
    /**
     * Show config page (singleton pattern)
     */
    public function index()
    {
        $config = AvaliacaoConfig::first();

        return Inertia::render('admin/avaliacao/Config', [
            'config' => $config,
        ]);
    }

    /**
     * Store or update config (singleton pattern)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'num_jurados' => 'required|integer|min:1|max:10',
            'nota_min' => 'required|numeric|min:0',
            'nota_max' => 'required|numeric|gt:nota_min',
            'pontos_bonus_melhor' => 'required|integer|min:0',
        ]);

        $config = AvaliacaoConfig::first();

        if ($config) {
            $config->update($validated);
        } else {
            AvaliacaoConfig::create($validated);
        }

        return redirect()->route('admin.avaliacao-config.index')
            ->with('success', 'Configuração salva com sucesso.');
    }
}
