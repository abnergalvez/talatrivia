<?php

namespace App\Actions\Role;

use App\Models\Role;
use App\Exceptions\BusinessRuleException;

class UpdateRoleAction
{
    public function execute($id, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $role = Role::findOrFail($id);

        if (in_array($role->name, ['admin', 'player'])) {
            throw new BusinessRuleException("Cannot modify system roles (admin, player).", 422);
        }

        $role->update($data);

        return response()->json([
            'message' => 'Role updated successfully.',
            'role' => $role
        ], 200);
    }
}