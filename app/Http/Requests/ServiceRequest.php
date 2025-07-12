<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
                'description' => 'nullable|string',
                'price' => 'required|decimal:2',
                'unit' => 'required|in:kg,pcs',
                'status' => 'required|in:active,inactive',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string',
                'price' => 'sometimes|decimal:2',
                'unit' => 'sometimes|in:kg,pcs',
                'status' => 'sometimes|in:active,inactive',
            ];
        }

        return $rules;
    }
}
