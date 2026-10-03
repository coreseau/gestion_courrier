<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMotDePasseChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->doit_changer_mot_de_passe && ! $request->routeIs('mot-de-passe.*')) {
            return redirect()->route('mot-de-passe.edit');
        }

        return $next($request);
    }
}