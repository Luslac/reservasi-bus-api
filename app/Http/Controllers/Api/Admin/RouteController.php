<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\StoreRouteRequest;
use App\Http\Requests\Admin\UpdateRouteRequest;
use App\Http\Resources\RouteResource;
use App\Models\BusRoute;
use App\Services\RouteService;
use Illuminate\Http\Request;

class RouteController extends ApiController
{
    public function __construct(private RouteService $routes)
    {
    }

    public function index(Request $request)
    {
        return RouteResource::collection(BusRoute::orderByDesc('id')->paginate($this->perPage($request)));
    }

    public function store(StoreRouteRequest $request)
    {
        $route = BusRoute::create($request->validated());

        return (new RouteResource($route->refresh()))->response()->setStatusCode(201);
    }

    public function show(BusRoute $busRoute)
    {
        return new RouteResource($busRoute);
    }

    public function update(UpdateRouteRequest $request, BusRoute $busRoute)
    {
        $busRoute->update($request->validated());

        return new RouteResource($busRoute->refresh());
    }

    public function destroy(BusRoute $busRoute)
    {
        $this->routes->delete($busRoute);

        return response()->noContent();
    }
}
