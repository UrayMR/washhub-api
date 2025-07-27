<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
        $rules = [];

        if ($this->isMethod('post')) {
            $rules = [
                // Transaction Number should not created on client
                // 'transaction_number' => 'required|string|unique:transactions,transaction_number',
                'invoice_id' => 'required|exists:invoices,id',
                'payment_method' => 'required|in:cash,transfer,qris',
                // Will be taken from invoice
                // 'paid_amount'  => 'required|decimal:2|min:0',
                'paid_at'  => 'nullable|date',
                'reference_number'  => 'nullable|string|max:255',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = [
                // Transaction Number should not be changed
                // 'transaction_number' => 'sometimes|string|unique:transactions,transaction_number,' . $this->transaction?->id,
                'invoice_id' => 'sometimes|exists:invoices,id',
                'payment_method' => 'sometimes|in:cash,transfer,qris',
                // Cannot be changed, will be taken from invoice
                // 'paid_amount'  => 'sometimes|decimal:2|min:0',
                'paid_at'  => 'sometimes|nullable|date',
                'reference_number'  => 'sometimes|nullable|string|max:255',
            ];
        }

        return $rules;
    }
}
