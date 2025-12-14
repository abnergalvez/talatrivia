<?php

namespace App\Actions\User;

use App\Models\User;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\DomainException;

class ShowUserAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        $user = User::find($id);
        
        if (!$userAuth) {
            throw new BusinessRuleException("User not authenticated.", 401);
        }

        if (!$userAuth->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.");
        }
        if (!$user) {
            throw new DomainException("User not found.", 404);
        }
        
        return response()->json($user);
    }
}
