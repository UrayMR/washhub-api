<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderItemRequest extends FormRequest
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
                'order_id' => 'required|exists:orders,id',
                'service_id' => 'required|exists:services,id',
                'name' => 'required|string|max:255',
                'quantity' => 'required|numeric|min:0',
                'price' => 'required|decimal:2|min:0',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = [
                // Does order id need to be on this put or patch?
                'order_id' => 'sometimes|exists:orders,id',
                'service_id' => 'sometimes|exists:services,id',
                'name' => 'sometimes|string|max:255',
                'quantity' => 'sometimes|numeric|min:0',
                'price' => 'sometimes|decimal:2|min:0',
            ];
        }

        return $rules;
    }
}
