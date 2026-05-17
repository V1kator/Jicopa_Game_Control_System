<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Jogo;
use Carbon\Carbon;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $nextGame = null;

        // Load next game for authenticated users (both admin and professor)
        if ($request->user()) {
            $nextGame = Jogo::with(['categoria', 'esporte', 'time1', 'time2'])
                ->where('data', '>=', Carbon::today())
                ->where('cancelado', false)
                ->whereNotNull('categoria_id')
                ->whereNotNull('esporte_id')
                ->orderBy('data', 'asc')
                ->orderBy('hora', 'asc')
                ->first();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id'       => $request->user()->id,
                    'name'     => $request->user()->name,
                    'email'    => $request->user()->email,
                    'active'   => $request->user()->active,
                    'roles'    => $request->user()->getRoleNames(), // Collection — React receives as array
                    'is_admin' => $request->user()->hasRole('admin'), // Explicit admin flag for frontend conditionals
                ] : null,
            ],
            'nextGame' => $nextGame,
        ];
    }
}
