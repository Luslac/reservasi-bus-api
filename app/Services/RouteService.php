<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\BusRoute;

class RouteService
{
    public function delete(BusRoute $route): void
    {
        if ($route->trips()->exists()) {
            throw new ApiException('Rute masih dipakai oleh trip.', 409, 'HAS_DEPENDENCIES');
        }
        $route->delete();
    }
}
