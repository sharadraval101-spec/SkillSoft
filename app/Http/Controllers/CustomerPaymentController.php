<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerPaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function checkout(Request $request, Booking $booking): RedirectResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();
        $this->ensureBookingOwner($booking, $customer->id);

        return redirect()->to(route('customer.dashboard', [
            'pay_booking' => $booking->getKey(),
        ]).'#payments-center');
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
            ->to(route('customer.dashboard').'#payments-center')
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
            ->to(route('customer.dashboard').'#payments-center')
            ->with('success', 'Cash payment record created successfully.');
    }

    public function index(Request $request): RedirectResponse
    {
        return redirect()->to(route('customer.dashboard').'#payments-center');
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
            ->to(route('customer.dashboard').'#payments-center')
            ->with('success', 'Refund processed successfully.');
    }

    private function ensureBookingOwner(Booking $booking, int $customerId): void
    {
        if ((int) $booking->customer_id !== $customerId) {
            abort(403);
        }
    }
}
