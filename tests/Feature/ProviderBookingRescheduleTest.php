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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProviderBookingRescheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_provider_can_reschedule_a_listed_booking_from_the_appointments_page(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-28 09:00:00'));

        $branch = $this->createBranch();
        $provider = $this->createProvider($branch, 'provider@example.com');
        $customer = $this->createCustomer('customer@example.com');
        $service = $this->createService($provider, $branch);

        $sourceDate = Carbon::parse('2026-04-02');
        $targetDate = Carbon::parse('2026-04-03');

        $sourceSchedule = $this->createSchedule($provider, $branch, $sourceDate);
        $targetSchedule = $this->createSchedule($provider, $branch, $targetDate);

        $sourceSlot = $this->createSlot($sourceSchedule, $provider, $branch, $sourceDate->copy()->setTime(10, 0), $sourceDate->copy()->setTime(10, 30));
        $targetSlot = $this->createSlot($targetSchedule, $provider, $branch, $targetDate->copy()->setTime(10, 0), $targetDate->copy()->setTime(10, 30));
        $booking = $this->createBooking($customer, $provider, $branch, $service, $sourceSlot, 'BK-LIST-0001');

        $page = $this->actingAs($provider)->get(route('provider.bookings.index'));
        $page->assertOk();
        $page->assertSee('Reschedule');
        $page->assertSee($booking->booking_number);

        $response = $this->actingAs($provider)
            ->from(route('provider.bookings.index'))
            ->put(route('provider.bookings.reschedule', $booking), [
                'booking_id' => $booking->id,
                'reschedule_to_date' => $targetDate->toDateString(),
                'reason' => 'Provider changed availability',
            ]);

        $response->assertRedirect(route('provider.bookings.index'));
        $response->assertSessionHas('success', 'Appointment rescheduled successfully.');

        $booking->refresh();
        $sourceSlot->refresh();
        $targetSlot->refresh();

        $this->assertSame($targetSlot->id, $booking->slot_id);
        $this->assertTrue($booking->scheduled_at->equalTo($targetSlot->start_at));
        $this->assertStringContainsString('[Provider Rescheduled]', (string) $booking->notes);
        $this->assertStringContainsString('Provider changed availability', (string) $booking->notes);
        $this->assertTrue($sourceSlot->is_available);
        $this->assertFalse($targetSlot->is_available);
    }

    public function test_provider_cannot_reschedule_listed_booking_when_target_slot_is_taken(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-28 09:00:00'));

        $branch = $this->createBranch();
        $provider = $this->createProvider($branch, 'provider@example.com');
        $customer = $this->createCustomer('customer@example.com');
        $conflictCustomer = $this->createCustomer('occupied@example.com');
        $service = $this->createService($provider, $branch);

        $sourceDate = Carbon::parse('2026-04-02');
        $targetDate = Carbon::parse('2026-04-03');

        $sourceSchedule = $this->createSchedule($provider, $branch, $sourceDate);
        $targetSchedule = $this->createSchedule($provider, $branch, $targetDate);

        $sourceSlot = $this->createSlot($sourceSchedule, $provider, $branch, $sourceDate->copy()->setTime(10, 0), $sourceDate->copy()->setTime(10, 30));
        $occupiedTargetSlot = $this->createSlot($targetSchedule, $provider, $branch, $targetDate->copy()->setTime(10, 0), $targetDate->copy()->setTime(10, 30));

        $booking = $this->createBooking($customer, $provider, $branch, $service, $sourceSlot, 'BK-LIST-1001');
        $this->createBooking($conflictCustomer, $provider, $branch, $service, $occupiedTargetSlot, 'BK-LIST-9999');

        $response = $this->actingAs($provider)
            ->from(route('provider.bookings.index'))
            ->put(route('provider.bookings.reschedule', $booking), [
                'booking_id' => $booking->id,
                'reschedule_to_date' => $targetDate->toDateString(),
                'reason' => 'Provider changed availability',
            ]);

        $response->assertRedirect(route('provider.bookings.index'));
        $response->assertSessionHasErrors('reschedule_to_date');

        $booking->refresh();
        $sourceSlot->refresh();

        $this->assertSame($sourceSlot->id, $booking->slot_id);
        $this->assertTrue($booking->scheduled_at->equalTo($sourceSlot->start_at));
        $this->assertFalse($sourceSlot->is_available);
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
            'slug' => 'booking-branch-'.Str::lower(Str::random(6)),
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
            'slug' => 'consulting-'.Str::lower(Str::random(6)),
            'is_active' => true,
        ]);

        return Service::query()->create([
            'provider_profile_id' => $provider->providerProfile->id,
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Therapy Session',
            'slug' => 'therapy-session-'.Str::lower(Str::random(6)),
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
