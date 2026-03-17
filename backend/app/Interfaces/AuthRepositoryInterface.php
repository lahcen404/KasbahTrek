<?php

namespace App\Interfaces;

interface AuthRepositoryInterface {
    // find by email
    public function findByEmail(string $email);

    // create token
    public function createToken(string $email, string $password);

    // attempt login
    public function attemptLogin(array $credentials): bool;

    // logout
    public function logout(): void;
}
