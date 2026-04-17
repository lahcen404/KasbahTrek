<?php

namespace App\Repositories;

use App\Interfaces\VerificationRepositoryInterface;
use App\Models\Verification;
use App\Models\User;

class VerificationRepository implements VerificationRepositoryInterface
{
    public function createRequest(array $data)
    {
        $data['status'] = 'PENDING';
        return Verification::create($data);
    }

    public function getPendingRequests()
    {
        // get all pending requests with guide info
        return Verification::where('status', 'PENDING')
            ->with('guide:id,fullname,email')
            ->get();
    }

    public function updateStatus($id, $status)
    {
        $verification = Verification::findOrFail($id);
        
        $verification->update([
            'status' => strtoupper($status)
        ]);

        // check status and update user is_verified
        if (strtoupper($status) === 'APPROVED') {
            User::where('id', $verification->guide_id)->update(['is_verified' => true]);
        } elseif (strtoupper($status) === 'REJECTED') {
            User::where('id', $verification->guide_id)->update(['is_verified' => false]);
        }

        return $verification;
    }
}
