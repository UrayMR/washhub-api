<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
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
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required|string|min:6',
                'role' => 'required|in:admin,super-admin',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $userId = $this->route('user')?->id ?? 'NULL';

            $rules = [
                'name' => 'sometimes|string|max:255',
                'email' => "sometimes|email|unique:users,email,{$userId}",
                'password' => 'sometimes|string|min:6|confirmed',
                'password_confirmation' => 'sometimes|string|min:6',
                'role' => 'sometimes|in:admin,super-admin',
            ];
        }

        return $rules;
    }

    /**
     * Custom failed validation response
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
