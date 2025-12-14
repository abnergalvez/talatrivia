<?php

namespace App\Actions\Role;

use App\Models\Role;
use App\Exceptions\BusinessRuleException;

class StoreRoleAction
{
    public function execute(array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $role = Role::create($data);

        return response()->json([
            'message' => 'Role created successfully.',
            'role' => $role
        ], 201);
    }
}