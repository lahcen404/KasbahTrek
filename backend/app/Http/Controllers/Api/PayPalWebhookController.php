<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingPaymentService;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayPalWebhookController extends Controller
{
    public function __construct(
        private BookingPaymentService $bookingPayment,
        private PayPalService $payPal
    ) {}

    public function handle(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $webhookId = config('paypal.webhook_id');
        if (! is_string($webhookId) || $webhookId === '') {
            return response()->json(['received' => true, 'message' => 'Webhook not configured — skipped']);
        }

        $raw = $request->getContent();
        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid JSON'], 400);
        }

        if (! $this->payPal->verifyWebhookSignature($request, $payload)) {
            return response()->json(['message' => 'Invalid webhook signature'], 400);
        }

        if (($payload['event_type'] ?? '') !== 'PAYMENT.CAPTURE.COMPLETED') {
            return response()->json(['received' => true]);
        }

        $resource = $payload['resource'] ?? [];
        if (! is_array($resource)) {
            return response()->json(['received' => true]);
        }

        $bookingId = $resource['custom_id'] ?? null;
        if (($bookingId === null || $bookingId === '') && isset($resource['invoice_id']) && is_string($resource['invoice_id'])) {
            $invoiceId = $resource['invoice_id'];
            if (str_starts_with($invoiceId, 'KT-')) {
                $bookingId = substr($invoiceId, 3);
            }
        }
        if ($bookingId === null || $bookingId === '') {
            return response()->json(['received' => true]);
        }

        $booking = Booking::query()->find($bookingId);
        if (! $booking) {
            return response()->json(['received' => true]);
        }

        $this->bookingPayment->markPaidAndSendReceipt($booking);

        return response()->json(['received' => true]);
    }
}
