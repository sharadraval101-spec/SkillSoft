<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceVariant;
use App\Models\Slot;
use App\Models\User;
use App\Services\ScheduleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoBookingFlowSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'Password@123';

    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(RolePermissionSeeder::class);

        $categories = $this->seedCategories();
        $branches = $this->seedBranches();
        $providers = $this->seedProviders($branches);
        $customers = $this->seedCustomers();
        $services = $this->seedServices($categories, $branches, $providers);

        $this->seedVariants($services);
        $this->seedSchedules($providers, $branches);
        $this->seedUpcomingBookings($providers, $customers, $services);
        $this->seedCompletedBookingsAndReviews($providers, $customers, $services);

        $this->command?->info('Demo booking data seeded successfully.');
        $this->command?->line('Customer test accounts:');
        $this->command?->line(' - demo.customer1@example.com / '.self::DEMO_PASSWORD);
        $this->command?->line(' - demo.customer2@example.com / '.self::DEMO_PASSWORD);
        $this->command?->line(' - demo.customer3@example.com / '.self::DEMO_PASSWORD);
    }

    private function seedCategories(): array
    {
        $definitions = [
            'beauty' => [
                'name' => 'Beauty & Salon',
                'slug' => 'beauty-salon',
                'description' => 'Haircuts, spa treatments, makeup artists, skincare, and professional beauty services near you.',
                'display_order' => 1,
            ],
            'fitness' => [
                'name' => 'Fitness & Yoga',
                'slug' => 'fitness-yoga',
                'description' => 'Find personal trainers, yoga instructors, gym sessions, and wellness coaching to help you stay fit and healthy.',
                'display_order' => 2,
            ],
            'healthcare' => [
                'name' => 'Healthcare',
                'slug' => 'healthcare',
                'description' => 'Connect with doctors, therapists, and healthcare experts for trusted medical advice and wellness support.',
                'display_order' => 3,
            ],
            'pet' => [
                'name' => 'Pet Services',
                'slug' => 'pet-services',
                'description' => 'Find trusted pet grooming, veterinary care, boarding, walking, and training services for your pets.',
                'display_order' => 4,
            ],
            'legal' => [
                'name' => 'Legal Consultation',
                'slug' => 'legal-consultation',
                'description' => 'Get reliable legal consultation for documentation, contracts, family matters, and professional advice.',
                'display_order' => 5,
            ],
        ];

        $categories = [];

        foreach ($definitions as $key => $definition) {
            $categories[$key] = ServiceCategory::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'display_order' => $definition['display_order'],
                    'status' => 'active',
                    'is_active' => true,
                ]
            );
        }

        return $categories;
    }

    private function seedBranches(): array
    {
        $definitions = [
            'beauty' => [
                'name' => 'Glamour Salon Studio',
                'slug' => 'demo-glamour-salon-studio',
                'email' => 'glamour.branch@example.com',
                'phone' => '9001001001',
                'address_line_1' => '14 Linking Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400050',
            ],
            'fitness' => [
                'name' => 'FitLife Wellness Hub',
                'slug' => 'demo-fitlife-wellness-hub',
                'email' => 'fitlife.branch@example.com',
                'phone' => '9001001002',
                'address_line_1' => '28 SV Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400058',
            ],
            'healthcare' => [
                'name' => 'CareWell Clinic',
                'slug' => 'demo-carewell-clinic',
                'email' => 'carewell.branch@example.com',
                'phone' => '9001001003',
                'address_line_1' => '5 Hill Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400050',
            ],
            'pet' => [
                'name' => 'Happy Paws Care Center',
                'slug' => 'demo-happy-paws-care-center',
                'email' => 'happypaws.branch@example.com',
                'phone' => '9001001004',
                'address_line_1' => '22 Turner Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400050',
            ],
            'legal' => [
                'name' => 'Sharma Legal Office',
                'slug' => 'demo-sharma-legal-office',
                'email' => 'sharma.branch@example.com',
                'phone' => '9001001005',
                'address_line_1' => '8 Fort Chambers',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400001',
            ],
        ];

        $branches = [];

        foreach ($definitions as $key => $definition) {
            $branches[$key] = Branch::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'email' => $definition['email'],
                    'phone' => $definition['phone'],
                    'address_line_1' => $definition['address_line_1'],
                    'city' => $definition['city'],
                    'state' => $definition['state'],
                    'postal_code' => $definition['postal_code'],
                    'country' => 'IN',
                    'is_active' => true,
                ]
            );
        }

        return $branches;
    }

    private function seedProviders(array $branches): array
    {
        $definitions = [
            'beauty' => [
                'name' => 'Glamour Salon',
                'email' => 'demo.provider.beauty@example.com',
                'business_name' => 'Glamour Salon',
                'bio' => 'Modern salon professionals focused on styling, grooming, and premium customer care.',
                'experience_years' => 8,
                'branch_key' => 'beauty',
            ],
            'fitness' => [
                'name' => 'FitLife Studio',
                'email' => 'demo.provider.fitness@example.com',
                'business_name' => 'FitLife Studio',
                'bio' => 'Private and small-group yoga sessions designed for flexibility, mobility, and stress relief.',
                'experience_years' => 6,
                'branch_key' => 'fitness',
            ],
            'healthcare' => [
                'name' => 'CareWell Clinic',
                'email' => 'demo.provider.healthcare@example.com',
                'business_name' => 'CareWell Clinic',
                'bio' => 'Compassionate healthcare guidance with a focus on routine care and ongoing support.',
                'experience_years' => 10,
                'branch_key' => 'healthcare',
            ],
            'pet' => [
                'name' => 'Happy Paws Care',
                'email' => 'demo.provider.pet@example.com',
                'business_name' => 'Happy Paws Care',
                'bio' => 'Pet grooming and care services delivered with safety, comfort, and friendly handling.',
                'experience_years' => 7,
                'branch_key' => 'pet',
            ],
            'legal' => [
                'name' => 'Sharma Legal Advisors',
                'email' => 'demo.provider.legal@example.com',
                'business_name' => 'Sharma Legal Advisors',
                'bio' => 'Professional consultations for contracts, disputes, legal documents, and personal guidance.',
                'experience_years' => 12,
                'branch_key' => 'legal',
            ],
        ];

        $providers = [];

        foreach ($definitions as $key => $definition) {
            $user = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => Hash::make(self::DEMO_PASSWORD),
                    'role' => User::ROLE_PROVIDER,
                    'is_active' => true,
                ]
            );

            $user->syncRoleFromLegacyValue();

            $providers[$key] = [
                'user' => $user,
                'profile' => ProviderProfile::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'branch_id' => $branches[$definition['branch_key']]->id,
                        'business_name' => $definition['business_name'],
                        'bio' => $definition['bio'],
                        'experience_years' => $definition['experience_years'],
                        'commission_rate' => 12.50,
                        'status' => 'active',
                        'verified_at' => now()->subDays(45),
                    ]
                ),
            ];
        }

        return $providers;
    }

    private function seedCustomers(): array
    {
        $definitions = [
            'customer_1' => [
                'name' => 'Rita Sharma',
                'email' => 'demo.customer1@example.com',
            ],
            'customer_2' => [
                'name' => 'Aman Verma',
                'email' => 'demo.customer2@example.com',
            ],
            'customer_3' => [
                'name' => 'Meera Nair',
                'email' => 'demo.customer3@example.com',
            ],
        ];

        $customers = [];

        foreach ($definitions as $key => $definition) {
            $customer = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => Hash::make(self::DEMO_PASSWORD),
                    'role' => User::ROLE_CUSTOMER,
                    'is_active' => true,
                ]
            );

            $customer->syncRoleFromLegacyValue();
            $customers[$key] = $customer;
        }

        return $customers;
    }

    private function seedServices(array $categories, array $branches, array $providers): array
    {
        $definitions = [
            'haircut' => [
                'name' => 'Haircut & Styling',
                'slug' => 'demo-haircut-styling',
                'category_key' => 'beauty',
                'branch_key' => 'beauty',
                'provider_key' => 'beauty',
                'description' => 'Precision haircut, wash, and styling session tailored to your preferred look.',
                'duration_minutes' => 45,
                'base_price' => 499,
                'type' => '1-on-1',
                'max_customers' => null,
                'created_at' => now()->subDays(1),
            ],
            'yoga' => [
                'name' => 'Personal Yoga Session',
                'slug' => 'demo-personal-yoga-session',
                'category_key' => 'fitness',
                'branch_key' => 'fitness',
                'provider_key' => 'fitness',
                'description' => 'Guided yoga session for flexibility, balance, posture correction, and mindful breathing.',
                'duration_minutes' => 60,
                'base_price' => 699,
                'type' => '1-on-1',
                'max_customers' => null,
                'created_at' => now()->subDays(2),
            ],
            'pet_grooming' => [
                'name' => 'Pet Grooming',
                'slug' => 'demo-pet-grooming',
                'category_key' => 'pet',
                'branch_key' => 'pet',
                'provider_key' => 'pet',
                'description' => 'Full grooming session including brushing, trimming, bath, and hygiene care for pets.',
                'duration_minutes' => 60,
                'base_price' => 499,
                'type' => '1-on-1',
                'max_customers' => null,
                'created_at' => now()->subDays(3),
            ],
            'legal' => [
                'name' => 'Legal Consultation',
                'slug' => 'demo-legal-consultation',
                'category_key' => 'legal',
                'branch_key' => 'legal',
                'provider_key' => 'legal',
                'description' => 'One-on-one consultation for contracts, legal notices, documentation, and case planning.',
                'duration_minutes' => 60,
                'base_price' => 899,
                'type' => '1-on-1',
                'max_customers' => null,
                'created_at' => now()->subDays(4),
            ],
            'healthcare' => [
                'name' => 'Home Wellness Consultation',
                'slug' => 'demo-home-wellness-consultation',
                'category_key' => 'healthcare',
                'branch_key' => 'healthcare',
                'provider_key' => 'healthcare',
                'description' => 'Routine wellness guidance, follow-up consultation, and practical care recommendations.',
                'duration_minutes' => 45,
                'base_price' => 799,
                'type' => '1-on-1',
                'max_customers' => null,
                'created_at' => now()->subDays(5),
            ],
        ];

        $services = [];

        foreach ($definitions as $key => $definition) {
            $service = Service::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'provider_profile_id' => $providers[$definition['provider_key']]['profile']->id,
                    'service_category_id' => $categories[$definition['category_key']]->id,
                    'branch_id' => $branches[$definition['branch_key']]->id,
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'duration_minutes' => $definition['duration_minutes'],
                    'base_price' => $definition['base_price'],
                    'type' => $definition['type'],
                    'max_customers' => $definition['max_customers'],
                    'status' => 'active',
                    'is_active' => true,
                ]
            );

            $service->forceFill([
                'created_at' => $definition['created_at'],
                'updated_at' => now(),
            ])->saveQuietly();

            $services[$key] = $service->fresh();
        }

        return $services;
    }

    private function seedVariants(array $services): void
    {
        $definitions = [
            [
                'service_key' => 'haircut',
                'name' => 'Premium Styling',
                'sku' => 'DEMO-HAIRCUT-PREMIUM',
                'duration_minutes' => 75,
                'price' => 699,
            ],
            [
                'service_key' => 'haircut',
                'name' => 'Deluxe Styling + Spa',
                'sku' => 'DEMO-HAIRCUT-DELUXE',
                'duration_minutes' => 90,
                'price' => 899,
            ],
            [
                'service_key' => 'yoga',
                'name' => 'Extended 90 Min Session',
                'sku' => 'DEMO-YOGA-EXTENDED',
                'duration_minutes' => 90,
                'price' => 999,
            ],
        ];

        foreach ($definitions as $definition) {
            ServiceVariant::query()->updateOrCreate(
                ['sku' => $definition['sku']],
                [
                    'service_id' => $services[$definition['service_key']]->id,
                    'name' => $definition['name'],
                    'duration_minutes' => $definition['duration_minutes'],
                    'price' => $definition['price'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedSchedules(array $providers, array $branches): void
    {
        $definitions = [
            'beauty' => ['start' => '10:00:00', 'end' => '19:00:00', 'buffer' => 15],
            'fitness' => ['start' => '07:00:00', 'end' => '14:00:00', 'buffer' => 15],
            'healthcare' => ['start' => '09:00:00', 'end' => '17:00:00', 'buffer' => 10],
            'pet' => ['start' => '10:00:00', 'end' => '18:00:00', 'buffer' => 15],
            'legal' => ['start' => '11:00:00', 'end' => '20:00:00', 'buffer' => 15],
        ];

        foreach ($definitions as $key => $definition) {
            for ($day = 0; $day <= 6; $day++) {
                Schedule::query()->updateOrCreate(
                    [
                        'provider_id' => $providers[$key]['user']->id,
                        'branch_id' => $branches[$key]->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => $definition['start'],
                        'end_time' => $definition['end'],
                        'slot_duration_minutes' => 30,
                        'buffer_minutes' => $definition['buffer'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function seedUpcomingBookings(array $providers, array $customers, array $services): void
    {
        $availabilityService = app(ScheduleAvailabilityService::class);

        $definitions = [
            [
                'booking_number' => 'DEMO-UPCOMING-HAIR-001',
                'customer_key' => 'customer_1',
                'provider_key' => 'beauty',
                'service_key' => 'haircut',
                'days_ahead' => 2,
                'slot_index' => 1,
                'status' => Booking::STATUS_ACCEPTED,
                'notes' => 'Demo accepted booking used to block one upcoming haircut slot.',
            ],
            [
                'booking_number' => 'DEMO-UPCOMING-YOGA-001',
                'customer_key' => 'customer_2',
                'provider_key' => 'fitness',
                'service_key' => 'yoga',
                'days_ahead' => 3,
                'slot_index' => 0,
                'status' => Booking::STATUS_PENDING,
                'notes' => 'Demo pending booking used to preview an upcoming yoga request.',
            ],
            [
                'booking_number' => 'DEMO-UPCOMING-PET-001',
                'customer_key' => 'customer_1',
                'provider_key' => 'pet',
                'service_key' => 'pet_grooming',
                'days_ahead' => 4,
                'slot_index' => 2,
                'status' => Booking::STATUS_ACCEPTED,
                'notes' => 'Demo accepted booking used to show a blocked pet grooming slot.',
            ],
        ];

        foreach ($definitions as $definition) {
            $existingBooking = Booking::query()
                ->where('booking_number', $definition['booking_number'])
                ->first();

            if ($existingBooking) {
                $existingBooking->slot?->update(['is_available' => false]);
                continue;
            }

            $service = $services[$definition['service_key']];
            $provider = $providers[$definition['provider_key']]['user'];
            $targetDate = now()->addDays($definition['days_ahead'])->startOfDay();

            $slots = $availabilityService->generateAvailableSlotsForDate(
                $provider,
                $targetDate,
                $service->branch_id,
                $service
            );

            if ($slots->isEmpty()) {
                $this->command?->warn('Skipping '.$definition['booking_number'].' because no upcoming slots were generated.');
                continue;
            }

            $selectedSlotData = $slots->values()->get(
                min($definition['slot_index'], max(0, $slots->count() - 1))
            );

            $slot = Slot::query()->find($selectedSlotData['slot_id']);

            if (!$slot) {
                continue;
            }

            $booking = Booking::query()->create([
                'booking_number' => $definition['booking_number'],
                'customer_id' => $customers[$definition['customer_key']]->id,
                'provider_id' => $provider->id,
                'branch_id' => $service->branch_id,
                'service_id' => $service->id,
                'slot_id' => $slot->id,
                'scheduled_at' => $slot->start_at,
                'status' => $definition['status'],
                'notes' => $definition['notes'],
            ]);

            $slot->update(['is_available' => false]);

            Payment::query()->updateOrCreate(
                ['gateway_reference' => 'demo-pending-'.$definition['booking_number']],
                [
                    'booking_id' => $booking->id,
                    'customer_id' => $customers[$definition['customer_key']]->id,
                    'provider_id' => $provider->id,
                    'gateway' => Payment::GATEWAY_CASH,
                    'method' => Payment::METHOD_CASH,
                    'payment_mode' => Payment::MODE_POSTPAID,
                    'amount' => $service->base_price,
                    'refunded_amount' => 0,
                    'currency' => 'INR',
                    'status' => Payment::STATUS_PENDING,
                    'paid_at' => null,
                    'refunded_at' => null,
                    'refund_reason' => null,
                    'meta' => ['demo' => true],
                ]
            );
        }
    }

    private function seedCompletedBookingsAndReviews(array $providers, array $customers, array $services): void
    {
        $reviewSets = [
            'haircut' => [5, 5, 5, 4, 5],
            'yoga' => [5, 5, 4],
            'pet_grooming' => [5, 4],
            'legal' => [5, 5, 4, 5, 5],
            'healthcare' => [5, 4, 4],
        ];

        $customerKeys = array_keys($customers);

        foreach ($reviewSets as $serviceKey => $ratings) {
            $service = $services[$serviceKey];
            $provider = $providers[$this->providerKeyForService($serviceKey)]['user'];

            foreach ($ratings as $index => $rating) {
                $customerKey = $customerKeys[$index % count($customerKeys)];
                $scheduledAt = now()
                    ->subDays(18 + ($index * 3))
                    ->setTime(11 + ($index % 3), 0);

                $schedule = $this->resolveSchedule($provider, $service->branch_id, $scheduledAt, (int) $service->duration_minutes);

                $slot = Slot::query()->updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'branch_id' => $service->branch_id,
                        'start_at' => $scheduledAt->format('Y-m-d H:i:s'),
                        'end_at' => $scheduledAt->copy()->addMinutes((int) $service->duration_minutes)->format('Y-m-d H:i:s'),
                    ],
                    [
                        'schedule_id' => $schedule->id,
                        'is_available' => false,
                        'reason' => 'Demo completed booking',
                    ]
                );

                $bookingNumber = sprintf('DEMO-COMPLETED-%s-%02d', strtoupper($serviceKey), $index + 1);

                $booking = Booking::query()->updateOrCreate(
                    ['booking_number' => $bookingNumber],
                    [
                        'customer_id' => $customers[$customerKey]->id,
                        'provider_id' => $provider->id,
                        'branch_id' => $service->branch_id,
                        'service_id' => $service->id,
                        'slot_id' => $slot->id,
                        'scheduled_at' => $slot->start_at,
                        'status' => Booking::STATUS_COMPLETED,
                        'notes' => 'Completed demo booking for homepage ratings and testimonials.',
                    ]
                );

                Payment::query()->updateOrCreate(
                    ['gateway_reference' => 'demo-paid-'.$bookingNumber],
                    [
                        'booking_id' => $booking->id,
                        'customer_id' => $customers[$customerKey]->id,
                        'provider_id' => $provider->id,
                        'gateway' => Payment::GATEWAY_RAZORPAY,
                        'method' => Payment::METHOD_ONLINE,
                        'payment_mode' => Payment::MODE_PREPAID,
                        'amount' => $service->base_price,
                        'refunded_amount' => 0,
                        'currency' => 'INR',
                        'status' => Payment::STATUS_PAID,
                        'paid_at' => $slot->start_at->copy()->subDay(),
                        'refunded_at' => null,
                        'refund_reason' => null,
                        'meta' => ['demo' => true],
                    ]
                );

                Review::query()->updateOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'customer_id' => $customers[$customerKey]->id,
                        'provider_id' => $provider->id,
                        'service_id' => $service->id,
                        'rating' => $rating,
                        'title' => $this->reviewTitle($rating),
                        'comment' => $this->reviewComment($service->name),
                        'is_approved' => true,
                    ]
                );
            }
        }
    }

    private function resolveSchedule(User $provider, ?string $branchId, Carbon $scheduledAt, int $durationMinutes): Schedule
    {
        return Schedule::query()->firstOrCreate(
            [
                'provider_id' => $provider->id,
                'branch_id' => $branchId,
                'day_of_week' => (int) $scheduledAt->dayOfWeek,
            ],
            [
                'start_time' => $scheduledAt->copy()->subHour()->format('H:i:s'),
                'end_time' => $scheduledAt->copy()->addMinutes($durationMinutes + 120)->format('H:i:s'),
                'slot_duration_minutes' => max(30, $durationMinutes),
                'buffer_minutes' => 15,
                'is_active' => true,
            ]
        );
    }

    private function providerKeyForService(string $serviceKey): string
    {
        return match ($serviceKey) {
            'haircut' => 'beauty',
            'yoga' => 'fitness',
            'healthcare' => 'healthcare',
            'pet_grooming' => 'pet',
            'legal' => 'legal',
            default => 'beauty',
        };
    }

    private function reviewTitle(int $rating): string
    {
        return match (true) {
            $rating >= 5 => 'Highly recommended',
            $rating === 4 => 'Very good experience',
            default => 'Good service',
        };
    }

    private function reviewComment(string $serviceName): string
    {
        return 'Demo review for '.$serviceName.'. The provider was professional, the timing felt smooth, and the overall booking experience was easy to trust.';
    }
}
