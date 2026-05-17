<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jogo;
use App\Models\Categoria;
use App\Models\Esporte;
use App\Models\Turma;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

class JogoController extends Controller
{
    public function index(Request $request)
    {
        // Redirect /admin/jogos to /calendario (consolidated view)
        if ($request->routeIs('admin.jogos.index')) {
            return redirect()->route('calendario.index', $request->query());
        }

        $query = Jogo::with(['categoria', 'esporte', 'time1', 'time2', 'resultadosIndividuais'])
            ->orderBy('data', 'asc')
            ->orderBy('hora', 'asc');

        // Apply filters
        if ($request->filled('data')) {
            $query->whereDate('data', $request->data);
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('esporte_id')) {
            $query->where('esporte_id', $request->esporte_id);
        }
        if ($request->filled('cancelado')) {
            $query->where('cancelado', $request->cancelado === 'true');
        }

        $jogos = $query->get()->map(function ($jogo) {
            // Add computed field to indicate if game has results
            $jogo->has_resultado = ($jogo->placar_time1 !== null && $jogo->placar_time2 !== null) 
                || $jogo->resultadosIndividuais->count() > 0;
            return $jogo;
        });

        return Inertia::render('shared/Calendario', [
            'jogos' => $jogos,
            'categorias' => Categoria::where('active', true)->get(),
            'esportes' => Esporte::where('active', true)->get(),
            'filters' => $request->only(['data', 'categoria_id', 'esporte_id', 'cancelado']),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/jogos/Create', [
            'categorias' => Categoria::where('active', true)->get(),
            'esportes' => Esporte::where('active', true)->get(),
            'turmas' => Turma::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'esporte_id' => 'required|exists:esportes,id',
            'time1_id' => 'nullable|exists:turmas,id',
            'time2_id' => 'nullable|exists:turmas,id|different:time1_id',
            'data' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'local' => 'required|string|max:255',
        ]);

        // Validate date+time is not in the past
        if (!$this->validateDateTimeNotPast($validated['data'], $validated['hora'])) {
            return back()->withErrors([
                'data' => 'A data e hora do jogo não podem estar no passado.',
            ])->withInput();
        }

        // Check for conflicts (per D-02: validate but allow override)
        $conflict = $this->checkConflict(
            $validated['data'],
            $validated['hora'],
            $validated['local']
        );

        if ($conflict && !$request->boolean('force_create')) {
            // Suggest alternative times (per D-03)
            $alternatives = $this->suggestAlternativeTimes(
                $validated['data'],
                $validated['hora'],
                $validated['local']
            );

            return back()->withErrors([
                'conflict' => 'Conflito detectado: outro jogo no mesmo horário e local.',
            ])->with('alternatives', $alternatives);
        }

        Jogo::create($validated);

        return redirect()->route('calendario.index')
            ->with('success', 'Jogo cadastrado com sucesso.');
    }

    public function edit(Jogo $jogo)
    {
        $jogo->load(['categoria', 'esporte', 'time1', 'time2']);

        return Inertia::render('admin/jogos/Edit', [
            'jogo' => $jogo,
            'categorias' => Categoria::where('active', true)->get(),
            'esportes' => Esporte::where('active', true)->get(),
            'turmas' => Turma::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Jogo $jogo)
    {
        $validated = $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'esporte_id' => 'required|exists:esportes,id',
            'time1_id' => 'nullable|exists:turmas,id',
            'time2_id' => 'nullable|exists:turmas,id|different:time1_id',
            'data' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'local' => 'required|string|max:255',
            'cancelado' => 'boolean',
        ]);

        // Validate date+time is not in the past (only if not already played)
        if (!$this->validateDateTimeNotPast($validated['data'], $validated['hora'])) {
            return back()->withErrors([
                'data' => 'A data e hora do jogo não podem estar no passado.',
            ])->withInput();
        }

        // Check for conflicts (excluding current game)
        $conflict = $this->checkConflict(
            $validated['data'],
            $validated['hora'],
            $validated['local'],
            $jogo->id
        );

        if ($conflict && !$request->boolean('force_update')) {
            $alternatives = $this->suggestAlternativeTimes(
                $validated['data'],
                $validated['hora'],
                $validated['local'],
                $jogo->id
            );

            return back()->withErrors([
                'conflict' => 'Conflito detectado: outro jogo no mesmo horário e local.',
            ])->with('alternatives', $alternatives);
        }

        $jogo->update($validated);

        return redirect()->route('calendario.index')
            ->with('success', 'Jogo atualizado com sucesso.');
    }

    public function destroy(Jogo $jogo)
    {
        // Hard delete with cascade (resultados, presencas, penalidades)
        $jogo->delete();

        return redirect()->route('calendario.index')
            ->with('success', 'Jogo excluído com sucesso.');
    }

    /**
     * Check if there's a scheduling conflict
     */
    private function checkConflict(string $data, string $hora, string $local, ?int $excludeId = null): bool
    {
        $hora_inicio = Carbon::parse($hora);
        $hora_fim = $hora_inicio->copy()->addHour(); // Assume 1 hour duration

        $query = Jogo::where('local', $local)
            ->where('data', $data)
            ->where('cancelado', false);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Get all games on the same date/location and check overlap in PHP
        // This approach is database-agnostic (works with SQLite and MySQL)
        $jogos = $query->get();

        foreach ($jogos as $jogo) {
            $jogo_inicio = Carbon::parse($jogo->hora);
            $jogo_fim = $jogo_inicio->copy()->addHour();

            // Check overlapping time ranges: (start1 < end2) AND (end1 > start2)
            if ($jogo_inicio->lt($hora_fim) && $jogo_fim->gt($hora_inicio)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Suggest alternative times when conflict detected (per D-03)
     */
    private function suggestAlternativeTimes(string $data, string $hora, string $local, ?int $excludeId = null): array
    {
        $alternatives = [];
        $currentTime = Carbon::parse($hora);

        // Try next 3 time slots (1 hour increments)
        for ($i = 1; $i <= 6; $i++) {
            $nextTime = $currentTime->copy()->addHours($i);

            if (!$this->checkConflict($data, $nextTime->format('H:i'), $local, $excludeId)) {
                $alternatives[] = $nextTime->format('H:i');
            }

            if (count($alternatives) >= 3) {
                break;
            }
        }

        return $alternatives;
    }

    /**
     * Validate that date+time combination is not in the past
     */
    private function validateDateTimeNotPast(string $data, string $hora): bool
    {
        $dateTime = Carbon::parse("$data $hora", 'America/Sao_Paulo');
        $now = Carbon::now('America/Sao_Paulo');
        return $dateTime->greaterThanOrEqualTo($now);
    }
}
