<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class OrderRequest extends FormRequest
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
                // Order Number should not created on client
                // 'order_number' => 'required|string|unique:orders,order_number',
                'customer_id' => 'required|exists:customers,id',
                'user_id' => 'required|exists:users,id',
                'order_status' => ['required', new Enum(OrderStatus::class)],
                'total_price'  => 'required|decimal:2|min:0',
                'notes'        => 'nullable|string',
                'pickup_date'  => 'nullable|date',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = [
                // Order Number should not be changed
                // 'order_number' => 'sometimes|string|unique:orders,order_number,' . $this->order?->id,
                'customer_id'  => 'sometimes|exists:customers,id',
                'user_id'      => 'sometimes|exists:users,id',
                'order_status' => ['sometimes', new Enum(OrderStatus::class)],
                'total_price'  => 'sometimes|decimal:2|min:0',
                'notes'        => 'sometimes|nullable|string',
                'pickup_date'  => 'sometimes|nullable|date',
            ];
        }

        return $rules;
    }
}
