<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ignoreId = $this->route('busRoute')?->id; // null saat create

        return [
            'asal'   => ['required', 'string', 'max:100'],
            'tujuan' => [
                'required', 'string', 'max:100', 'different:asal',
                // pasangan asal + tujuan harus unik
                Rule::unique('routes', 'tujuan')
                    ->where(fn ($q) => $q->where('asal', $this->input('asal')))
                    ->ignore($ignoreId),
            ],
            'status' => ['sometimes', 'in:Active,Inactive'],
        ];
    }
}
