<?php

namespace App\Http\Requests\Branches;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'name'         => 'required|string|max:255',
            'code'         => ['required', 'string', 'max:10', Rule::unique('branches', 'code')->ignore($branchId)],
            'address'      => 'required|string|max:255',
            'city'         => 'required|string|max:100',
            'country'      => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i|after:opening_time',
            'is_active'    => 'boolean',
            'photo'        => 'nullable|image|max:4096',
        ];
    }
}
