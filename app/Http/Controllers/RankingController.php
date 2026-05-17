<?php

namespace App\Http\Controllers;

use App\Services\ScoringService;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RankingController extends Controller
{
    public function __construct(
        private ScoringService $scoringService
    ) {}

    public function index(Request $request): Response
    {
        // Get categoria_id from query param, default to first active categoria
        $categoriaId = $request->input('categoria_id');
        
        if (!$categoriaId) {
            $firstCategoria = Categoria::where('active', true)->first();
            $categoriaId = $firstCategoria ? $firstCategoria->id : null;
        }
        
        // If no active categoria exists, return empty ranking
        if (!$categoriaId) {
            return Inertia::render('ranking/Index', [
                'ranking' => [],
                'categorias' => [],
                'categoriaAtual' => null,
                'lastUpdated' => now()->toISOString(),
            ]);
        }
        
        // Get ranking from ScoringService
        $ranking = $this->scoringService->getRankingByCategoria((int) $categoriaId);
        
        // Load all active categorias for filter dropdown
        $categorias = Categoria::where('active', true)->get();
        
        return Inertia::render('ranking/Index', [
            'ranking' => $ranking,
            'categorias' => $categorias,
            'categoriaAtual' => (int) $categoriaId,
            'lastUpdated' => now()->toISOString(),
        ]);
    }
}
