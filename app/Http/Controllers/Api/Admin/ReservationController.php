<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ReservationResource;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends ApiController
{
    public function __construct(private ReservationService $reservations)
    {
    }

    public function index(Request $request)
    {
        $filter = $request->validate([
            'status'         => ['nullable', 'in:pending,confirmed,cancelled'],
            'payment_status' => ['nullable', 'in:unpaid,paid,refunded'],
            'trip_id'        => ['nullable', 'integer'],
            'tanggal'        => ['nullable', 'date_format:Y-m-d'],
            'q'              => ['nullable', 'string', 'max:100'],
            'page'           => ['nullable', 'integer', 'min:1'],
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return ReservationResource::collection(
            $this->reservations->paginate($filter, null, $this->perPage($request))
        );
    }

    public function show(int $id)
    {
        return new ReservationResource($this->reservations->find($id));
    }

    public function cancel(int $id)
    {
        return new ReservationResource($this->reservations->cancel(null, $id, true));
    }
}
