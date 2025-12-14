<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Requests\QuestionStoreRequest;
use App\Http\Requests\QuestionUpdateRequest;
use App\Http\Requests\BulkQuestionStoreRequest;
use App\Actions\Question\StoreBulkQuestionsAction;
use App\Actions\Question\ListQuestionsAction;
use App\Actions\Question\ShowQuestionAction;
use App\Actions\Question\StoreQuestionAction;
use App\Actions\Question\UpdateQuestionAction;
use App\Actions\Question\DeleteQuestionAction;

class QuestionController extends Controller
{
    public function index($triviaId, ListQuestionsAction $action)
    {
        return $action->execute($triviaId);
    }

    public function show($id, ShowQuestionAction $action)
    {
        return $action->execute($id);
    }

    public function store(QuestionStoreRequest $request, $triviaId, StoreQuestionAction $action)
    {
        return $action->execute($triviaId, $request->validated());
    }

    public function update(QuestionUpdateRequest $request, $id, UpdateQuestionAction $action)
    {
        return $action->execute($id, $request->validated());
    }

    public function destroy($id, DeleteQuestionAction $action)
    {
        return $action->execute($id);
    }

    public function bulkStore(BulkQuestionStoreRequest $request, $triviaId, StoreBulkQuestionsAction $action) 
    {
        return  $action->execute($triviaId, $request->validated());
    }
}
