<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asal'         => ['required', 'string', 'max:100'],
            'tujuan'       => ['required', 'string', 'max:100'],
            'tanggal'      => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'tipe_bus'     => ['nullable', 'string', 'max:50'],
            'jumlah_kursi' => ['nullable', 'integer', 'min:1', 'max:10'],
            'page'         => ['nullable', 'integer', 'min:1'],
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
