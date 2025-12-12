<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswerAllQuestionsRequest;
use App\Actions\PlayerTrivia\GetFullTriviaAction;
use App\Actions\PlayerTrivia\AnswerAllQuestionsAction;
use App\Actions\PlayerTrivia\MyAnswersAction;
use App\Actions\PlayerTrivia\TriviaRankingAction;
use App\Actions\PlayerTrivia\AllTriviasRankingAction;

class PlayerTriviaController extends Controller
{
    public function getFullTrivia($id, GetFullTriviaAction $action)
    {
        return $action->execute($id);
    }

    public function answerAllQuestions(AnswerAllQuestionsRequest $request, $id, AnswerAllQuestionsAction $action)
    {
        return $action->execute($id, $request->validated());
    }

    public function myAnswers($id, MyAnswersAction $action)
    {
        return $action->execute($id);
    }

    public function ranking($id, TriviaRankingAction $action)
    {
        return $action->execute($id);
    }

    public function allTriviasRanking(AllTriviasRankingAction $action)
    {
        return $action->execute();
    }
}