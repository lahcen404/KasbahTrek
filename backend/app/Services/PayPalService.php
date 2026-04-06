<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalService
{
    public function baseUrl(): string
    {
        return config('paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function getAccessToken(): string
    {
        $clientId = config('paypal.client_id');
        $secret = config('paypal.secret');

        if (! is_string($clientId) || $clientId === '' || ! is_string($secret) || $secret === '') {
            throw new RuntimeException('PayPal client id or secret is not configured.');
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->timeout(30)
            ->post($this->baseUrl().'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('PayPal OAuth failed: '.$response->body());
        }

        $token = $response->json('access_token');
        if (! is_string($token) || $token === '') {
            throw new RuntimeException('PayPal OAuth response missing access_token.');
        }

        return $token;
    }

    /**
     * @return array{order_id: string, approval_url: string}
     */
    public function createOrder(Booking $booking): array
    {
        $booking->loadMissing('tour');

        $currency = config('paypal.currency');
        $amount = number_format((float) $booking->total_price, 2, '.', '');

        $token = $this->getAccessToken();

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => 'booking_'.$booking->id,
                    'custom_id' => (string) $booking->id,
                    'invoice_id' => 'KT-'.$booking->id,
                    'description' => $booking->tour?->title ?? 'Tour booking',
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $amount,
                    ],
                ],
            ],
            'application_context' => [
                'return_url' => rtrim((string) config('paypal.return_url'), '/'),
                'cancel_url' => rtrim((string) config('paypal.cancel_url'), '/'),
                'brand_name' => config('app.name'),
                'user_action' => 'PAY_NOW',
            ],
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($this->baseUrl().'/v2/checkout/orders', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('PayPal create order failed: '.$response->body());
        }

        $orderId = $response->json('id');
        $links = $response->json('links');
        if (! is_string($orderId) || $orderId === '' || ! is_array($links)) {
            throw new RuntimeException('PayPal create order response missing id or links.');
        }

        $approvalUrl = null;
        foreach ($links as $link) {
            if (($link['rel'] ?? null) === 'approve') {
                $approvalUrl = $link['href'] ?? null;
                break;
            }
        }

        if (! is_string($approvalUrl) || $approvalUrl === '') {
            throw new RuntimeException('PayPal create order response missing approve link.');
        }

        return [
            'order_id' => $orderId,
            'approval_url' => $approvalUrl,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function captureOrder(string $orderId): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($this->baseUrl().'/v2/checkout/orders/'.$orderId.'/capture');

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $body = $response->json();
        $name = is_array($body) ? ($body['name'] ?? null) : null;

        if ($name === 'RESOURCE_NOT_FOUND' || $response->status() === 422) {
            $order = $this->getOrder($orderId);
            if (($order['status'] ?? null) === 'COMPLETED') {
                return $order;
            }
        }

        throw new RuntimeException('PayPal capture failed: '.$response->body());
    }

    /**
     * @return array<string, mixed>
     */
    public function getOrder(string $orderId): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(30)
            ->get($this->baseUrl().'/v2/checkout/orders/'.$orderId);

        if (! $response->successful()) {
            throw new RuntimeException('PayPal get order failed: '.$response->body());
        }

        return $response->json() ?? [];
    }

    public function verifyWebhookSignature(Request $request, array $payload): bool
    {
        $webhookId = config('paypal.webhook_id');
        if (! is_string($webhookId) || $webhookId === '') {
            return false;
        }

        $verify = [
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'webhook_id' => $webhookId,
            'webhook_event' => $payload,
        ];

        foreach (['auth_algo', 'cert_url', 'transmission_id', 'transmission_sig', 'transmission_time'] as $key) {
            if (empty($verify[$key])) {
                return false;
            }
        }

        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($this->baseUrl().'/v1/notifications/verify-webhook-signature', $verify);

        if (! $response->successful()) {
            return false;
        }

        return ($response->json('verification_status') ?? '') === 'SUCCESS';
    }
}
