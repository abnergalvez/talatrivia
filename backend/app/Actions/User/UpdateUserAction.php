<?php

namespace App\Actions\User;

use App\Models\User;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\DomainException;

class UpdateUserAction
{
    public function execute($id, $data)
    {
        $user = User::find($id);

        if (!$user) {
            throw new DomainException("User not found", 404);
        }

        if ($user->role === 'admin' && isset($data['role'])) {
            throw new BusinessRuleException("Cannot change role for admin users.");
        }

        $user->update($data);

        return $user;
    }
}
