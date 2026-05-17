<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\Esporte;
use App\Models\Turma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlunoController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Aluno::with('turma', 'esportes');

        // Filter by turma
        if ($request->filled('turma_id')) {
            $query->where('turma_id', $request->turma_id);
        }

        // Filter by period
        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('active', $request->active === 'true');
        }

        $alunos = $query->orderBy('name')->get();

        $turmas = Turma::where('active', true)
            ->orderBy('period')
            ->orderBy('name')
            ->get(['id', 'name', 'period']);

        return Inertia::render('admin/alunos/Index', [
            'alunos' => $alunos,
            'turmas' => $turmas,
            'filters' => $request->only(['turma_id', 'period', 'active']),
        ]);
    }

    public function create(): Response
    {
        $turmas = Turma::where('active', true)
            ->orderBy('period')
            ->orderBy('name')
            ->get(['id', 'name', 'period']);

        $esportes = Esporte::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/alunos/Create', [
            'turmas' => $turmas,
            'esportes' => $esportes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'turma_id' => 'required|exists:turmas,id',
            'period' => 'required|in:Matutino,Vespertino',
            'esportes' => 'nullable|array',
            'esportes.*' => 'exists:esportes,id',
        ]);

        // Validate period matches turma period
        $turma = Turma::findOrFail($validated['turma_id']);
        if ($turma->period !== $validated['period']) {
            return back()->withErrors([
                'period' => 'Período do aluno deve corresponder ao período da turma selecionada.'
            ])->withInput();
        }

        $aluno = Aluno::create([
            'name' => $validated['name'],
            'turma_id' => $validated['turma_id'],
            'period' => $validated['period'],
            'active' => true,
        ]);

        if (!empty($validated['esportes'])) {
            $aluno->esportes()->sync($validated['esportes']);
        }

        return redirect()->route('admin.alunos.index')
            ->with('success', 'Aluno criado com sucesso.');
    }

    public function edit(Aluno $aluno): Response
    {
        $aluno->load('turma', 'esportes');

        $turmas = Turma::where('active', true)
            ->orderBy('period')
            ->orderBy('name')
            ->get(['id', 'name', 'period']);

        $esportes = Esporte::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/alunos/Edit', [
            'aluno' => $aluno,
            'turmas' => $turmas,
            'esportes' => $esportes,
        ]);
    }

    public function update(Request $request, Aluno $aluno): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'turma_id' => 'required|exists:turmas,id',
            'period' => 'required|in:Matutino,Vespertino',
            'esportes' => 'nullable|array',
            'esportes.*' => 'exists:esportes,id',
        ]);

        // Validate period matches turma period
        $turma = Turma::findOrFail($validated['turma_id']);
        if ($turma->period !== $validated['period']) {
            return back()->withErrors([
                'period' => 'Período do aluno deve corresponder ao período da turma selecionada.'
            ])->withInput();
        }

        $aluno->update([
            'name' => $validated['name'],
            'turma_id' => $validated['turma_id'],
            'period' => $validated['period'],
        ]);

        $aluno->esportes()->sync($validated['esportes'] ?? []);

        return redirect()->route('admin.alunos.index')
            ->with('success', 'Aluno atualizado com sucesso.');
    }

    public function destroy(Aluno $aluno): RedirectResponse
    {
        // Deactivate, never delete — preserves referential integrity
        $aluno->update(['active' => false]);

        return redirect()->route('admin.alunos.index')
            ->with('success', 'Aluno desativado.');
    }

    public function restore(Aluno $aluno): RedirectResponse
    {
        $aluno->update(['active' => true]);
        return redirect()->route('admin.alunos.index')
            ->with('success', 'Aluno reativado com sucesso.');
    }
}
