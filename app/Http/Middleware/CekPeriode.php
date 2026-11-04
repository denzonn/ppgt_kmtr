<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CekPeriode
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $periode = $request->route('periode');

        // (int): driver bisa mengembalikan string
        abort_unless($user->isAdmin() || (int) $user->periode_id === (int) $periode->id, 403);

        return $next($request);
    }
}
