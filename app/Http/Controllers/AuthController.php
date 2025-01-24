<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {

        $user = User::where('user_name', $request->user_name)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        return response()->json([
            'message' => 'User logged in successfully',
            'data' => [
                'user' => new UserResource($user->load('userType', 'roles')),
                'token' => $user->createToken($request->user_name)->plainTextToken
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'User logged out successfully'], 200);
    }


}
