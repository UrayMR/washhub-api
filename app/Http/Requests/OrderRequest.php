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
                'order_status' => ['nullable', new Enum(OrderStatus::class)],
                'notes'        => ['nullable', 'string'],
                'pickup_date'  => ['nullable', 'date'],

                // Customer as nested object
                'customer'               => ['required', 'array'],
                'customer.name'          => ['required', 'string', 'max:100'],
                'customer.phone_number'  => ['required', 'string', 'max:20'],
                'customer.address'       => ['nullable', 'string'],

                // Order items
                'items'                  => ['required', 'array', 'min:1'],
                'items.*.service_id'     => ['required', 'exists:services,id'],
                'items.*.name'           => ['required', 'string'],
                'items.*.quantity'       => ['required', 'numeric', 'min:0.1'],
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = [
                'order_status' => ['sometimes', new Enum(OrderStatus::class)],
                'notes'        => ['sometimes', 'nullable', 'string'],
                'pickup_date'  => ['sometimes', 'nullable', 'date'],

                'customer'               => ['sometimes', 'array'],
                'customer.name'          => ['sometimes', 'required', 'string', 'max:100'],
                'customer.phone_number'  => ['sometimes', 'required', 'string', 'max:20'],
                'customer.address'       => ['sometimes', 'nullable', 'string'],

                'items'                  => ['sometimes', 'array', 'min:1'],
                'items.*.id'             => ['sometimes', 'integer', 'exists:order_items,id'],
                'items.*.service_id'     => ['required_with:items', 'exists:services,id'],
                'items.*.name'           => ['required_with:items', 'string'],
                'items.*.quantity'       => ['required_with:items', 'numeric', 'min:0.1'],
            ];
        }

        return $rules;
    }
}
