<?php

namespace App\Interfaces;

interface AuthRepositoryInterface {
    // find by email
    public function findByEmail(string $email);

    // attempt login
    public function attemptLogin(array $credentials): bool;

    // logout
    public function logout(): void;
}
