<?php

namespace App\Actions\User;

use App\Models\User;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\DomainException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StoreUserAction
{
    public function execute($data)
    {
        $userAuth = app('user');
        if (!$userAuth) {
            throw new BusinessRuleException("User not authenticated.", 401);
        }

        if (!$userAuth->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.");
        }

        // Por defecto, los nuevos usuarios son players
        $playerRole = \App\Models\Role::where('name', 'player')->first();
        $user = User::create([
            'name' => $data['name'],          
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'api_token' => Str::random(60),
            'role_id' => $data['role_id'] ?? $playerRole->id
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user->load('role'),
            'token' => $user->api_token
        ], 201);

    }
}
