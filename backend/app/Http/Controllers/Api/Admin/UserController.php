<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateUserRequest;
use App\Interfaces\AdminUserRepositoryInterface;

class UserController extends Controller
{
    protected $adminUserRepository;

    public function __construct(AdminUserRepositoryInterface $adminUserRepository)
    {
        $this->adminUserRepository = $adminUserRepository;
    }

    public function index()
    {
        $users = $this->adminUserRepository->getAllUsers();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    public function show($id)
    {
        $user = $this->adminUserRepository->getUserById($id);

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->adminUserRepository->updateUser($id, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully!!!',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        $this->adminUserRepository->deleteUser($id);

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully!!!'
        ]);
    }
}
