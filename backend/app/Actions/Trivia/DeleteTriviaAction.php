<?php

namespace App\Actions\Trivia;

use App\Models\Trivia;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class DeleteTriviaAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $trivia = Trivia::with(['questions', 'users'])->findOrFail($id);

        $hasAnswers = DB::table('answers')
            ->whereIn('question_id', $trivia->questions->pluck('id'))
            ->exists();

        if ($hasAnswers) {
            throw new BusinessRuleException("Cannot delete trivia with recorded answers. This trivia has active game history.", 422);
        }

        $triviaName = $trivia->name;
        $usersCount = $trivia->users->count();
        $questionsCount = $trivia->questions->count();

        DB::transaction(function () use ($trivia) {
            $trivia->users()->detach();
            
            foreach ($trivia->questions as $question) {
                $question->options()->delete(); 
                $question->delete();
            }
            
            $trivia->delete();
        });

        $message = "Trivia '{$triviaName}' deleted successfully.";
        
        if ($usersCount > 0 || $questionsCount > 0) {
            $details = [];
            if ($usersCount > 0) $details[] = "{$usersCount} user(s) dissociated";
            if ($questionsCount > 0) $details[] = "{$questionsCount} question(s) deleted";
            $message .= " " . implode(", ", $details) . ".";
        }

        return response()->json([
            'message' => $message
        ], 200);
    }
}