<?php

namespace App\Http\Requests\Vehicles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'category_id'         => 'required|uuid|exists:vehicle_categories,id',
            'branch_id'           => 'required|uuid|exists:branches,id',
            'make'                => 'required|string|max:100',
            'model'               => 'required|string|max:100',
            'year'                => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'color'               => 'required|string|max:50',
            'plate_number'        => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->ignore($vehicleId)],
            'vin'                 => ['nullable', 'string', 'max:17', Rule::unique('vehicles', 'vin')->ignore($vehicleId)],
            'transmission'        => 'required|in:automatic,manual',
            'fuel_type'           => 'required|in:gasoline,diesel,electric,hybrid',
            'seat_count'          => 'required|integer|min:2|max:12',
            'mileage'             => 'required|integer|min:0',
            'fuel_level'          => 'required|integer|min:0|max:100',
            'next_service_date'   => 'nullable|date',
            'insurance_expiry'    => 'nullable|date',
            'registration_expiry' => 'nullable|date',
            'notes'               => 'nullable|string|max:1000',
            'is_active'           => 'boolean',
            'thumbnail'           => 'nullable|image|max:4096',
            'photos'              => 'nullable|array|max:10',
            'photos.*'            => 'image|max:4096',
        ];
    }
}
