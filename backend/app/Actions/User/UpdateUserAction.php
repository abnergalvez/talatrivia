<?php

namespace App\Actions\User;

use App\Models\User;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\DomainException;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    public function execute($id, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth || !$userAuth->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $user = User::findOrFail($id);

        // Prevenir cambio de rol en admins
        if ($user->isAdmin() && isset($data['role_id'])) {
            $newRole = \App\Models\Role::find($data['role_id']);
            if ($newRole && $newRole->name !== 'admin') {
                throw new BusinessRuleException("Cannot change role for admin users.", 422);
            }
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->load('role')
        ], 200);
    }
}