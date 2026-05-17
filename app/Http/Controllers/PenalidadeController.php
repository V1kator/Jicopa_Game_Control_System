<?php

namespace App\Http\Controllers;

use App\Models\Penalidade;
use App\Models\Turma;
use App\Models\Aluno;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PenalidadeController extends Controller
{
    /**
     * Display a listing of penalties
     */
    public function index()
    {
        $penalidades = Penalidade::with(['turma', 'aluno.turma', 'registradoPor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('penalidades/Index', [
            'penalidades' => $penalidades,
            'turmas' => Turma::where('active', true)->get(),
            'alunos' => Aluno::where('active', true)->with('turma')->get(),
        ]);
    }

    /**
     * Store a newly created penalty
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:turma,aluno',
            'turma_id' => 'required_if:tipo,turma|nullable|exists:turmas,id',
            'aluno_id' => 'required_if:tipo,aluno|nullable|exists:alunos,id',
            'motivo' => 'required|string|max:1000',
            'pontos' => 'required|integer|min:1',
        ]);

        // Set registrado_por from authenticated user (T-03-06: never trust request input)
        $validated['registrado_por'] = Auth::id();

        Penalidade::create($validated);

        return redirect()->route('penalidades.index')
            ->with('success', 'Penalidade registrada com sucesso.');
    }

    /**
     * Update the specified penalty
     */
    public function update(Request $request, Penalidade $penalidade)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:turma,aluno',
            'turma_id' => 'required_if:tipo,turma|nullable|exists:turmas,id',
            'aluno_id' => 'required_if:tipo,aluno|nullable|exists:alunos,id',
            'motivo' => 'required|string|max:1000',
            'pontos' => 'required|integer|min:1',
        ]);

        // Keep original registrado_por (do not allow changing who registered it)
        $penalidade->update($validated);

        return redirect()->route('penalidades.index')
            ->with('success', 'Penalidade atualizada com sucesso.');
    }

    /**
     * Remove the specified penalty (hard delete per D-19)
     */
    public function destroy(Penalidade $penalidade)
    {
        $penalidade->delete();

        return redirect()->route('penalidades.index')
            ->with('success', 'Penalidade removida com sucesso.');
    }
}
