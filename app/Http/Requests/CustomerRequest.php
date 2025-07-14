<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
                'name' => 'required|string|max:255',
                'phone_number' => 'required|string|unique:customers,phone_number',
                'address' => 'nullable|string|min:6',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = [
                'name' => 'sometimes|string|max:255',
                'phone_number' => 'sometimes|string|unique:customers,phone_number',
                'address' => 'sometimes|nullable|string|min:6',
            ];
        }

        return $rules;
    }
}
