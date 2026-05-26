<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;
        $userId     = $this->route('customer')?->user_id;
        $isUpdate   = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            // User fields
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password'        => [$isUpdate ? 'nullable' : 'nullable', 'string', 'min:8'],

            // Customer fields
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:255',
            'city'            => 'nullable|string|max:100',
            'country'         => 'nullable|string|max:100',
            'nationality'     => 'nullable|string|max:100',
            'license_number'  => ['nullable', 'string', 'max:50', Rule::unique('customers', 'license_number')->ignore($customerId)],
            'license_country' => 'nullable|string|max:100',
            'license_expiry'  => 'nullable|date',
            'passport_number' => 'nullable|string|max:50',
            'id_card_number'  => 'nullable|string|max:50',
            'date_of_birth'   => 'nullable|date|before:today',
            'gender'          => 'nullable|in:male,female,other',

            // Documents
            'license_front'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'license_back'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'passport'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'id_card'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];
    }
}
