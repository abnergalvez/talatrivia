<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Tests\DatabaseMigrations;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use DatabaseMigrations; 
    public function test_user_can_login_with_valid_credentials()
    {
        $role = Role::create([
            'name' => 'player'
        ]);

        $user = User::create([
            'name' => 'Player Test',
            'email' => 'player@test.com',
            'password' => Hash::make('secret123'),
            'role_id' => $role->id,
        ]);

        $response = $this->post('/api/login', [
            'email' => 'player@test.com',
            'password' => 'secret123',
        ]);

        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'user' => ['id', 'name', 'email', 'role'],
            'token'
        ]);
        $response->seeJson([
            'email' => 'player@test.com',
            'name' => 'Player Test',
            'role' => 'player'
        ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $role = Role::create(['name' => 'player']);

        User::create([
            'name' => 'Player Test',
            'email' => 'player@test.com',
            'password' => Hash::make('secret123'),
            'role_id' => $role->id,
        ]);

        $response = $this->post('/api/login', [
            'email' => 'player@test.com',
            'password' => 'wrong-password',
        ]);

        $response->seeStatusCode(401);
        $response->seeJson(['error' => 'Invalid credentials']);
    }

    public function test_login_requires_email()
    {
        $response = $this->post('/api/login', [
            'password' => 'secret123',
        ]);

        $response->seeStatusCode(422);
    }

    public function test_login_requires_password()
    {
        $response = $this->post('/api/login', [
            'email' => 'player@test.com',
        ]);

        $response->seeStatusCode(422);
    }
}