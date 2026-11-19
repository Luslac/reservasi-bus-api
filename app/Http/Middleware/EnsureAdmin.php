<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->role !== 'admin') {
            throw new ApiException('Akses ditolak.', 403, 'FORBIDDEN');
        }

        return $next($request);
    }
}
