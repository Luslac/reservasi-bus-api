<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses admin dijaga middleware `admin`
    }

    public function rules(): array
    {
        return [
            'plat_nomor'   => ['required', 'string', 'max:20', 'unique:buses,plat_nomor'],
            'tipe_bus'     => ['required', 'string', 'max:50'],
            'jumlah_baris' => ['required', 'integer', 'min:1', 'max:20'], // kapasitas = baris x 4
            'status'       => ['sometimes', 'in:Active,Inactive'],
        ];
    }
}
