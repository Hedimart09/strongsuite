<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('membership_plans')->ignore($this->membership_plan)],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'duration_in_days' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Plan name is required',
            'name.unique' => 'A plan with this name already exists',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price must be at least 0',
            'currency.required' => 'Currency is required',
            'currency.size' => 'Currency must be a 3-letter code (e.g., GHS, USD)',
            'duration_in_days.required' => 'Duration is required',
            'duration_in_days.integer' => 'Duration must be a whole number',
            'duration_in_days.min' => 'Duration must be at least 1 day',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('price')) {
            $this->merge([
                'price' => (int) ($this->price * 100),
            ]);
        }
    }
}
