<?php

namespace App\Interfaces;

interface VerificationRepositoryInterface
{
    public function createRequest(array $data);
    public function getPendingRequests();
    public function updateStatus($id, $status);
}
