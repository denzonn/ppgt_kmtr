<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CekMenu
{
    public function handle(Request $request, Closure $next, string $menu)
    {
        abort_unless($request->user()->bisaMenu($menu), 403);

        return $next($request);
    }
}
