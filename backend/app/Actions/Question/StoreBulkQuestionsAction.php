<?php

namespace App\Actions\Question;

use App\Models\Trivia;
use App\Models\Question;
use App\Models\Level;
use Illuminate\Support\Facades\DB;
use App\Exceptions\BusinessRuleException;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBulkQuestionsAction
{
    public function execute($triviaId, array $questionsData)
    {
        $userAuth = app('user');

        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $trivia = Trivia::findOrFail($triviaId);

        $levelIds = collect($questionsData)
            ->pluck('level_id')
            ->unique()
            ->values();

        $existingLevels = Level::whereIn('id', $levelIds)->pluck('id');
        $missingLevels = $levelIds->diff($existingLevels);

        if ($missingLevels->isNotEmpty()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Some level_id values do not exist.',
                'invalid_levels' => $missingLevels->values(),
            ], 422));
        }

        $created = DB::transaction(function () use ($triviaId, $questionsData) {

            $createdQuestions = [];

            foreach ($questionsData as $qData) {

                $options = $qData['options'];
                unset($qData['options']);

                $qData['trivia_id'] = $triviaId;

                $question = Question::create($qData);
                foreach ($options as $op) {
                    $question->options()->create($op);
                }

                $question->load(['level', 'options']);
                $createdQuestions[] = $question;
            }

            return $createdQuestions;
        });

        return response()->json([
            'message' => 'Bulk questions created successfully.',
            'questions' => $created
        ], 201);
    }
}
