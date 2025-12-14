<?php

namespace App\Actions\Role;

use App\Models\Role;
use App\Exceptions\BusinessRuleException;

class ShowRoleAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $role = Role::withCount('users')->findOrFail($id);

        return response()->json([
            'role' => $role
        ], 200);
    }
}