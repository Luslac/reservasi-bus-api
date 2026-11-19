<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\StoreTripRequest;
use App\Http\Requests\Admin\UpdateTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Http\Request;

class TripController extends ApiController
{
    public function __construct(private TripService $trips)
    {
    }

    public function index(Request $request)
    {
        $filter = $request->validate([
            'route_id'       => ['nullable', 'integer'],
            'bus_id'         => ['nullable', 'integer'],
            'tanggal_dari'   => ['nullable', 'date_format:Y-m-d'],
            'tanggal_sampai' => ['nullable', 'date_format:Y-m-d'],
            'page'           => ['nullable', 'integer', 'min:1'],
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return TripResource::collection($this->trips->adminList($filter, $this->perPage($request)));
    }

    public function store(StoreTripRequest $request)
    {
        return (new TripResource($this->trips->create($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new TripResource($this->trips->find($id));
    }

    public function update(UpdateTripRequest $request, Trip $trip)
    {
        return new TripResource($this->trips->update($trip, $request->validated()));
    }

    public function destroy(Trip $trip)
    {
        $this->trips->delete($trip);

        return response()->noContent();
    }
}
