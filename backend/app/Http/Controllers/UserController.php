<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Actions\User\UpdateUserAction;
use App\Actions\User\ShowUserAction;

class UserController extends Controller
{
    public function index(ShowUserAction $action)
    {
        return $action->execute();
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(UserUpdateRequest $request, $id, UpdateUserAction $action)
    {
        return $action->execute($id, $request->validated());
    }

    public function destroy($id)
    {
        //
    }
}
