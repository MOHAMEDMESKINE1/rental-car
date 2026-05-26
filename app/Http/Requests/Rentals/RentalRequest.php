<?php

namespace App\Http\Requests\Rentals;

use Illuminate\Foundation\Http\FormRequest;

class RentalRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id'        => 'required|uuid|exists:customers,id',
            'vehicle_id'         => 'required|uuid|exists:vehicles,id',
            'pickup_branch_id'   => 'required|uuid|exists:branches,id',
            'dropoff_branch_id'  => 'required|uuid|exists:branches,id',
            'insurance_plan_id'  => 'nullable|uuid|exists:insurance_plans,id',
            'planned_pickup_at'  => 'required|date',
            'planned_dropoff_at' => 'required|date|after:planned_pickup_at',
            'pickup_mileage'     => 'required|integer|min:0',
            'pickup_fuel_level'  => 'required|integer|min:0|max:100',
            'deposit_amount'     => 'nullable|numeric|min:0',
            'extra_service_ids'  => 'nullable|array',
            'extra_service_ids.*'=> 'uuid|exists:extra_services,id',
            'promo_code'         => 'nullable|string|max:50',
            'notes'              => 'nullable|string|max:1000',
        ];
    }
}
