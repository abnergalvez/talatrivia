<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use App\Http\Requests\LevelStoreRequest;
use App\Http\Requests\LevelUpdateRequest;
use App\Actions\Level\ListLevelAction;
use App\Actions\Level\ShowLevelAction;
use App\Actions\Level\StoreLevelAction;
use App\Actions\Level\UpdateLevelAction;
use App\Actions\Level\DeleteLevelAction;

class LevelController extends Controller
{
       public function index(ListLevelAction $action)
    {
        return $action->execute();
    }

    public function show($id, ShowLevelAction $action)
    {
        return $action->execute($id);
    }

    public function store(LevelStoreRequest $request, StoreLevelAction $action)
    {
        return $action->execute($request->validated());
    }

    public function update(LevelUpdateRequest $request, $id, UpdateLevelAction $action)
    {
        return $action->execute($id, $request->validated());
    }

    public function destroy($id, DeleteLevelAction $action)
    {
        return $action->execute($id);
    }
}
