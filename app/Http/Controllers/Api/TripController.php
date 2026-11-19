<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SearchTripRequest;
use App\Http\Resources\TripResource;
use App\Services\TripService;
use Illuminate\Http\Request;

class TripController extends ApiController
{
    public function __construct(private TripService $trips)
    {
    }

    public function index(SearchTripRequest $request)
    {
        return TripResource::collection(
            $this->trips->search($request->validated(), $this->perPage($request))
        );
    }

    public function show(Request $request, int $id)
    {
        ['trip' => $trip, 'seats' => $seats] = $this->trips->detail($id);

        return response()->json([
            'data' => (new TripResource($trip))->resolve($request) + ['seats' => $seats],
        ]);
    }
}
