<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OnlineGatewayService
{
    public function createOrder(
        string $gateway,
        float $amount,
        string $currency,
        array $metadata = []
    ): array {
        return match ($gateway) {
            'razorpay' => $this->createRazorpayOrder($amount, $currency, $metadata),
            'stripe' => $this->createStripePaymentIntent($amount, $currency, $metadata),
            'paypal' => $this->createPayPalOrder($amount, $currency, $metadata),
            default => throw ValidationException::withMessages([
                'gateway' => 'Unsupported payment gateway.',
            ]),
        };
    }

    public function refund(string $gateway, string $reference, float $amount, string $currency): array
    {
        return match ($gateway) {
            'razorpay' => $this->refundRazorpay($reference, $amount, $currency),
            'stripe' => $this->refundStripe($reference, $amount, $currency),
            'paypal' => $this->refundPayPal($reference, $amount, $currency),
            default => [
                'reference' => 'refund_sim_'.Str::upper(Str::random(10)),
                'status' => 'refunded',
                'simulated' => true,
            ],
        };
    }

    private function createRazorpayOrder(float $amount, string $currency, array $metadata): array
    {
        $keyId = (string) config('services.razorpay.key_id');
        $secret = (string) config('services.razorpay.key_secret');
        if ($keyId === '' || $secret === '') {
            return $this->simulatedOrder('razorpay', $amount, $currency, $metadata);
        }

        $response = Http::withBasicAuth($keyId, $secret)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round($amount * 100),
                'currency' => strtoupper($currency),
                'receipt' => $metadata['booking_number'] ?? ('rcpt_'.Str::random(8)),
                'notes' => $metadata,
            ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'gateway' => 'Unable to create Razorpay order.',
            ]);
        }

        $data = $response->json();

        return [
            'reference' => $data['id'] ?? ('rzp_'.Str::random(12)),
            'status' => 'pending',
            'simulated' => false,
            'payload' => $data,
        ];
    }

    private function createStripePaymentIntent(float $amount, string $currency, array $metadata): array
    {
        $secret = (string) config('services.stripe.secret');
        if ($secret === '') {
            return $this->simulatedOrder('stripe', $amount, $currency, $metadata);
        }

        $response = Http::asForm()
            ->withToken($secret)
            ->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => (int) round($amount * 100),
                'currency' => strtolower($currency),
                'capture_method' => 'automatic',
                'metadata[booking_number]' => $metadata['booking_number'] ?? '',
            ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'gateway' => 'Unable to create Stripe payment intent.',
            ]);
        }

        $data = $response->json();

        return [
            'reference' => $data['id'] ?? ('pi_'.Str::random(12)),
            'status' => 'pending',
            'simulated' => false,
            'payload' => $data,
        ];
    }

    private function createPayPalOrder(float $amount, string $currency, array $metadata): array
    {
        $clientId = (string) config('services.paypal.client_id');
        $clientSecret = (string) config('services.paypal.client_secret');
        if ($clientId === '' || $clientSecret === '') {
            return $this->simulatedOrder('paypal', $amount, $currency, $metadata);
        }

        $baseUrl = config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $tokenResponse = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post($baseUrl.'/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        if ($tokenResponse->failed()) {
            throw ValidationException::withMessages([
                'gateway' => 'Unable to authenticate with PayPal.',
            ]);
        }

        $accessToken = $tokenResponse->json('access_token');
        if (!$accessToken) {
            throw ValidationException::withMessages([
                'gateway' => 'Invalid PayPal token response.',
            ]);
        }

        $orderResponse = Http::withToken($accessToken)->post($baseUrl.'/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => strtoupper($currency),
                    'value' => number_format($amount, 2, '.', ''),
                ],
                'reference_id' => $metadata['booking_number'] ?? Str::uuid()->toString(),
            ]],
        ]);

        if ($orderResponse->failed()) {
            throw ValidationException::withMessages([
                'gateway' => 'Unable to create PayPal order.',
            ]);
        }

        $data = $orderResponse->json();

        return [
            'reference' => $data['id'] ?? ('pp_'.Str::random(12)),
            'status' => 'pending',
            'simulated' => false,
            'payload' => $data,
        ];
    }

    private function refundRazorpay(string $reference, float $amount, string $currency): array
    {
        $keyId = (string) config('services.razorpay.key_id');
        $secret = (string) config('services.razorpay.key_secret');
        if ($keyId === '' || $secret === '') {
            return $this->simulatedRefund();
        }

        $response = Http::withBasicAuth($keyId, $secret)
            ->post('https://api.razorpay.com/v1/payments/'.$reference.'/refund', [
                'amount' => (int) round($amount * 100),
                'notes' => ['currency' => strtoupper($currency)],
            ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'refund' => 'Razorpay refund failed.',
            ]);
        }

        $data = $response->json();

        return [
            'reference' => $data['id'] ?? ('rfnd_'.Str::random(12)),
            'status' => 'refunded',
            'simulated' => false,
            'payload' => $data,
        ];
    }

    private function refundStripe(string $reference, float $amount, string $currency): array
    {
        $secret = (string) config('services.stripe.secret');
        if ($secret === '') {
            return $this->simulatedRefund();
        }

        $response = Http::asForm()
            ->withToken($secret)
            ->post('https://api.stripe.com/v1/refunds', [
                'payment_intent' => $reference,
                'amount' => (int) round($amount * 100),
                'metadata[currency]' => strtoupper($currency),
            ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'refund' => 'Stripe refund failed.',
            ]);
        }

        $data = $response->json();

        return [
            'reference' => $data['id'] ?? ('rf_'.Str::random(12)),
            'status' => 'refunded',
            'simulated' => false,
            'payload' => $data,
        ];
    }

    private function refundPayPal(string $reference, float $amount, string $currency): array
    {
        $clientId = (string) config('services.paypal.client_id');
        $clientSecret = (string) config('services.paypal.client_secret');
        if ($clientId === '' || $clientSecret === '') {
            return $this->simulatedRefund();
        }

        $baseUrl = config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $tokenResponse = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post($baseUrl.'/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        if ($tokenResponse->failed()) {
            throw ValidationException::withMessages([
                'refund' => 'PayPal authentication failed.',
            ]);
        }

        $token = $tokenResponse->json('access_token');
        if (!$token) {
            throw ValidationException::withMessages([
                'refund' => 'Invalid PayPal token response.',
            ]);
        }

        $response = Http::withToken($token)
            ->post($baseUrl.'/v2/payments/captures/'.$reference.'/refund', [
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency_code' => strtoupper($currency),
                ],
            ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'refund' => 'PayPal refund failed.',
            ]);
        }

        $data = $response->json();

        return [
            'reference' => $data['id'] ?? ('rfnd_'.Str::random(12)),
            'status' => 'refunded',
            'simulated' => false,
            'payload' => $data,
        ];
    }

    private function simulatedOrder(string $gateway, float $amount, string $currency, array $metadata): array
    {
        return [
            'reference' => $gateway.'_sim_'.Str::upper(Str::random(10)),
            'status' => 'pending',
            'simulated' => true,
            'payload' => [
                'gateway' => $gateway,
                'amount' => $amount,
                'currency' => strtoupper($currency),
                'metadata' => $metadata,
            ],
        ];
    }

    private function simulatedRefund(): array
    {
        return [
            'reference' => 'refund_sim_'.Str::upper(Str::random(10)),
            'status' => 'refunded',
            'simulated' => true,
            'payload' => [],
        ];
    }
}
