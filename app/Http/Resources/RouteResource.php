<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'asal'       => $this->asal,
            'tujuan'     => $this->tujuan,
            'status'     => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
