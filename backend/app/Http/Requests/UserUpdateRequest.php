<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;

class UserUpdateRequest extends FormRequest
{
    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => 'sometimes|required|string|min:2|max:255',
            'email' => "sometimes|required|string|email|max:255|unique:users,email,{$userId}",
            'password' => 'sometimes|nullable|string|min:8|max:255',
            'role_id' => 'sometimes|nullable|integer|exists:roles,id'
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.min' => 'The name must be at least :min characters.',
            'name.max' => 'The name must not exceed :max characters.',
            
            'email.required' => 'The email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already taken.',
            
            'password.min' => 'The password must be at least :min characters long.',
            
            'role_id.integer' => 'The role ID must be a valid integer.',
            'role_id.exists' => 'The selected role does not exist.',
        ];
    }
}