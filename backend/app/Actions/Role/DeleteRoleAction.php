<?php

namespace App\Actions\Role;

use App\Models\Role;
use App\Exceptions\BusinessRuleException;

class DeleteRoleAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $role = Role::findOrFail($id);

        if (in_array($role->name, ['admin', 'player'])) {
            throw new BusinessRuleException("Cannot delete system roles (admin, player).", 422);
        }

        if ($role->users()->exists()) {
            throw new BusinessRuleException("Cannot delete role with assigned users. Role has {$role->users()->count()} user(s).", 422);
        }

        $roleName = $role->name;
        $role->delete();

        return response()->json([
            'message' => "Role '{$roleName}' deleted successfully."
        ], 200);
    }
}