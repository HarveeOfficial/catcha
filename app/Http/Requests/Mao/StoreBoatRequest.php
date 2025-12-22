<?php

namespace App\Http\Requests\Mao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBoatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMao() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'registration_number' => ['required', 'string', 'max:50', 'unique:boats,registration_number'],
            'name' => ['required', 'string', 'max:100'],
            'owner_name' => ['required', 'string', 'max:100'],
            'owner_contact' => ['nullable', 'string', 'max:50'],
            'boat_type' => ['required', Rule::in(['motorized', 'non-motorized'])],
            'length_m' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'width_m' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'gross_tonnage' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'engine_type' => ['nullable', 'string', 'max:50'],
            'engine_horsepower' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'home_port' => ['nullable', 'string', 'max:100'],
            'psgc_region' => ['nullable', 'string', 'max:100'],
            'psgc_municipality' => ['nullable', 'string', 'max:100'],
            'psgc_barangay' => ['nullable', 'string', 'max:100'],
            'registration_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:registration_date'],
            'status' => ['required', Rule::in(['active', 'expired', 'suspended', 'decommissioned'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'registration_number.unique' => 'This registration number is already registered.',
            'expiry_date.after_or_equal' => 'The expiry date must be after or equal to the registration date.',
        ];
    }
}
