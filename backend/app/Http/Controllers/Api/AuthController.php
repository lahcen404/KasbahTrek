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

    //register
    public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'fullname' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'role' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $user = $this->authRepository->register($request->all());

    return response()->json([
        'message' => 'User registered successfully',
        'user' => $user
    ], 201);
}

// logout
public function logout(Request $request)
{
    $header = $request->header('Authorization');
    $plainToken = str_replace('Bearer ', '', $header);

    $this->authRepository->logout($plainToken);

    return response()->json([
        'message' => 'Successfully logged out !!'
    ], 200);
}
}
