<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

abstract class ApiController
{
    // per_page default 10, maksimal 50
    protected function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 10), 1), 50);
    }
}
