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
        // $rf = session('IDUsuario');
        $rf = $_SESSION['IDUsuario'];

        // Se não estiver logado, pode ver apenas tutorial
        if (!$rf) {
            return redirect('/tutorial');
        }

        $apenasBI = [
            'd837864',
            'd947587',
            'd927090'
        ];

        // Se puder acessar apenas BI, direciona
        // if (!empty($apenasBI) && in_array($rf, $apenasBI)) {
        //     if (!$request->routeIs('businessintelligence')) {
        //         return redirect()->route('businessintelligence');
        //     }
        // }

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
