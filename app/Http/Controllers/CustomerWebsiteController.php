<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\ScheduleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerWebsiteController extends Controller
{
    public function __construct(
        private readonly ScheduleAvailabilityService $availabilityService,
        private readonly BookingService $bookingService,
        private readonly PaymentService $paymentService
    ) {
    }

    public function home(Request $request): View
    {
        $categories = ServiceCategory::query()
            ->withCount('services')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'slug', 'description']);

        $featuredServices = Service::query()
            ->with([
                'category:id,name,slug',
                'branch:id,name,city,state',
                'providerProfile.user:id,name,profile_photo_path',
            ])
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->latest()
            ->limit(6)
            ->get();

        $featuredServices = $this->decorateServicesForUi($featuredServices);

        $testimonials = \App\Models\Review::query()
            ->with(['customer:id,name,profile_photo_path', 'service:id,name'])
            ->where('is_approved', true)
            ->latest()
            ->limit(6)
            ->get(['id', 'customer_id', 'service_id', 'rating', 'title', 'comment', 'created_at']);

        $locations = Branch::query()
            ->where('is_active', true)
            ->get(['city', 'state'])
            ->map(function (Branch $branch): string {
                return trim(implode(', ', array_filter([$branch->city, $branch->state])));
            })
            ->filter()
            ->unique()
            ->sort()
            ->take(12)
            ->values();

        return view('site.home', [
            'categories' => $categories,
            'featuredServices' => $featuredServices,
            'testimonials' => $testimonials,
            'locations' => $locations,
            'searchDate' => $request->query('date', now()->toDateString()),
        ]);
    }

    public function categories(Request $request): View
    {
        $validated = $this->validateCategoryFilters($request);
        $serviceScope = $validated['service_scope'] ?? 'any';
        $sort = $validated['sort'] ?? 'featured';

        $categoriesQuery = ServiceCategory::query()
            ->withCount(['services as active_services_count' => fn (Builder $query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->when(!empty($validated['q']), function (Builder $query) use ($validated): void {
                $search = trim((string) $validated['q']);

                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($serviceScope === 'with-services', function (Builder $query): void {
                $query->whereHas('services', fn (Builder $serviceQuery) => $serviceQuery->where('is_active', true));
            })
            ->when($serviceScope === 'three-plus', function (Builder $query): void {
                $query->whereHas('services', fn (Builder $serviceQuery) => $serviceQuery->where('is_active', true), '>=', 3);
            });

        match ($sort) {
            'name' => $categoriesQuery->orderBy('name'),
            'services' => $categoriesQuery->orderByDesc('active_services_count')->orderBy('name'),
            default => $categoriesQuery->orderBy('display_order')->orderBy('name'),
        };

        $categories = $categoriesQuery
            ->get(['id', 'name', 'slug', 'description', 'image_path', 'display_order'])
            ->map(function (ServiceCategory $category): ServiceCategory {
                $previewServices = Service::query()
                    ->with([
                        'category:id,name,slug',
                        'branch:id,name,city,state',
                        'providerProfile.user:id,name',
                    ])
                    ->withAvg('reviews as avg_rating', 'rating')
                    ->withCount('reviews')
                    ->where('is_active', true)
                    ->where('service_category_id', $category->id)
                    ->latest()
                    ->limit(3)
                    ->get();

                $category->setAttribute('preview_services', $this->decorateServicesForUi($previewServices));

                return $category;
            });

        return view('site.categories.index', [
            'categories' => $categories,
            'filters' => [
                'q' => $validated['q'] ?? '',
                'service_scope' => $serviceScope,
                'sort' => $sort,
            ],
            'serviceScopeOptions' => $this->categoryServiceScopeOptions(),
            'sortOptions' => $this->categorySortOptions(),
            'heroStats' => [
                [
                    'label' => 'Active Categories',
                    'value' => number_format(ServiceCategory::query()->where('is_active', true)->count()),
                ],
                [
                    'label' => 'Listed Services',
                    'value' => number_format(Service::query()->where('is_active', true)->count()),
                ],
                [
                    'label' => 'Visible Results',
                    'value' => number_format($categories->count()),
                ],
            ],
        ]);
    }

    public function services(Request $request): View
    {
        $validated = $this->validateServicesFilters($request);

        $availabilityDate = !empty($validated['availability'])
            ? Carbon::parse($validated['availability'])->startOfDay()
            : null;
        $sort = $validated['sort'] ?? 'recommended';

        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $services = $this->buildServicesQuery($validated, $availabilityDate)
            ->paginate(9)
            ->withQueryString();
        $services->setCollection(
            $this->decorateServicesForUi(collect($services->items()))
        );

        return view('site.services.index', [
            'categories' => $categories,
            'services' => $services,
            'resultCount' => $services->total(),
            'filters' => [
                'category' => $validated['category'] ?? '',
                'type' => $validated['type'] ?? '',
                'price_range' => $validated['price_range'] ?? '',
                'rating' => $validated['rating'] ?? '',
                'availability' => $availabilityDate?->toDateString() ?? '',
                'sort' => $sort,
            ],
            'serviceTypeOptions' => $this->serviceTypeOptions(),
            'priceRangeOptions' => $this->priceRangeOptions(),
            'ratingOptions' => [
                5 => '5.0 only',
                4 => '4.0 and above',
                3 => '3.0 and above',
            ],
            'sortOptions' => $this->sortOptions(),
            'heroStats' => [
                [
                    'label' => 'Active Services',
                    'value' => number_format(Service::query()->where('is_active', true)->count()),
                ],
                [
                    'label' => 'Trusted Providers',
                    'value' => number_format(
                        User::query()
                            ->where('role', User::ROLE_PROVIDER)
                            ->where('is_active', true)
                            ->whereHas('providerProfile', fn (Builder $query) => $query->where('status', 'active'))
                            ->count()
                    ),
                ],
                [
                    'label' => 'Categories',
                    'value' => number_format($categories->count()),
                ],
            ],
        ]);
    }

    public function servicesData(Request $request): JsonResponse
    {
        $validated = $this->validateServicesFilters($request);

        $availabilityDate = !empty($validated['availability'])
            ? Carbon::parse($validated['availability'])->startOfDay()
            : null;

        $services = $this->buildServicesQuery($validated, $availabilityDate)->get();
        $services = $this->decorateServicesForUi($services);

        return response()->json([
            'data' => $services->map(fn (Service $service): array => $this->transformServiceForListing($service)),
        ]);
    }

    public function serviceDetail(Request $request, string $slug): View
    {
        $service = Service::query()
            ->with([
                'category:id,name,slug',
                'branch:id,name,address_line_1,address_line_2,city,state,country',
                'providerProfile.user:id,name,email,profile_photo_path',
                'variants' => fn ($query) => $query->where('is_active', true)->orderBy('price'),
                'reviews' => fn ($query) => $query->where('is_approved', true)->latest()->limit(6),
                'reviews.customer:id,name,profile_photo_path',
            ])
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $selectedDate = Carbon::parse((string) $request->query('date', now()->addDay()->toDateString()))->startOfDay();
        $selectedBranchId = $request->query('branch_id', $service->branch_id ?: $service->providerProfile?->branch_id);
        $branches = Branch::query()
            ->where('is_active', true)
            ->whereIn('id', collect([$service->branch_id, $service->providerProfile?->branch_id])->filter()->unique())
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'state']);

        /** @var User|null $provider */
        $provider = $service->providerProfile?->user;
        $availableSlots = collect();
        $calendarDays = collect();
        $serviceIsUnavailable = true;
        $serviceAvailabilityMessage = 'This service is currently not available for booking. Please check back again later.';

        if ($provider) {
            $availableSlots = $this->availabilityService->generateAvailableSlotsForDate(
                $provider,
                $selectedDate,
                $selectedBranchId ? (string) $selectedBranchId : null,
                $service
            );

            $calendarDays = $this->buildServiceCalendarDays(
                $provider,
                $service,
                $selectedBranchId ? (string) $selectedBranchId : null,
                $selectedDate
            );
            $serviceIsUnavailable = !$this->serviceHasUpcomingAvailability(
                $provider,
                $service,
                $branches->pluck('id')
            );
            $serviceAvailabilityMessage = $serviceIsUnavailable
                ? 'This service does not have any upcoming booking slots right now. Please check again later.'
                : '';
        } else {
            $serviceAvailabilityMessage = 'This service is currently not available because the provider cannot accept bookings right now.';
        }

        $gallery = $this->generateServiceGallery($service);

        return view('site.services.show', [
            'service' => $service,
            'gallery' => $gallery,
            'branches' => $branches,
            'selectedDate' => $selectedDate,
            'selectedBranchId' => $selectedBranchId,
            'availableSlots' => $availableSlots,
            'calendarDays' => $calendarDays,
            'availabilityUrl' => route('site.services.availability', $service->slug),
            'serviceIsUnavailable' => $serviceIsUnavailable,
            'serviceAvailabilityMessage' => $serviceAvailabilityMessage,
        ]);
    }

    public function availability(Request $request, string $slug): JsonResponse
    {
        $data = $request->validate([
            'date' => 'required|date',
            'branch_id' => 'nullable|uuid|exists:branches,id',
        ]);

        $service = Service::query()
            ->with('providerProfile.user:id,name')
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        /** @var User|null $provider */
        $provider = $service->providerProfile?->user;
        if (!$provider) {
            return response()->json([
                'message' => 'Provider is not available for this service.',
                'data' => [
                    'selected_date' => Carbon::parse($data['date'])->toDateString(),
                    'slots' => [],
                    'calendar_days' => [],
                ],
            ], 422);
        }

        $selectedDate = Carbon::parse($data['date'])->startOfDay();
        $branchId = $data['branch_id'] ?? null;

        $slots = $this->availabilityService->generateAvailableSlotsForDate(
            $provider,
            $selectedDate,
            $branchId,
            $service
        );

        $calendarDays = $this->buildServiceCalendarDays($provider, $service, $branchId, $selectedDate)
            ->map(fn (array $day): array => [
                'date' => $day['date']->toDateString(),
                'label' => $day['label'],
                'slot_count' => $day['slot_count'],
                'is_selected' => $day['is_selected'],
            ]);

        return response()->json([
            'data' => [
                'selected_date' => $selectedDate->toDateString(),
                'slots' => $slots->values(),
                'calendar_days' => $calendarDays->values(),
            ],
        ]);
    }

    public function dashboard(Request $request): View
    {
        /** @var User $customer */
        $customer = $request->user();

        $upcomingStatuses = [
            Booking::STATUS_PENDING,
            Booking::STATUS_ACCEPTED,
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_IN_PROGRESS,
        ];

        $closedStatuses = [
            Booking::STATUS_COMPLETED,
            Booking::STATUS_CANCELLED,
            Booking::STATUS_REJECTED,
        ];

        $bookingRelations = [
            'provider:id,name,email',
            'service:id,name,slug,base_price',
            'serviceVariant:id,name,price',
            'branch:id,name,city,state',
            'payments:id,booking_id,status',
        ];

        $bookingsBase = Booking::query()->where('customer_id', $customer->id);
        $paymentsBase = Payment::query()->where('customer_id', $customer->id);
        $paidStatuses = [Payment::STATUS_PAID, Payment::STATUS_REFUNDED];

        $totalBookings = (clone $bookingsBase)->count();
        $upcomingBookingsCount = (clone $bookingsBase)
            ->whereIn('status', $upcomingStatuses)
            ->where('scheduled_at', '>=', now())
            ->count();
        $completedBookingsCount = (clone $bookingsBase)
            ->where('status', Booking::STATUS_COMPLETED)
            ->count();
        $cancelledBookingsCount = (clone $bookingsBase)
            ->where('status', Booking::STATUS_CANCELLED)
            ->count();
        $favoritesCount = collect(session('site.favorites', []))->filter()->unique()->count();
        $pendingPaymentsCount = (clone $paymentsBase)
            ->where('status', Payment::STATUS_PENDING)
            ->count();
        $grossPaid = (clone $paymentsBase)
            ->whereIn('status', $paidStatuses)
            ->sum('amount');
        $refundedAmount = (clone $paymentsBase)
            ->whereIn('status', $paidStatuses)
            ->sum('refunded_amount');
        $netSpent = max(0, (float) $grossPaid - (float) $refundedAmount);

        $upcomingBookings = Booking::query()
            ->with($bookingRelations)
            ->where('customer_id', $customer->id)
            ->whereIn('status', $upcomingStatuses)
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(4)
            ->get();

        $bookingHistory = Booking::query()
            ->with($bookingRelations)
            ->where('customer_id', $customer->id)
            ->where(function (Builder $query) use ($closedStatuses): void {
                $query
                    ->whereIn('status', $closedStatuses)
                    ->orWhere('scheduled_at', '<', now());
            })
            ->latest('scheduled_at')
            ->limit(8)
            ->get();

        $recentPayments = Payment::query()
            ->with([
                'booking:id,booking_number,scheduled_at,service_id',
                'booking.service:id,name',
            ])
            ->where('customer_id', $customer->id)
            ->latest()
            ->limit(5)
            ->get();

        $paymentHistory = Payment::query()
            ->with([
                'booking:id,booking_number,scheduled_at,status,service_id',
                'booking.service:id,name',
                'provider:id,name,email',
            ])
            ->where('customer_id', $customer->id)
            ->latest()
            ->limit(12)
            ->get()
            ->map(function (Payment $payment): Payment {
                $payment->setAttribute('can_refund', $this->paymentService->canRefund($payment));

                return $payment;
            });

        $hydrateBookingMeta = function (Collection $bookings) use ($upcomingStatuses): Collection {
            return $bookings->map(function (Booking $booking) use ($upcomingStatuses): Booking {
                $hasPaidPayment = $booking->payments->contains(function ($payment) {
                    return in_array($payment->status, [
                        Payment::STATUS_PAID,
                        Payment::STATUS_REFUNDED,
                    ], true);
                });

                $booking->setAttribute('can_reschedule', $this->bookingService->canReschedule($booking));
                $booking->setAttribute('can_cancel', $this->bookingService->canCancel($booking));
                $booking->setAttribute(
                    'can_pay',
                    !$hasPaidPayment && in_array($booking->status, [
                        Booking::STATUS_PENDING,
                        Booking::STATUS_ACCEPTED,
                    ], true)
                );
                $booking->setAttribute(
                    'location_label',
                    collect([
                        $booking->branch?->name,
                        trim(implode(', ', array_filter([$booking->branch?->city, $booking->branch?->state]))),
                    ])->filter()->implode(' - ')
                );
                $booking->setAttribute('status_label', Str::headline((string) $booking->status));
                $booking->setAttribute('is_upcoming', in_array($booking->status, $upcomingStatuses, true) && $booking->scheduled_at?->isFuture());
                $booking->setAttribute('book_again_url', route('site.booking', array_filter([
                    'provider_id' => $booking->provider_id,
                    'service_id' => $booking->service_id,
                    'branch_id' => $booking->branch_id,
                ])));

                return $booking;
            });
        };

        $upcomingBookings = $hydrateBookingMeta($upcomingBookings);
        $bookingHistory = $hydrateBookingMeta($bookingHistory);

        $payableBookings = $upcomingBookings
            ->filter(fn (Booking $booking): bool => (bool) $booking->getAttribute('can_pay'))
            ->values();

        $selectedPaymentBookingId = (string) $request->query('pay_booking', '');
        $selectedPaymentBooking = $payableBookings->firstWhere('id', $selectedPaymentBookingId) ?? $payableBookings->first();

        if ($selectedPaymentBooking) {
            $selectedPaymentBooking->load([
                'provider:id,name,email',
                'service:id,name,base_price',
                'serviceVariant:id,name,price',
                'payments' => fn ($query) => $query->latest(),
            ]);

            $selectedPaymentBooking->setAttribute(
                'payment_amount',
                number_format((float) ($selectedPaymentBooking->serviceVariant?->price ?? $selectedPaymentBooking->service?->base_price ?? 0), 2, '.', '')
            );
        }

        $profileCompletion = (int) round(
            collect([
                $customer->name,
                $customer->email,
            ])->filter(fn ($value) => filled($value))->count() / 2 * 100
        );

        $nextBooking = $upcomingBookings->first();

        return view('site.dashboard', [
            'customer' => $customer,
            'nextBooking' => $nextBooking,
            'upcomingBookings' => $upcomingBookings,
            'bookingHistory' => $bookingHistory,
            'recentPayments' => $recentPayments,
            'paymentHistory' => $paymentHistory,
            'payableBookings' => $payableBookings,
            'selectedPaymentBooking' => $selectedPaymentBooking,
            'profileCompletion' => $profileCompletion,
            'paymentCurrency' => config('payment.currency', 'INR'),
            'dashboardStats' => [
                [
                    'label' => 'Total bookings',
                    'value' => number_format($totalBookings),
                    'hint' => 'Across all services',
                ],
                [
                    'label' => 'Upcoming',
                    'value' => number_format($upcomingBookingsCount),
                    'hint' => 'Scheduled sessions ahead',
                ],
                [
                    'label' => 'Completed',
                    'value' => number_format($completedBookingsCount),
                    'hint' => 'Finished appointments',
                ],
                [
                    'label' => 'Net spent',
                    'value' => 'Rs. '.number_format($netSpent, 0),
                    'hint' => 'After refunds',
                ],
            ],
            'accountHighlights' => [
                [
                    'label' => 'Saved favorites',
                    'value' => number_format($favoritesCount),
                ],
                [
                    'label' => 'Pending payments',
                    'value' => number_format($pendingPaymentsCount),
                ],
                [
                    'label' => 'Cancelled bookings',
                    'value' => number_format($cancelledBookingsCount),
                ],
            ],
        ]);
    }

    public function dashboardBookingsData(Request $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();

        $bookings = Booking::query()
            ->with([
                'provider:id,name',
                'service:id,name,slug',
                'serviceVariant:id,name',
                'branch:id,name,city,state',
                'payments:id,booking_id,status',
            ])
            ->where('customer_id', $customer->id)
            ->latest('scheduled_at')
            ->get();

        $data = $bookings->map(function (Booking $booking): array {
            $hasPaidPayment = $booking->payments->contains(function ($payment) {
                return in_array($payment->status, [
                    \App\Models\Payment::STATUS_PAID,
                    \App\Models\Payment::STATUS_REFUNDED,
                ], true);
            });

            $canReschedule = $this->bookingService->canReschedule($booking);
            $canCancel = $this->bookingService->canCancel($booking);
            $canPay = !$hasPaidPayment && in_array($booking->status, [
                Booking::STATUS_PENDING,
                Booking::STATUS_ACCEPTED,
            ], true);

            return [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'service' => $booking->service?->name ?? 'N/A',
                'variant' => $booking->serviceVariant?->name,
                'provider' => $booking->provider?->name ?? 'N/A',
                'scheduled_at' => $booking->scheduled_at?->format('d M Y, h:i A') ?? '-',
                'scheduled_at_timestamp' => $booking->scheduled_at?->timestamp ?? 0,
                'location' => $booking->branch?->name ?? 'N/A',
                'status' => $booking->status,
                'status_label' => ucfirst($booking->status),
                'can_reschedule' => $canReschedule,
                'can_cancel' => $canCancel,
                'can_pay' => $canPay,
                'reschedule_url' => route('customer.bookings.reschedule.form', $booking),
                'cancel_url' => route('customer.bookings.cancel', $booking),
                'checkout_url' => $canPay ? route('customer.dashboard', ['pay_booking' => $booking->id]).'#payments-center' : null,
            ];
        });

        return response()->json(['data' => $data]);
    }

    private function validateServicesFilters(Request $request): array
    {
        return $request->validate([
            'q' => 'nullable|string|max:120',
            'category' => 'nullable|string',
            'type' => 'nullable|in:1-on-1,group',
            'location' => 'nullable|string|max:80',
            'price_range' => 'nullable|in:under-500,500-800,800-1000,1000-plus',
            'rating' => 'nullable|integer|min:1|max:5',
            'availability' => 'nullable|date',
            'sort' => 'nullable|in:recommended,price_low,price_high,rating,newest',
        ]);
    }

    private function validateCategoryFilters(Request $request): array
    {
        return $request->validate([
            'q' => 'nullable|string|max:80',
            'service_scope' => 'nullable|in:any,with-services,three-plus',
            'sort' => 'nullable|in:featured,name,services',
        ]);
    }

    private function categoryServiceScopeOptions(): array
    {
        return [
            'any' => 'Any category',
            'with-services' => 'With services only',
            'three-plus' => '3+ services',
        ];
    }

    private function categorySortOptions(): array
    {
        return [
            'featured' => 'Featured first',
            'name' => 'Name A-Z',
            'services' => 'Most services',
        ];
    }

    private function serviceTypeOptions(): array
    {
        return [
            '1-on-1' => '1-on-1',
            'group' => 'Group',
        ];
    }

    private function priceRangeOptions(): array
    {
        return [
            'under-500' => 'Under Rs. 500',
            '500-800' => 'Rs. 500 - Rs. 800',
            '800-1000' => 'Rs. 800 - Rs. 1,000',
            '1000-plus' => 'Rs. 1,000+',
        ];
    }

    private function sortOptions(): array
    {
        return [
            'recommended' => 'Recommended',
            'rating' => 'Top Rated',
            'price_low' => 'Price: Low to High',
            'price_high' => 'Price: High to Low',
            'newest' => 'Newest',
        ];
    }

    private function resolvePriceRangeBounds(?string $priceRange): array
    {
        return match ($priceRange) {
            'under-500' => [null, 500],
            '500-800' => [500, 800],
            '800-1000' => [800, 1000],
            '1000-plus' => [1000, null],
            default => [null, null],
        };
    }

    private function buildServiceCalendarDays(
        User $provider,
        Service $service,
        ?string $branchId,
        Carbon $selectedDate,
        int $days = 14
    ): Collection {
        return collect(range(0, $days - 1))->map(function (int $offset) use ($provider, $service, $branchId, $selectedDate): array {
            $date = now()->addDays($offset)->startOfDay();
            $slots = $this->availabilityService->generateAvailableSlotsForDate(
                $provider,
                $date,
                $branchId,
                $service
            );

            return [
                'date' => $date,
                'label' => $date->format('d M'),
                'slot_count' => $slots->count(),
                'is_selected' => $date->isSameDay($selectedDate),
            ];
        });
    }

    private function serviceHasUpcomingAvailability(
        User $provider,
        Service $service,
        Collection $branchIds,
        int $days = 14
    ): bool {
        $branchIds = $branchIds->filter()->values();
        $branchOptions = $branchIds->isNotEmpty() ? $branchIds->all() : [null];

        foreach ($branchOptions as $branchId) {
            foreach (range(0, $days - 1) as $offset) {
                $date = now()->addDays($offset)->startOfDay();

                if ($this->availabilityService->generateAvailableSlotsForDate($provider, $date, $branchId, $service)->isNotEmpty()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function transformServiceForListing(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'type' => $service->type === 'group' ? 'Group' : '1-on-1',
            'category' => $service->category?->name ?? 'Service',
            'provider' => $service->providerProfile?->user?->name ?? 'Provider',
            'location' => $service->branch?->city
                ? trim(($service->branch->city ?? '').', '.($service->branch->state ?? ''))
                : 'Multiple locations',
            'price' => number_format((float) ($service->base_price ?? 0), 2),
            'price_value' => (float) ($service->base_price ?? 0),
            'duration' => (int) ($service->duration_minutes ?? 0),
            'rating' => (float) ($service->avg_rating ?? 0),
            'rating_label' => (float) ($service->avg_rating ?? 0) > 0 ? number_format((float) $service->avg_rating, 1) : 'New',
            'reviews_count' => (int) ($service->reviews_count ?? 0),
            'description' => Str::limit((string) ($service->description ?? ''), 110),
            'image' => $service->ui_image,
            'details_url' => route('site.services.show', $service->slug),
            'book_url' => route('site.booking', array_filter([
                'provider_id' => $service->providerProfile?->user_id,
                'service_id' => $service->id,
                'branch_id' => $service->branch_id,
            ])),
        ];
    }

    private function decorateServicesForUi(Collection $services): Collection
    {
        return $services->map(function (Service $service): Service {
            $gallery = $this->generateServiceGallery($service);
            $service->setAttribute('ui_image', $gallery[0] ?? null);
            $service->setAttribute('ui_gallery', $gallery);
            $service->setAttribute('avg_rating', round((float) ($service->avg_rating ?? 0), 1));

            return $service;
        });
    }

    private function generateServiceGallery(Service $service): array
    {
        $seed = md5($service->id.'|'.$service->name);
        $fallbackImages = collect(range(0, 3))
            ->map(function (int $index) use ($seed): string {
                $segment = substr($seed, $index * 8, 8);
                return 'https://picsum.photos/seed/'.$segment.'/1200/800';
            })
            ->all();

        $images = [];
        if ($service->image_url) {
            $images[] = $service->image_url;
        }

        return array_values(array_unique(array_merge($images, $fallbackImages)));
    }

    private function buildServicesQuery(array $validated, ?Carbon $availabilityDate): Builder
    {
        [$priceMin, $priceMax] = $this->resolvePriceRangeBounds($validated['price_range'] ?? null);

        $servicesQuery = Service::query()
            ->with([
                'category:id,name,slug',
                'branch:id,name,city,state',
                'providerProfile.user:id,name,profile_photo_path',
            ])
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->when(!empty($validated['q']), function (Builder $query) use ($validated): void {
                $search = trim((string) $validated['q']);

                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('providerProfile.user', function (Builder $providerQuery) use ($search): void {
                            $providerQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(!empty($validated['category']), function (Builder $query) use ($validated): void {
                $query->whereHas('category', function (Builder $categoryQuery) use ($validated): void {
                    $categoryQuery
                        ->where('slug', $validated['category'])
                        ->orWhere('id', $validated['category']);
                });
            })
            ->when(!empty($validated['type']), fn (Builder $query) => $query->where('type', $validated['type']))
            ->when(!empty($validated['location']), function (Builder $query) use ($validated): void {
                $location = trim((string) $validated['location']);

                $query->whereHas('branch', function (Builder $branchQuery) use ($location): void {
                    $branchQuery
                        ->where('city', 'like', "%{$location}%")
                        ->orWhere('state', 'like', "%{$location}%");
                });
            })
            ->when($priceMin !== null, fn (Builder $query) => $query->where('base_price', '>=', $priceMin))
            ->when($priceMax !== null, fn (Builder $query) => $query->where('base_price', '<=', $priceMax))
            ->when(!empty($validated['rating']), function (Builder $query) use ($validated): void {
                $rating = (int) $validated['rating'];
                $query->whereRaw(
                    '(SELECT COALESCE(AVG(reviews.rating), 0) FROM reviews WHERE reviews.service_id = services.id AND reviews.is_approved = 1) >= ?',
                    [$rating]
                );
            })
            ->when($availabilityDate, function (Builder $query) use ($availabilityDate): void {
                $dayOfWeek = (int) $availabilityDate->dayOfWeek;

                $query->whereHas('providerProfile.user.schedules', function (Builder $scheduleQuery) use ($dayOfWeek): void {
                    $scheduleQuery
                        ->where('day_of_week', $dayOfWeek)
                        ->where('is_active', true);
                });
            });

        $sort = $validated['sort'] ?? 'recommended';
        match ($sort) {
            'price_low' => $servicesQuery->orderBy('base_price'),
            'price_high' => $servicesQuery->orderByDesc('base_price'),
            'rating' => $servicesQuery->orderByDesc('avg_rating')->orderByDesc('reviews_count'),
            'newest' => $servicesQuery->latest(),
            default => $servicesQuery->orderByDesc('reviews_count')->orderByDesc('avg_rating'),
        };

        return $servicesQuery;
    }
}
