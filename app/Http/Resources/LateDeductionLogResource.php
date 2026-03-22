<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LateDeductionLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'deduction_type'   => $this->deduction_type,
            'reference_date'   => $this->reference_date?->format('Y-m-d'),
            'deduction_amount' => $this->deduction_amount,
            'note'             => $this->note,
        ];
    }
}