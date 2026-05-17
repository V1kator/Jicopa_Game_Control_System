<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictProfessorAccess
{
    /**
     * Handle an incoming request.
     *
     * Professors are restricted from:
     * 1. Managing users (cadastro de professores)
     * 2. Scoring configuration (config pontuação)
     * 3. Evaluation pages (página avaliação)
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only restrict professors, admins have full access
        if ($user && $user->hasRole('professor')) {
            $restrictedRoutes = [
                'admin.users.*',
                'admin.scoring-config.*',
                'admin.avaliacao-config.*',
                'admin.avaliacao-notas.*',
            ];

            foreach ($restrictedRoutes as $pattern) {
                if ($request->routeIs($pattern)) {
                    abort(403, 'Acesso negado. Esta funcionalidade é restrita a administradores.');
                }
            }
        }

        return $next($request);
    }
}
