<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderBookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    public function index(Request $request): View
    {
        /** @var User $provider */
        $provider = $request->user();

        $bookings = Booking::query()
            ->with([
                'customer:id,name,email',
                'service:id,name',
                'serviceVariant:id,name',
                'slot:id,start_at,end_at',
            ])
            ->where('provider_id', $provider->id)
            ->latest('scheduled_at')
            ->paginate(12);

        $bookings->getCollection()->transform(function (Booking $booking) {
            $booking->setAttribute('can_provider_accept', $this->bookingService->canProviderAccept($booking));
            $booking->setAttribute('can_provider_complete', $this->bookingService->canProviderComplete($booking));
            $booking->setAttribute('can_provider_reschedule', $this->bookingService->canProviderReschedule($booking));

            return $booking;
        });

        return view('provider.bookings.index', [
            'bookings' => $bookings,
        ]);
    }

    public function accept(Request $request, Booking $booking): RedirectResponse
    {
        /** @var User $provider */
        $provider = $request->user();
        abort_unless((int) $booking->provider_id === (int) $provider->id, 403);

        $this->bookingService->acceptBookingByProvider($provider, $booking);

        return redirect()
            ->route('provider.bookings.index')
            ->with('success', 'Appointment accepted successfully.');
    }

    public function complete(Request $request, Booking $booking): RedirectResponse
    {
        /** @var User $provider */
        $provider = $request->user();
        abort_unless((int) $booking->provider_id === (int) $provider->id, 403);

        $this->bookingService->completeBookingByProvider($provider, $booking);

        return redirect()
            ->route('provider.bookings.index')
            ->with('success', 'Appointment marked as completed successfully.');
    }

    public function reschedule(Request $request, Booking $booking): RedirectResponse
    {
        /** @var User $provider */
        $provider = $request->user();
        abort_unless((int) $booking->provider_id === (int) $provider->id, 403);

        $data = $request->validate([
            'reschedule_to_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->bookingService->rescheduleBookingByProvider(
            $provider,
            $booking,
            (string) $data['reschedule_to_date'],
            $data['reason'] ?? null
        );

        return redirect()
            ->route('provider.bookings.index')
            ->with('success', 'Appointment rescheduled successfully.');
    }
}
