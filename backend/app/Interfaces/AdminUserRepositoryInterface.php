<?php

namespace App\Interfaces;

interface AdminUserRepositoryInterface
{
    public function getAllUsers();
    public function getUserById($id);
    public function updateUser($id, array $data);
    public function deleteUser($id);
}
