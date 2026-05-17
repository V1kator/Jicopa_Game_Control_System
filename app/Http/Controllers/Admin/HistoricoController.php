<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class HistoricoController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->whereIn('subject_type', [
                'App\\Models\\Turma',
                'App\\Models\\Categoria',
                'App\\Models\\Jogo',
                'App\\Models\\Esporte',
                'App\\Models\\Aluno',
                'App\\Models\\Penalidade',
            ])
            ->orderBy('created_at', 'desc');

        // Filter by model type
        if ($request->filled('tipo')) {
            $modelMap = [
                'turmas' => 'App\\Models\\Turma',
                'categorias' => 'App\\Models\\Categoria',
                'jogos' => 'App\\Models\\Jogo',
                'esportes' => 'App\\Models\\Esporte',
                'alunos' => 'App\\Models\\Aluno',
                'penalidades' => 'App\\Models\\Penalidade',
            ];

            if (isset($modelMap[$request->tipo])) {
                $query->where('subject_type', $modelMap[$request->tipo]);
            }
        }

        // Filter by event type
        if ($request->filled('evento')) {
            $query->where('event', $request->evento);
        }

        // Filter by date range
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $atividades = $query->paginate(50)->through(function ($activity) {
            return [
                'id' => $activity->id,
                'log_name' => $activity->log_name,
                'description' => $activity->description,
                'event' => $activity->event,
                'subject_type' => class_basename($activity->subject_type),
                'subject_id' => $activity->subject_id,
                'causer' => $activity->causer ? [
                    'id' => $activity->causer->id,
                    'name' => $activity->causer->name,
                ] : null,
                'properties' => $activity->properties,
                'created_at' => $activity->created_at->format('d/m/Y H:i:s'),
            ];
        });

        return Inertia::render('admin/historico/Index', [
            'atividades' => $atividades,
            'filtros' => [
                'tipo' => $request->tipo,
                'evento' => $request->evento,
                'data_inicio' => $request->data_inicio,
                'data_fim' => $request->data_fim,
            ],
        ]);
    }
}
