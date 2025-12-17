<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordManualPaymentRequest extends FormRequest
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
            'member_id' => ['required', 'exists:members,id'],
            'subscription_id' => ['sometimes', 'exists:subscriptions,id'],
            'invoice_id' => ['sometimes', 'exists:invoices,id'],
            'amount' => ['required', 'integer', 'min:100'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'payment_method' => ['required', 'in:cash,mobile_money,bank_transfer'],
            'transaction_id' => ['sometimes', 'string'],
            'notes' => ['sometimes', 'string', 'max:500'],
        ];
    }
}
