<?php

namespace App\Actions\User;

use App\Models\User;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\DomainException;

class ShowUserAction
{
    public function execute()
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.");
        }
        $userlist = User::all();
        return response()->json($userlist);
    }
}
