<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;

class TriviaAssignRequest extends FormRequest
{
    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        return [
            'user_ids' => 'required_without:emails|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'emails' => 'required_without:user_ids|array|min:1',
            'emails.*' => 'email|exists:users,email'
        ];
    }

    protected function messages(): array
    {
        return [
            'user_ids.required_without' => 'You must provide either user IDs or emails.',
            'user_ids.array' => 'User IDs must be provided as an array.',
            'user_ids.min' => 'You must provide at least one user ID.',
            'user_ids.*.integer' => 'Each user ID must be a valid integer.',
            'user_ids.*.exists' => 'One or more user IDs do not exist.',
            
            'emails.required_without' => 'You must provide either user IDs or emails.',
            'emails.array' => 'Emails must be provided as an array.',
            'emails.min' => 'You must provide at least one email.',
            'emails.*.email' => 'Each email must be a valid email address.',
            'emails.*.exists' => 'One or more emails do not exist in the system.',
        ];
    }
}