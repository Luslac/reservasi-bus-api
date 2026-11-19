<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Kapasitas dan kursi sengaja tidak bisa diubah
    public function rules(): array
    {
        return [
            'plat_nomor' => ['required', 'string', 'max:20', Rule::unique('buses', 'plat_nomor')->ignore($this->route('bus')?->id)],
            'tipe_bus'   => ['required', 'string', 'max:50'],
            'status'     => ['sometimes', 'in:Active,Inactive'],
        ];
    }
}
