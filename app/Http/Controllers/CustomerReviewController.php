<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerReviewController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $customer */
        $customer = $request->user();

        $feedbackQuery = Review::query()->where('customer_id', $customer->id);

        $completedBookings = Booking::query()
            ->with([
                'provider:id,name,email',
                'service:id,name,slug,base_price',
                'serviceVariant:id,name,price',
                'branch:id,name,city,state',
                'review:id,booking_id,rating,title,comment,is_approved,created_at,updated_at',
            ])
            ->where('customer_id', $customer->id)
            ->where('status', Booking::STATUS_COMPLETED)
            ->latest('scheduled_at')
            ->paginate(9);

        return view('customer.feedback.index', [
            'completedBookings' => $completedBookings,
            'feedbackStats' => [
                [
                    'label' => 'Completed bookings',
                    'value' => number_format($completedBookings->total()),
                    'hint' => 'Ready for rating',
                ],
                [
                    'label' => 'Feedback submitted',
                    'value' => number_format((clone $feedbackQuery)->count()),
                    'hint' => 'Across all services',
                ],
                [
                    'label' => 'Average rating given',
                    'value' => number_format((float) ((clone $feedbackQuery)->avg('rating') ?? 0), 1),
                    'hint' => 'Based on your reviews',
                ],
            ],
        ]);
    }

    public function edit(Request $request, Booking $booking): View|RedirectResponse
    {
        /** @var User $customer */
        $customer = $request->user();

        $this->ensureBookingOwner($booking, $customer);

        if (!$this->bookingCanReceiveFeedback($booking)) {
            return redirect()
                ->route('customer.feedback.index')
                ->with('error', 'Only completed bookings can receive feedback.');
        }

        $booking->load([
            'provider:id,name,email',
            'service:id,name,slug,description,base_price,duration_minutes',
            'serviceVariant:id,name,price',
            'branch:id,name,city,state',
            'review:id,booking_id,rating,title,comment,is_approved,created_at,updated_at',
        ]);

        return view('customer.feedback.edit', [
            'booking' => $booking,
            'review' => $booking->review,
            'selectedRating' => (int) old('rating', $booking->review?->rating ?? 0),
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        /** @var User $customer */
        $customer = $request->user();

        $this->ensureBookingOwner($booking, $customer);

        if (!$this->bookingCanReceiveFeedback($booking)) {
            return redirect()
                ->route('customer.feedback.index')
                ->with('error', 'Only completed bookings can receive feedback.');
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'comment' => 'nullable|string|max:1500',
        ]);

        $existingReview = $booking->review()->first();
        Review::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'customer_id' => $customer->id,
                'provider_id' => $booking->provider_id,
                'service_id' => $booking->service_id,
                'rating' => (int) $data['rating'],
                'title' => filled($data['title'] ?? null) ? trim((string) $data['title']) : null,
                'comment' => filled($data['comment'] ?? null) ? trim((string) $data['comment']) : null,
                'is_approved' => $existingReview?->is_approved ?? true,
            ]
        );

        return redirect()
            ->route('customer.feedback.index')
            ->with('success', $existingReview ? 'Feedback updated successfully.' : 'Feedback submitted successfully.');
    }

    private function ensureBookingOwner(Booking $booking, User $customer): void
    {
        if ((int) $booking->customer_id !== (int) $customer->id) {
            abort(403);
        }
    }

    private function bookingCanReceiveFeedback(Booking $booking): bool
    {
        return $booking->status === Booking::STATUS_COMPLETED;
    }
}
