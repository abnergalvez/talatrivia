<?php

namespace App\Actions\Role;

use App\Models\Role;
use App\Exceptions\BusinessRuleException;

class ListRoleAction
{
    public function execute()
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $roles = Role::withCount('users')->get();

        return response()->json([
            'roles' => $roles
        ], 200);
    }
}