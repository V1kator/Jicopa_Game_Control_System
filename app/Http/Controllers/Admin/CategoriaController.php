<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Turma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Categoria::with('turmas', 'esportes');

        // Filter by active status: default 'true' (apenas ativas);
        // 'false' (apenas inativas); 'all' (todas).
        $activeFilter = $request->input('active', 'true');
        if ($activeFilter === 'false') {
            $query->where('active', false);
        } elseif ($activeFilter !== 'all') {
            $query->where('active', true);
            $activeFilter = 'true';
        }

        $categorias = $query->orderBy('name')->get();

        return Inertia::render('admin/categorias/Index', [
            'categorias' => $categorias,
            'filters' => ['active' => $activeFilter],
        ]);
    }

    public function create(): Response
    {
        $turmas = Turma::where('active', true)
            ->orderBy('period')
            ->orderBy('name')
            ->get(['id', 'name', 'period']);

        return Inertia::render('admin/categorias/Create', [
            'turmas' => $turmas,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'turmas' => 'nullable|array',
            'turmas.*' => 'exists:turmas,id',
        ]);

        // Checar duplicata de nome apenas entre ativas
        $existingActive = Categoria::where('name', $validated['name'])
            ->where('active', true)
            ->first();

        if ($existingActive) {
            return back()->withErrors([
                'name' => "Categoria '{$validated['name']}' já existe e está ativa."
            ])->withInput();
        }

        // Checar se existe inativa com mesmo nome → oferecer reativação
        $existingInactive = Categoria::where('name', $validated['name'])
            ->where('active', false)
            ->first();

        if ($existingInactive) {
            return back()
                ->withErrors(['name' => "Já existe uma categoria '{$validated['name']}' desativada."])
                ->with('reactivate_id', $existingInactive->id)
                ->withInput();
        }

        $categoria = Categoria::create([
            'name' => $validated['name'],
            'active' => true,
        ]);

        if (!empty($validated['turmas'])) {
            $categoria->turmas()->sync($validated['turmas']);
        }

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(Categoria $categoria): Response
    {
        $categoria->load('turmas', 'esportes');

        $turmas = Turma::where('active', true)
            ->orderBy('period')
            ->orderBy('name')
            ->get(['id', 'name', 'period']);

        return Inertia::render('admin/categorias/Edit', [
            'categoria' => $categoria,
            'turmas' => $turmas,
        ]);
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'turmas' => 'nullable|array',
            'turmas.*' => 'exists:turmas,id',
        ]);

        $existingActive = Categoria::where('name', $validated['name'])
            ->where('active', true)
            ->where('id', '!=', $categoria->id)
            ->first();

        if ($existingActive) {
            return back()->withErrors([
                'name' => "Categoria '{$validated['name']}' já existe e está ativa."
            ])->withInput();
        }

        $categoria->update([
            'name' => $validated['name'],
        ]);

        $categoria->turmas()->sync($validated['turmas'] ?? []);

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        // Deactivate, never delete — preserves referential integrity
        $categoria->update(['active' => false]);

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoria desativada.');
    }

    public function restore(Categoria $categoria): RedirectResponse
    {
        $categoria->update(['active' => true]);
        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoria reativada com sucesso.');
    }
}
