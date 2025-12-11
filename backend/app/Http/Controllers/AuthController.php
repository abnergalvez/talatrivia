<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Registro de usuario (público)
     * Siempre queda con rol "player"
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        // Buscar rol player
        $playerRole = Role::where('code', 'player')->first();

        if (!$playerRole) {
            return response()->json([
                'error' => 'Role player not found. Run seeder.'
            ], 500);
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'api_token' => Str::random(60),
            'role_id'   => $playerRole->id,
        ]);

        return response()->json([
            'message' => 'User registered as player',
            'token'   => $user->api_token,
            'user'    => $user
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        $user->api_token = Str::random(60);
        $user->save();

        return response()->json([
            'message' => 'Login successful',
            'token'   => $user->api_token,
            'user'    => $user,
            'role'    => $user->role->code,
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = app('user');

        $user->api_token = null;
        $user->save();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
