<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;

class BulkQuestionStoreRequest extends FormRequest
{
    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        return [
            '*.level_id' => 'required|exists:levels,id',
            '*.description' => 'required|string|min:10|max:1000',
            '*.time' => 'nullable|integer|min:1|max:3600',

            '*.options' => 'required|array|min:2|max:6',
            '*.options.*.text' => 'required|string|min:1|max:500',
            '*.options.*.is_correct' => 'required|boolean',
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();

            foreach ($data as $index => $question) {
                if (!isset($question['options'])) {
                    $validator->errors()->add("$index.options", "Each question must contain options.");
                    continue;
                }

                $correct = collect($question['options'])
                    ->filter(fn ($op) => ($op['is_correct'] ?? false) === true)
                    ->count();

                if ($correct === 0) {
                    $validator->errors()->add("$index.options", "At least one option must be correct.");
                }

                if ($correct > 1) {
                    $validator->errors()->add("$index.options", "Only one option can be correct.");
                }
            }
        });
    }
}
