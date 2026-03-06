<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function checkout(Request $request, Booking $booking): View
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();
        $this->ensureBookingOwner($booking, $customer->id);

        $booking->load([
            'provider:id,name,email',
            'service:id,name,base_price',
            'serviceVariant:id,name,price',
            'payments' => fn ($query) => $query->latest(),
        ]);

        $amount = $booking->serviceVariant?->price ?? $booking->service?->base_price ?? 0;

        return view('customer.payments.checkout', [
            'booking' => $booking,
            'amount' => number_format((float) $amount, 2, '.', ''),
            'currency' => config('payment.currency', 'INR'),
        ]);
    }

    public function payOnline(Request $request, Booking $booking): RedirectResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();
        $this->ensureBookingOwner($booking, $customer->id);

        $data = $request->validate([
            'gateway' => 'required|in:razorpay,stripe,paypal',
            'payment_mode' => 'nullable|in:prepaid',
        ]);

        $this->paymentService->payOnline(
            $customer,
            $booking,
            $data['gateway'],
            $data['payment_mode'] ?? Payment::MODE_PREPAID
        );

        return redirect()
            ->route('customer.payments.index')
            ->with('success', 'Online payment initiated successfully.');
    }

    public function payCash(Request $request, Booking $booking): RedirectResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();
        $this->ensureBookingOwner($booking, $customer->id);

        $data = $request->validate([
            'payment_mode' => 'required|in:prepaid,postpaid',
        ]);

        $this->paymentService->payCash($customer, $booking, $data['payment_mode']);

        return redirect()
            ->route('customer.payments.index')
            ->with('success', 'Cash payment record created successfully.');
    }

    public function index(Request $request): View
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();

        $payments = Payment::query()
            ->with([
                'booking:id,booking_number,scheduled_at,status,service_id',
                'booking.service:id,name',
                'provider:id,name,email',
            ])
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(12);

        $payments->getCollection()->transform(function (Payment $payment) {
            $payment->setAttribute('can_refund', $this->paymentService->canRefund($payment));
            return $payment;
        });

        return view('customer.payments.index', compact('payments'));
    }

    public function refund(Request $request, Payment $payment): RedirectResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();

        if ((int) $payment->customer_id !== (int) $customer->id) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $this->paymentService->refund($customer, $payment, $data['reason'] ?? null);

        return redirect()
            ->route('customer.payments.index')
            ->with('success', 'Refund processed successfully.');
    }

    private function ensureBookingOwner(Booking $booking, int $customerId): void
    {
        if ((int) $booking->customer_id !== $customerId) {
            abort(403);
        }
    }
}
