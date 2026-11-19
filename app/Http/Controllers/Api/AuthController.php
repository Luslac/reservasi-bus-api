<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function register(RegisterRequest $request)
    {
        // Role selalu customer; admin hanya dibuat lewat seeder
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password, // di-hash otomatis oleh cast pada model
            'role'     => 'customer',
        ]);

        return response()->json(['data' => $this->payload($user)], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw new ApiException('Email atau password salah.', 401, 'INVALID_CREDENTIALS');
        }

        return response()->json(['data' => $this->payload($user)]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    private function payload(User $user): array
    {
        return [
            'token' => $user->createToken('api')->plainTextToken,
            'user'  => (new UserResource($user))->resolve(),
        ];
    }
}
