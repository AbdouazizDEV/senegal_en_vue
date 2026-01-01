<?php

namespace App\Http\Middleware;

use App\Domain\User\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        if (!$user) {
            throw new UnauthorizedHttpException('', 'Non authentifié');
        }

        // Vérifier si l'utilisateur a l'un des rôles requis
        $hasRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            throw new UnauthorizedHttpException('', 'Permissions insuffisantes');
        }

        return $next($request);
    }
}
