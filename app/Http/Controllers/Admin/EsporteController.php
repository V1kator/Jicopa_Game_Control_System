<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Esporte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EsporteController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Esporte::with('categorias');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('active', $request->active === 'true');
        }

        $esportes = $query->orderBy('name')->get();

        return Inertia::render('admin/esportes/Index', [
            'esportes' => $esportes,
            'filters' => $request->only(['type', 'active']),
        ]);
    }

    public function create(): Response
    {
        $categorias = Categoria::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/esportes/Create', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:coletivo,individual',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ]);

        $existingActive = Esporte::where('name', $validated['name'])
            ->where('active', true)
            ->first();

        if ($existingActive) {
            return back()->withErrors([
                'name' => "Esporte '{$validated['name']}' já existe e está ativo."
            ])->withInput();
        }

        $existingInactive = Esporte::where('name', $validated['name'])
            ->where('active', false)
            ->first();

        if ($existingInactive) {
            return back()
                ->withErrors(['name' => "Já existe um esporte '{$validated['name']}' desativado."])
                ->with('reactivate_id', $existingInactive->id)
                ->withInput();
        }

        $esporte = Esporte::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'active' => true,
        ]);

        if (!empty($validated['categorias'])) {
            $esporte->categorias()->sync($validated['categorias']);
        }

        return redirect()->route('admin.esportes.index')
            ->with('success', 'Esporte criado com sucesso.');
    }

    public function edit(Esporte $esporte): Response
    {
        $esporte->load('categorias');

        $categorias = Categoria::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/esportes/Edit', [
            'esporte' => $esporte,
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Esporte $esporte): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:coletivo,individual',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ]);

        $existingActive = Esporte::where('name', $validated['name'])
            ->where('active', true)
            ->where('id', '!=', $esporte->id)
            ->first();

        if ($existingActive) {
            return back()->withErrors([
                'name' => "Esporte '{$validated['name']}' já existe e está ativo."
            ])->withInput();
        }

        $esporte->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
        ]);

        $esporte->categorias()->sync($validated['categorias'] ?? []);

        return redirect()->route('admin.esportes.index')
            ->with('success', 'Esporte atualizado com sucesso.');
    }

    public function destroy(Esporte $esporte): RedirectResponse
    {
        // Deactivate, never delete — preserves referential integrity
        $esporte->update(['active' => false]);

        return redirect()->route('admin.esportes.index')
            ->with('success', 'Esporte desativado.');
    }

    public function restore(Esporte $esporte): RedirectResponse
    {
        $esporte->update(['active' => true]);
        return redirect()->route('admin.esportes.index')
            ->with('success', 'Esporte reativado com sucesso.');
    }
}
