<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitializePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'gateway' => ['required', 'in:paystack,flutterwave,stripe'],
            'subscription_id' => ['required', 'exists:subscriptions,id'],
            'amount' => ['sometimes', 'integer', 'min:100'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'callback_url' => ['sometimes', 'url'],
        ];
    }
}
