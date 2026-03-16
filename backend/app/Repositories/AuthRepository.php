<?php

namespace App\Repositories;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthRepository implements AuthRepositoryInterface {

    public function findByEmail(string $email) {
        return User::where('email', $email)->first();
    }

    public function attemptLogin(array $credentials): bool {
       
        return Auth::attempt($credentials);
    }

    public function logout(): void {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
