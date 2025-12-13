<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;

trait AuthHelper
{
    protected function createUser(string $roleName = 'player'): User
    {
        $role = Role::where('name', $roleName)->first();

        return User::factory()->create([
            'role_id' => $role->id,
            'api_token' => Str::random(60),
        ]);
    }

    protected function actingAs(User $user): array
    {
        return [
            'Authorization' => 'Bearer ' . $user->api_token,
        ];
    }
}
