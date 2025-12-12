<?php

namespace App\Http\Controllers;

use App\Models\Trivia;
use Illuminate\Http\Request;
use App\Http\Requests\TriviaStoreRequest;
use App\Http\Requests\TriviaUpdateRequest;
use App\Http\Requests\TriviaAssignRequest;
use App\Http\Requests\TriviaUnassignRequest;

use App\Actions\Trivia\ListTriviaAction;
use App\Actions\Trivia\ShowTriviaAction;
use App\Actions\Trivia\StoreTriviaAction;
use App\Actions\Trivia\UpdateTriviaAction;
use App\Actions\Trivia\DeleteTriviaAction;
use App\Actions\Trivia\TriviaUsersAction;
use App\Actions\Trivia\AssignTriviaToUsersAction;
use App\Actions\Trivia\UnassignTriviaFromUsersAction;

class TriviaController extends Controller
{
   public function index(ListTriviaAction $action)
    {
        return $action->execute();
    }

    public function show($id, ShowTriviaAction $action)
    {
        return $action->execute($id);
    }

    public function store(TriviaStoreRequest $request, StoreTriviaAction $action)
    {
        return $action->execute($request->validated());
    }

    public function update(TriviaUpdateRequest $request, $id, UpdateTriviaAction $action)
    {
        return $action->execute($id, $request->validated());
    }

    public function destroy($id, DeleteTriviaAction $action)
    {
        return $action->execute($id);
    }

    public function triviaUsers($id, TriviaUsersAction $action)
    {
        return $action->execute($id);
    }

    public function assignToUsers(TriviaAssignRequest $request, $id, AssignTriviaToUsersAction $action)
    {
        return $action->execute($id, $request->validated());
    }

    public function unassignFromUsers(TriviaUnassignRequest $request, $id, UnassignTriviaFromUsersAction $action)
    {
        return $action->execute($id, $request->validated());
    }
}
