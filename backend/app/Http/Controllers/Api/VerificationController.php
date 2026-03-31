<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Verification\StoreVerificationRequest;
use App\Http\Requests\Api\Verification\UpdateVerificationStatusRequest;
use App\Interfaces\VerificationRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    protected $verificationRepository;

    public function __construct(VerificationRepositoryInterface $verificationRepository)
    {
        $this->verificationRepository = $verificationRepository;
    }

    public function index()
    {
        $requests = $this->verificationRepository->getPendingRequests();

        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }

    public function store(StoreVerificationRequest $request)
    {
        $fileUrl = '';
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('verifications', 'public');
            $fileUrl = Storage::url($path);
        }

        $data = [
            'guide_id' => Auth::id(),
            'file_url' => $fileUrl
        ];

        $verification = $this->verificationRepository->createRequest($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Verification request submitted successfully',
            'data' => $verification
        ], 201);
    }

    public function updateStatus(UpdateVerificationStatusRequest $request, $id)
    {
        $verification = $this->verificationRepository->updateStatus($id, $request->status);

        // diiispatch event to trigger the email listener
        event(new \App\Events\VerificationStatusUpdated($verification));

        return response()->json([
            'status' => 'success',
            'message' => 'Verification status updated successfully',
            'data' => $verification
        ]);
    }
}
