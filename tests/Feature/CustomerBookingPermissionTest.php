<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\ProviderProfile;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerBookingPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_customer_cannot_see_or_use_reschedule_actions(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-02 09:00:00'));

        $branch = $this->createBranch();
        $provider = $this->createProvider($branch, 'provider@example.com');
        $customer = $this->createCustomer('customer@example.com');
        $service = $this->createService($provider, $branch);
        $scheduleDate = Carbon::parse('2026-04-03');
        $schedule = $this->createSchedule($provider, $branch, $scheduleDate);
        $slot = $this->createSlot($schedule, $provider, $branch, $scheduleDate->copy()->setTime(10, 0), $scheduleDate->copy()->setTime(10, 30));
        $booking = $this->createBooking($customer, $provider, $branch, $service, $slot, 'BK-CUST-0001');
        $originalSlotId = $booking->slot_id;

        $page = $this->actingAs($customer)->get(route('customer.bookings.index'));
        $page->assertOk();
        $page->assertDontSee('Reschedule');

        $viewResponse = $this->actingAs($customer)
            ->from(route('customer.bookings.index'))
            ->get(route('customer.bookings.reschedule.form', $booking));

        $viewResponse->assertRedirect(route('customer.bookings.index'));
        $viewResponse->assertSessionHas('error', 'Only providers can reschedule appointments.');

        $updateResponse = $this->actingAs($customer)
            ->from(route('customer.bookings.index'))
            ->put(route('customer.bookings.reschedule', $booking), [
                'slot_id' => (string) Str::uuid(),
            ]);

        $updateResponse->assertRedirect(route('customer.bookings.index'));
        $updateResponse->assertSessionHas('error', 'Only providers can reschedule appointments.');

        $booking->refresh();

        $this->assertSame($originalSlotId, $booking->slot_id);
        $this->assertSame(Booking::STATUS_PENDING, $booking->status);
    }

    private function createProvider(Branch $branch, string $email): User
    {
        $provider = User::query()->create([
            'name' => 'Provider Test',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => User::ROLE_PROVIDER,
            'is_active' => true,
        ]);

        ProviderProfile::query()->create([
            'user_id' => $provider->id,
            'branch_id' => $branch->id,
            'business_name' => 'Provider Business',
            'status' => 'active',
            'verified_at' => now(),
        ]);

        return $provider->fresh('providerProfile');
    }

    private function createCustomer(string $email): User
    {
        return User::query()->create([
            'name' => 'Customer Test',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);
    }

    private function createBranch(): Branch
    {
        return Branch::query()->create([
            'name' => 'Main Branch',
            'slug' => 'customer-branch-'.Str::lower(Str::random(6)),
            'email' => 'branch@example.com',
            'phone' => '1234567890',
            'country' => 'IN',
            'is_active' => true,
        ]);
    }

    private function createService(User $provider, Branch $branch): Service
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Consulting',
            'slug' => 'customer-consulting-'.Str::lower(Str::random(6)),
            'is_active' => true,
        ]);

        return Service::query()->create([
            'provider_profile_id' => $provider->providerProfile->id,
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Therapy Session',
            'slug' => 'customer-therapy-session-'.Str::lower(Str::random(6)),
            'description' => 'Test service',
            'duration_minutes' => 30,
            'base_price' => 100,
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function createSchedule(User $provider, Branch $branch, Carbon $date): Schedule
    {
        return Schedule::query()->create([
            'provider_id' => $provider->id,
            'branch_id' => $branch->id,
            'day_of_week' => (int) $date->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '14:00',
            'slot_duration_minutes' => 30,
            'buffer_minutes' => 0,
            'is_active' => true,
        ]);
    }

    private function createSlot(Schedule $schedule, User $provider, Branch $branch, Carbon $startAt, Carbon $endAt): Slot
    {
        return Slot::query()->create([
            'schedule_id' => $schedule->id,
            'provider_id' => $provider->id,
            'branch_id' => $branch->id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'is_available' => true,
            'reason' => null,
        ]);
    }

    private function createBooking(
        User $customer,
        User $provider,
        Branch $branch,
        Service $service,
        Slot $slot,
        string $bookingNumber
    ): Booking {
        $booking = Booking::query()->create([
            'booking_number' => $bookingNumber,
            'customer_id' => $customer->id,
            'provider_id' => $provider->id,
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'slot_id' => $slot->id,
            'scheduled_at' => $slot->start_at,
            'status' => Booking::STATUS_PENDING,
            'notes' => null,
        ]);

        $slot->update(['is_available' => false]);

        return $booking;
    }
}
