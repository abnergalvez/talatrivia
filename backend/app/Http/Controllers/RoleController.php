<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Actions\Role\ListRoleAction;
use App\Actions\Role\ShowRoleAction;
use App\Actions\Role\StoreRoleAction;
use App\Actions\Role\UpdateRoleAction;
use App\Actions\Role\DeleteRoleAction;

class RoleController extends Controller
{
    public function index(ListRoleAction $action)
    {
        return $action->execute();
    }

    public function show($id, ShowRoleAction $action)
    {
        return $action->execute($id);
    }

    public function store(RoleStoreRequest $request, StoreRoleAction $action)
    {
        return $action->execute($request->validated());
    }

    public function update(RoleUpdateRequest $request, $id, UpdateRoleAction $action)
    {
        return $action->execute($id, $request->validated());
    }

    public function destroy($id, DeleteRoleAction $action)
    {
        return $action->execute($id);
    }
}
