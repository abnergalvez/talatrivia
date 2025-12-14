<?php

namespace App\Http\Requests;
use Anik\Form\FormRequest;

class UserStoreRequest extends FormRequest
{
    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255',
            'role_id' => 'nullable|integer|exists:roles,id'
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
            'email.unique' => 'This email address is already registered.',
            
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least :min characters long.',
            
            'role_id.exists' => 'The selected role does not exist.',
        ];
    }
}