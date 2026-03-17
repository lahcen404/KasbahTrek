<?php

namespace App\Repositories;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthRepository implements AuthRepositoryInterface {

    public function findByEmail(string $email) {
        return User::where('email', $email)->first();
    }

    public function createToken(string $email, string $password): ?string
    {
        $user = $this->findByEmail($email);

        //cheeck id useer exists and password is correct
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        // generaate a random token
        $plainToken = Str::random(64);

        // stoore hashed version in DB
        DB::table('personal_access_tokens')->insert([
            'user_id' => $user->id,
            'name' => 'api_token',
            'token' => hash('sha256', $plainToken),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $plainToken;
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
