<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;

class AnswerAllQuestionsRequest extends FormRequest
{
    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        return [
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.option_id' => 'required|exists:options,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'answers.required' => 'You must provide at least one answer.',
            'answers.array' => 'Answers must be an array.',
            'answers.min' => 'You must answer at least one question.',
            
            'answers.*.question_id.required' => 'Each answer must have a question_id.',
            'answers.*.question_id.exists' => 'One or more questions do not exist.',
            
            'answers.*.option_id.required' => 'Each answer must have an option_id.',
            'answers.*.option_id.exists' => 'One or more options do not exist.',
        ];
    }
}