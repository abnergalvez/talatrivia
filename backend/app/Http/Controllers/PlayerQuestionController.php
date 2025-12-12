<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswerQuestionRequest;
use App\Actions\PlayerQuestion\AnswerQuestionAction;

class PlayerQuestionController extends Controller
{
    public function answerQuestion(AnswerQuestionRequest $request, $id, AnswerQuestionAction $action)
    {
        return $action->execute($id, $request->validated());
    }
}