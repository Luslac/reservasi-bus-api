<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Services\DashboardService;

class DashboardController extends ApiController
{
    public function index(DashboardService $dashboard)
    {
        return response()->json(['data' => $dashboard->summary()]);
    }
}
