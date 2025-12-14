<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserStoreRequest;
use App\Actions\User\UpdateUserAction;
use App\Actions\User\ShowUserAction;
use App\Actions\User\ListUsersAction;
use App\Actions\User\StoreUserAction;
use App\Actions\User\DeleteUserAction;
use App\Actions\User\AssignedTriviasAction;


class UserController extends Controller
{
    public function index(ListUsersAction $action)
    {
        return $action->execute();
    }

    public function store(UserStoreRequest $request, StoreUserAction $action)
    {
        return $action->execute($request->validated());
    }

    public function show($id, ShowUserAction $action)
    {
        return $action->execute($id);
    }

    public function update(UserUpdateRequest $request, $id, UpdateUserAction $action)
    {
        return $action->execute($id, $request->validated());
    }

    public function destroy($id, DeleteUserAction $action)
    {
        return $action->execute($id);
    }

    public function assignedTrivias(AssignedTriviasAction $action)
    {
        return $action->execute();
    }
    
}
