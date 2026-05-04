<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChecarRF
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$apenasBI): Response
    {

        // Se for rota de API, NÃO redireciona
        if ($request->is('api/*')) {
            return $next($request);
        }

        // $rf = session('IDUsuario');
        $rf = $_SESSION['IDUsuario'];

        // Se não estiver logado, pode ver apenas tutorial
        if (!$rf) {
            return redirect('/tutorial');
        }

        $apenasBI = [
            'd837864',
            'd947587',
            'd927090',
            'd800650',
            'd930678',
            'd950688'
        ];

        if (!empty($apenasBI) && in_array($rf, $apenasBI)) {
            // evita loop
            if ($request->routeIs('businessintelligence') || $request->routeIs('businessintelligence.*')) {
                return $next($request);
            }
            return redirect()->route('businessintelligence'); // nome, sem barra
        }
        return $next($request);
    }
}
