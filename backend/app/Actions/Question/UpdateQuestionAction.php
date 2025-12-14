<?php

namespace App\Actions\Question;

use App\Models\Question;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class UpdateQuestionAction
{
    public function execute($id, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $question = Question::findOrFail($id);

        DB::transaction(function () use ($question, $data) {
            
            if (isset($data['options'])) {
                $options = $data['options'];
                unset($data['options']);
                $question->options()->delete();

                foreach ($options as $optionData) {
                    $question->options()->create($optionData);
                }
            }

            if (!empty($data)) {
                $question->update($data);
            }
        });

        $question->load(['level', 'options', 'trivia']);

        return response()->json([
            'message' => 'Question updated successfully.',
            'question' => $question
        ], 200);
    }
}