<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;

class QuestionUpdateRequest extends FormRequest
{
    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        return [
            'level_id' => 'sometimes|required|exists:levels,id',
            'description' => 'sometimes|required|string|min:10|max:1000',
            'time' => 'sometimes|nullable|integer|min:1|max:3600',
            'options' => 'sometimes|array|min:2|max:6',
            'options.*.text' => 'required|string|min:1|max:500',
            'options.*.is_correct' => 'required|boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'level_id.required' => 'The level is required.',
            'level_id.exists' => 'The selected level does not exist.',
            
            'description.required' => 'The question description is required.',
            'description.min' => 'The question description must be at least :min characters.',
            'description.max' => 'The question description must not exceed :max characters.',
            
            'time.integer' => 'The time must be a valid integer.',
            'time.min' => 'The time must be at least :min second.',
            'time.max' => 'The time must not exceed :max seconds (1 hour).',
            
            'options.array' => 'Options must be an array.',
            'options.min' => 'At least :min options are required.',
            'options.max' => 'A maximum of :max options are allowed.',
            
            'options.*.text.required' => 'Each option must have text.',
            'options.*.text.min' => 'Each option text must be at least :min character.',
            'options.*.text.max' => 'Each option text must not exceed :max characters.',
            
            'options.*.is_correct.required' => 'Each option must specify if it is correct.',
            'options.*.is_correct.boolean' => 'The is_correct field must be true or false.',
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            
            if (isset($data['options']) && is_array($data['options'])) {
                $correctCount = collect($data['options'])
                    ->filter(fn($option) => ($option['is_correct'] ?? false) === true)
                    ->count();
                
                if ($correctCount === 0) {
                    $validator->errors()->add('options', 'At least one option must be marked as correct.');
                }
                
                if ($correctCount > 1) {
                    $validator->errors()->add('options', 'Only one option can be marked as correct.');
                }
            }
        });
    }
}