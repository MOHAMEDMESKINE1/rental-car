<?php

namespace App\Http\Requests\ReturnVehicles;

use Illuminate\Foundation\Http\FormRequest;

class ReturnVehicleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'actual_dropoff_at'  => 'required|date',
            'dropoff_mileage'    => 'required|integer|min:0',
            'dropoff_fuel_level' => 'required|integer|min:0|max:100',
            'notes'              => 'nullable|string|max:1000',
        ];
    }
}
