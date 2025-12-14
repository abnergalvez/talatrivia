<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;

class AnswerQuestionRequest extends FormRequest
{
    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        return [
            'option_id' => 'required|exists:options,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'option_id.required' => 'You must select an option.',
            'option_id.exists' => 'The selected option does not exist.',
        ];
    }
}