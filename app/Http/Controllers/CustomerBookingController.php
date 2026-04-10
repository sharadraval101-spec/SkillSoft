<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingService;
use App\Services\ScheduleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerBookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly ScheduleAvailabilityService $availabilityService
    ) {
    }

    public function index(Request $request): View
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();

        $bookings = Booking::query()
            ->with([
                'provider:id,name,email',
                'service:id,name',
                'serviceVariant:id,name',
                'slot:id,start_at,end_at',
                'payments:id,booking_id,status',
                'review:id,booking_id,rating,is_approved',
            ])
            ->where('customer_id', $customer->id)
            ->latest('scheduled_at')
            ->paginate(12);

        $bookings->getCollection()->transform(function (Booking $booking) {
            $booking->setAttribute('can_reschedule', false);
            $booking->setAttribute('can_cancel', $this->bookingService->canCancel($booking));
            $booking->setAttribute('has_paid_payment', $booking->payments->contains(function ($payment) {
                return in_array($payment->status, [
                    \App\Models\Payment::STATUS_PAID,
                    \App\Models\Payment::STATUS_REFUNDED,
                ], true);
            }));

            return $booking;
        });

        return view('customer.bookings.index', compact('bookings'));
    }

    public function create(Request $request): View
    {
        $request->validate([
            'provider_id' => 'nullable|integer|exists:users,id',
            'service_id' => 'nullable|uuid',
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'date' => 'nullable|date|after_or_equal:today',
        ]);

        $providers = User::query()
            ->where('role', User::ROLE_PROVIDER)
            ->where('is_active', true)
            ->whereHas('providerProfile', fn ($query) => $query->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedProvider = $providers->firstWhere('id', (int) $request->query('provider_id'));
        $selectedDate = Carbon::parse((string) $request->query('date', now()->addDay()->toDateString()))->startOfDay();
        $selectedBranchId = $request->filled('branch_id') ? (string) $request->query('branch_id') : null;

        $services = Service::query()
            ->with(['providerProfile:id,user_id,status', 'variants' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->whereHas('providerProfile', function ($query) {
                $query
                    ->where('status', 'active')
                    ->whereHas('user', fn ($userQuery) => $userQuery->where('is_active', true));
            })
            ->when($selectedProvider, function ($query) use ($selectedProvider) {
                $query->whereHas('providerProfile', fn ($innerQuery) => $innerQuery->where('user_id', $selectedProvider->id));
            })
            ->orderBy('name')
            ->get(['id', 'provider_profile_id', 'name', 'duration_minutes', 'branch_id']);

        $selectedService = $services->firstWhere('id', $request->query('service_id'));

        if ($request->filled('service_id') && !$selectedService) {
            throw ValidationException::withMessages([
                'service_id' => 'Selected service is invalid for current filter.',
            ]);
        }

        if ($selectedService && !$selectedProvider) {
            $providerId = (int) $selectedService->providerProfile?->user_id;
            $selectedProvider = $providers->firstWhere('id', $providerId);
        }

        $branchIds = collect([$selectedProvider?->providerProfile?->branch_id, $selectedService?->branch_id])
            ->filter()
            ->unique()
            ->values();

        $branches = Branch::query()
            ->where('is_active', true)
            ->when($branchIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $branchIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        $availableSlots = collect();
        if ($selectedProvider && $selectedService) {
            $availableSlots = $this->availabilityService->generateAvailableSlotsForDate(
                $selectedProvider,
                $selectedDate,
                $selectedBranchId,
                $selectedService
            );
        }

        return view('customer.bookings.create', [
            'providers' => $providers,
            'services' => $services,
            'branches' => $branches,
            'selectedProviderId' => $selectedProvider?->id,
            'selectedServiceId' => $selectedService?->id,
            'selectedBranchId' => $selectedBranchId,
            'selectedDate' => $selectedDate,
            'selectedService' => $selectedService,
            'availableSlots' => $availableSlots,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();

        $payload = $request->validate([
            'provider_id' => 'required|integer|exists:users,id',
            'service_id' => 'required|uuid|exists:services,id',
            'service_variant_id' => 'nullable|uuid|exists:service_variants,id',
            'slot_id' => 'required|uuid|exists:slots,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->bookingService->createBooking($customer, $payload);

        return redirect()
            ->route('customer.bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    public function rescheduleForm(Request $request, Booking $booking): RedirectResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();
        $this->ensureBookingOwner($booking, $customer);

        return redirect()
            ->route('customer.bookings.index')
            ->with('error', 'Only providers can reschedule appointments.');
    }

    public function reschedule(Request $request, Booking $booking): RedirectResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();
        $this->ensureBookingOwner($booking, $customer);

        return redirect()
            ->route('customer.bookings.index')
            ->with('error', 'Only providers can reschedule appointments.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse|JsonResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();
        $this->ensureBookingOwner($booking, $customer);

        $data = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $this->bookingService->cancelBooking($customer, $booking, $data['reason'] ?? null);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Booking cancelled successfully.',
            ]);
        }

        return redirect()
            ->route('customer.bookings.index')
            ->with('success', 'Booking cancelled successfully.');
    }

    private function ensureBookingOwner(Booking $booking, User $customer): void
    {
        if ((int) $booking->customer_id !== (int) $customer->id) {
            abort(403);
        }
    }
}
