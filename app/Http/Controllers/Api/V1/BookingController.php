<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    public function store(Request $request): JsonResponse
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

        $booking = $this->bookingService->createBooking($customer, $payload);

        return response()->json([
            'message' => 'Booking created successfully.',
            'data' => $this->transformBooking($booking),
        ], 201);
    }

    public function reschedule(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();

        $data = $request->validate([
            'slot_id' => 'required|uuid|exists:slots,id',
        ]);

        $booking = Booking::query()
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        $booking = $this->bookingService->rescheduleBooking($customer, $booking, $data['slot_id']);

        return response()->json([
            'message' => 'Booking rescheduled successfully.',
            'data' => $this->transformBooking($booking),
        ]);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $customer */
        $customer = $request->user();

        $data = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $booking = Booking::query()
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        $booking = $this->bookingService->cancelBooking($customer, $booking, $data['reason'] ?? null);

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'data' => $this->transformBooking($booking),
        ]);
    }

    private function transformBooking(Booking $booking): array
    {
        $booking->loadMissing([
            'provider:id,name,email',
            'service:id,name',
            'serviceVariant:id,name',
            'slot:id,start_at,end_at',
        ]);

        return [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'customer_id' => $booking->customer_id,
            'provider_id' => $booking->provider_id,
            'branch_id' => $booking->branch_id,
            'service_id' => $booking->service_id,
            'service_variant_id' => $booking->service_variant_id,
            'slot_id' => $booking->slot_id,
            'scheduled_at' => $booking->scheduled_at,
            'status' => $booking->status,
            'notes' => $booking->notes,
            'cancelled_at' => $booking->cancelled_at,
            'provider' => $booking->provider ? [
                'id' => $booking->provider->id,
                'name' => $booking->provider->name,
                'email' => $booking->provider->email,
            ] : null,
            'service' => $booking->service ? [
                'id' => $booking->service->id,
                'name' => $booking->service->name,
            ] : null,
            'variant' => $booking->serviceVariant ? [
                'id' => $booking->serviceVariant->id,
                'name' => $booking->serviceVariant->name,
            ] : null,
            'slot' => $booking->slot ? [
                'id' => $booking->slot->id,
                'start_at' => $booking->slot->start_at,
                'end_at' => $booking->slot->end_at,
            ] : null,
            'created_at' => $booking->created_at,
            'updated_at' => $booking->updated_at,
        ];
    }
}
