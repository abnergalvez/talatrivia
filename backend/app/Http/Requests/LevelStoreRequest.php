<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;

class LevelStoreRequest extends FormRequest
{
    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:100|unique:levels,name',
            'points' => 'required|integer|min:1|max:100'
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'The level name is required.',
            'name.min' => 'The level name must be at least :min characters.',
            'name.max' => 'The level name must not exceed :max characters.',
            'name.unique' => 'This level name already exists.',
            
            'points.required' => 'The points field is required.',
            'points.integer' => 'The points must be a valid integer.',
            'points.min' => 'The points must be at least :min.',
            'points.max' => 'The points must not exceed :max.',
        ];
    }
}