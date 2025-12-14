<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;

class RoleUpdateRequest extends FormRequest
{
    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        $roleId = $this->route('id');

        return [
            'name' => "sometimes|required|string|min:2|max:50|unique:roles,name,{$roleId}|alpha_dash",
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'The role name is required.',
            'name.min' => 'The role name must be at least :min characters.',
            'name.max' => 'The role name must not exceed :max characters.',
            'name.unique' => 'This role name already exists.',
            'name.alpha_dash' => 'The role name may only contain letters, numbers, dashes and underscores.',
        ];
    }
}