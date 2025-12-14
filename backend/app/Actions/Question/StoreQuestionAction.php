<?php

namespace App\Actions\Question;

use App\Models\Trivia;
use App\Models\Question;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class StoreQuestionAction
{
    public function execute($triviaId, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $trivia = Trivia::findOrFail($triviaId);

        $question = DB::transaction(function () use ($triviaId, $data) {
            $options = $data['options'];
            unset($data['options']);

            $data['trivia_id'] = $triviaId;
            
            $question = Question::create($data);
            foreach ($options as $optionData) {
                $question->options()->create($optionData);
            }
            
            $question->load(['level', 'options']);

            return $question;
        });

        return response()->json([
            'message' => 'Question created successfully.',
            'question' => $question
        ], 201);
    }
}