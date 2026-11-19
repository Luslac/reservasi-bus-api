<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Admin\StoreBusRequest;
use App\Http\Requests\Admin\UpdateBusRequest;
use App\Http\Resources\BusResource;
use App\Models\Bus;
use App\Services\BusService;
use Illuminate\Http\Request;

class BusController extends ApiController
{
    public function __construct(private BusService $buses)
    {
    }

    public function index(Request $request)
    {
        return BusResource::collection(Bus::orderByDesc('id')->paginate($this->perPage($request)));
    }

    public function store(StoreBusRequest $request)
    {
        $bus = $this->buses->create($request->validated());

        return (new BusResource($bus))->response()->setStatusCode(201);
    }

    public function show(Bus $bus)
    {
        return new BusResource($bus->load('seats'));
    }

    public function update(UpdateBusRequest $request, Bus $bus)
    {
        $bus->update($request->validated());

        return new BusResource($bus->refresh());
    }

    public function destroy(Bus $bus)
    {
        $this->buses->delete($bus);

        return response()->noContent();
    }
}
