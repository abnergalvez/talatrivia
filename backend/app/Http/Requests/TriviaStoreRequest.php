<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;

class TriviaStoreRequest extends FormRequest
{
    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'questions_order' => 'nullable|in:random,sequential',
            'time_option' => 'nullable|in:by_trivia,by_question',
            'time' => 'nullable|integer|min:1|max:3600'
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'The trivia name is required.',
            'name.min' => 'The trivia name must be at least :min characters.',
            'name.max' => 'The trivia name must not exceed :max characters.',
            
            'description.max' => 'The description must not exceed :max characters.',
            
            'is_active.boolean' => 'The is_active field must be true or false.',
            
            'questions_order.in' => 'The questions order must be either random or sequential.',
            
            'time_option.in' => 'The time option must be either by_trivia or by_question.',
            
            'time.integer' => 'The time must be a valid integer.',
            'time.min' => 'The time must be at least :min second.',
            'time.max' => 'The time must not exceed :max seconds (1 hour).',
        ];
    }
}