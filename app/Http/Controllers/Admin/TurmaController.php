<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Turma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TurmaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Turma::with('categorias');

        // Filter by period
        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('active', $request->active === 'true');
        }

        $turmas = $query->orderBy('period')->orderBy('name')->get();

        return Inertia::render('admin/turmas/Index', [
            'turmas' => $turmas,
            'filters' => $request->only(['period', 'active']),
        ]);
    }

    public function create(): Response
    {
        $categorias = Categoria::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/turmas/Create', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'period' => 'required|in:Matutino,Vespertino',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ]);

        // Checar duplicata apenas entre ativas
        $existingActive = Turma::where('name', $validated['name'])
            ->where('period', $validated['period'])
            ->where('active', true)
            ->first();

        if ($existingActive) {
            return back()->withErrors([
                'name' => "Turma '{$validated['name']}' no período {$validated['period']} já existe e está ativa."
            ])->withInput();
        }

        // Checar se existe inativa com mesmo nome+período → oferecer reativação
        $existingInactive = Turma::where('name', $validated['name'])
            ->where('period', $validated['period'])
            ->where('active', false)
            ->first();

        if ($existingInactive) {
            return back()
                ->withErrors(['name' => "Já existe uma turma '{$validated['name']}' ({$validated['period']}) desativada."])
                ->with('reactivate_id', $existingInactive->id)
                ->withInput();
        }

        $turma = Turma::create([
            'name' => $validated['name'],
            'period' => $validated['period'],
            'active' => true,
        ]);

        if (!empty($validated['categorias'])) {
            $turma->categorias()->sync($validated['categorias']);
        }

        return redirect()->route('admin.turmas.index')
            ->with('success', 'Turma criada com sucesso.');
    }

    public function edit(Turma $turma): Response
    {
        $turma->load('categorias');

        $categorias = Categoria::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/turmas/Edit', [
            'turma' => $turma,
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Turma $turma): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'period' => 'required|in:Matutino,Vespertino',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ]);

        // Manual unique constraint validation (name + period), excluding current turma and ignoring inativos
        $existingActive = Turma::where('name', $validated['name'])
            ->where('period', $validated['period'])
            ->where('active', true)
            ->where('id', '!=', $turma->id)
            ->first();

        if ($existingActive) {
            return back()->withErrors([
                'name' => "Turma '{$validated['name']}' no período {$validated['period']} já existe e está ativa."
            ])->withInput();
        }

        $turma->update([
            'name' => $validated['name'],
            'period' => $validated['period'],
        ]);

        $turma->categorias()->sync($validated['categorias'] ?? []);

        return redirect()->route('admin.turmas.index')
            ->with('success', 'Turma atualizada com sucesso.');
    }

    public function destroy(Turma $turma): RedirectResponse
    {
        // Deactivate, never delete — preserves referential integrity
        $turma->update(['active' => false]);

        return redirect()->route('admin.turmas.index')
            ->with('success', 'Turma desativada.');
    }

    public function restore(Turma $turma): RedirectResponse
    {
        $turma->update(['active' => true]);
        return redirect()->route('admin.turmas.index')
            ->with('success', 'Turma reativada com sucesso.');
    }
}
