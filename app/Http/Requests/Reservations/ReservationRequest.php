<?php

namespace App\Http\Requests\Reservations;

use Illuminate\Foundation\Http\FormRequest;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_id'       => 'required|uuid|exists:customers,id',
            'vehicle_id'        => 'required|uuid|exists:vehicles,id',
            'pickup_branch_id'  => 'required|uuid|exists:branches,id',
            'dropoff_branch_id' => 'required|uuid|exists:branches,id',
            'insurance_plan_id' => 'nullable|uuid|exists:insurance_plans,id',
            'pickup_date'       => 'required|date|after_or_equal:today',
            'dropoff_date'      => 'required|date|after:pickup_date',
            'extra_service_ids' => 'nullable|array',
            'extra_service_ids.*'=> 'uuid|exists:extra_services,id',
            'promo_code'        => 'nullable|string|max:50',
            'notes'             => 'nullable|string|max:1000',
        ];
    }
}
