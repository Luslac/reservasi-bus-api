<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bus_id'            => ['required', 'integer', Rule::exists('buses', 'id')->where('status', 'Active')],
            'route_id'          => ['required', 'integer', Rule::exists('routes', 'id')->where('status', 'Active')],
            'tanggal_berangkat' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'jam_berangkat'     => ['required', 'date_format:H:i'],
            // maks 9.999.999 agar total 10 kursi tetap muat di DECIMAL(10,2)
            'harga'             => ['required', 'numeric', 'gt:0', 'max:9999999'],
        ];
    }
}
