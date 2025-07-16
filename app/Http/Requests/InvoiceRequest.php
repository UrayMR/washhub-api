<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
                // Invoice Number should not created on client
                // 'invoice_number' => 'required|string|unique:invoices,invoice_number',
                'order_id' => 'required|exists:orders,id',
                'amount'  => 'required|decimal:2|min:0',
                'issued_at'  => 'nullable|date',
                'status' => 'required|in:unpaid,paid,cancelled',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = [
                // Invoice Number should not be changed
                // 'invoice_number' => 'sometimes|string|unique:invoices,invoice_number,' . $this->invoice?->id,
                'order_id' => 'sometimes|exists:orders,id',
                'amount'  => 'sometimes|decimal:2|min:0',
                'issued_at'  => 'sometimes|nullable|date',
                'status' => 'sometimes|in:unpaid,paid,cancelled',
            ];
        }

        return $rules;
    }
}
