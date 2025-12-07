<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'exists:members,id'],
            'membership_plan_id' => ['required', 'exists:membership_plans,id'],
            'start_date' => ['required', 'date'],
            'auto_renew' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Member is required',
            'member_id.exists' => 'Selected member does not exist',
            'membership_plan_id.required' => 'Membership plan is required',
            'membership_plan_id.exists' => 'Selected membership plan does not exist',
            'start_date.required' => 'Start date is required',
            'start_date.date' => 'Start date must be a valid date',
        ];
    }
}
