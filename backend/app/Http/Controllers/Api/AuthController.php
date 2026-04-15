<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Interfaces\AuthRepositoryInterface;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function login(LoginRequest $request)
    {
        $token = $this->authRepository->createToken(
            $request->validated('email'),
            $request->validated('password')
        );

        if (!$token) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = $this->authRepository->findByEmail($request->validated('email'));

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'fullname' => $user->fullname,
                'role' => $user->role->value,
            ],
        ], 200);
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authRepository->register($request->validated());

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'role' => $user->role?->value,
            'is_verified' => (bool) $user->is_verified,
            'verification_request' => $user->verificationRequest?->loadMissing('guide:id,fullname,email,is_verified'),
        ]);
    }

    public function logout(Request $request)
    {
        $header = $request->header('Authorization');
        $plainToken = str_replace('Bearer ', '', $header);

        $this->authRepository->logout($plainToken);

        return response()->json([
            'message' => 'Successfully logged out !!',
        ], 200);
    }
}
