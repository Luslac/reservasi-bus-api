<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trip_id'                     => ['required', 'integer', 'exists:trips,id'],
            'passengers'                  => ['required', 'array', 'min:1', 'max:' . config('booking.max_seats')],
            'passengers.*.seat_id'        => ['required', 'integer', 'distinct', 'exists:seats,id'],
            'passengers.*.nama_penumpang' => ['required', 'string', 'max:100'],
        ];
    }
}
