<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Services\ReservationService;
use Illuminate\Http\Request;

// Reservasi milik user yang sedang login
class ReservationController extends ApiController
{
    public function __construct(private ReservationService $reservations)
    {
    }

    public function index(Request $request)
    {
        $filter = $request->validate([
            'status'   => ['nullable', 'in:pending,confirmed,cancelled'],
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return ReservationResource::collection(
            $this->reservations->paginate($filter, $request->user()->id, $this->perPage($request))
        );
    }

    public function store(StoreReservationRequest $request)
    {
        $data = $request->validated();
        $reservation = $this->reservations->book($request->user(), $data['trip_id'], $data['passengers']);

        return (new ReservationResource($reservation))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id)
    {
        return new ReservationResource($this->reservations->find($id, $request->user()->id));
    }

    public function pay(Request $request, int $id)
    {
        return new ReservationResource($this->reservations->pay($request->user(), $id));
    }

    public function cancel(Request $request, int $id)
    {
        return new ReservationResource($this->reservations->cancel($request->user(), $id));
    }
}
