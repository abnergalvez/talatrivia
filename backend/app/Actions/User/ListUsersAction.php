<?php

namespace App\Actions\User;

use App\Models\User;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\DomainException;

class ListUsersAction
{
    public function execute()
    {
        $user = app('user');
        
        if (!$user) {
            throw new BusinessRuleException("User not authenticated.", 401);
        }

        if (!$user->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.");
        }
        $userlist = User::with('role')->get();
        
        return response()->json($userlist);
    }
}
