<?php

namespace App\Http\Controllers\Api;

use App\Interfaces\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function login(Request $request)
    {
        // valiiidation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // create tokkeen
        $token = $this->authRepository->createToken(
            $request->email,
            $request->password
        );

        if (!$token) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = $this->authRepository->findByEmail($request->email);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'fullname' => $user->fullname,
                'role' => $user->role->value,
            ]
        ], 200);
    }
}
