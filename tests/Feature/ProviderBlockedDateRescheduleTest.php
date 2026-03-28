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

class ProviderBlockedDateRescheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_provider_can_block_a_day_and_reschedule_multiple_appointments_in_one_request(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-27 09:00:00'));

        $branch = $this->createBranch();
        $provider = $this->createProvider($branch, 'provider@example.com');
        $customerA = $this->createCustomer('customer-a@example.com');
        $customerB = $this->createCustomer('customer-b@example.com');
        $service = $this->createService($provider, $branch);

        $sourceDate = Carbon::parse('2026-03-30');
        $targetDate = Carbon::parse('2026-03-31');

        $sourceSchedule = $this->createSchedule($provider, $branch, $sourceDate);
        $targetSchedule = $this->createSchedule($provider, $branch, $targetDate);

        $sourceSlotA = $this->createSlot($sourceSchedule, $provider, $branch, $sourceDate->copy()->setTime(10, 0), $sourceDate->copy()->setTime(10, 30));
        $sourceSlotB = $this->createSlot($sourceSchedule, $provider, $branch, $sourceDate->copy()->setTime(11, 0), $sourceDate->copy()->setTime(11, 30));
        $targetSlotA = $this->createSlot($targetSchedule, $provider, $branch, $targetDate->copy()->setTime(10, 0), $targetDate->copy()->setTime(10, 30));
        $targetSlotB = $this->createSlot($targetSchedule, $provider, $branch, $targetDate->copy()->setTime(11, 0), $targetDate->copy()->setTime(11, 30));

        $bookingA = $this->createBooking($customerA, $provider, $branch, $service, $sourceSlotA, 'BK-TEST-0001');
        $bookingB = $this->createBooking($customerB, $provider, $branch, $service, $sourceSlotB, 'BK-TEST-0002');

        $response = $this->actingAs($provider)
            ->from(route('provider.availability.index'))
            ->post(route('provider.availability.blocks.store'), [
                'block_date' => $sourceDate->toDateString(),
                'reason' => 'Medical leave',
                'reschedule_to_date' => $targetDate->toDateString(),
            ]);

        $response->assertRedirect(route('provider.availability.index'));
        $response->assertSessionHas('success', 'Blocked date/time added and 2 appointments were rescheduled successfully.');

        $this->assertDatabaseHas('provider_unavailable_dates', [
            'provider_id' => $provider->id,
            'block_date' => $sourceDate->copy()->startOfDay()->format('Y-m-d H:i:s'),
            'reason' => 'Medical leave',
        ]);

        $bookingA->refresh();
        $bookingB->refresh();
        $sourceSlotA->refresh();
        $sourceSlotB->refresh();
        $targetSlotA->refresh();
        $targetSlotB->refresh();

        $this->assertSame($targetSlotA->id, $bookingA->slot_id);
        $this->assertSame($targetSlotB->id, $bookingB->slot_id);
        $this->assertTrue($bookingA->scheduled_at->equalTo($targetSlotA->start_at));
        $this->assertTrue($bookingB->scheduled_at->equalTo($targetSlotB->start_at));
        $this->assertStringContainsString('[Provider Rescheduled]', (string) $bookingA->notes);
        $this->assertStringContainsString('Medical leave', (string) $bookingA->notes);
        $this->assertStringContainsString('[Provider Rescheduled]', (string) $bookingB->notes);

        $this->assertFalse($sourceSlotA->is_available);
        $this->assertFalse($sourceSlotB->is_available);
        $this->assertFalse($targetSlotA->is_available);
        $this->assertFalse($targetSlotB->is_available);
    }

    public function test_provider_bulk_reschedule_rolls_back_when_any_target_slot_is_unavailable(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-27 09:00:00'));

        $branch = $this->createBranch();
        $provider = $this->createProvider($branch, 'provider@example.com');
        $customerA = $this->createCustomer('customer-a@example.com');
        $customerB = $this->createCustomer('customer-b@example.com');
        $conflictCustomer = $this->createCustomer('customer-conflict@example.com');
        $service = $this->createService($provider, $branch);

        $sourceDate = Carbon::parse('2026-03-30');
        $targetDate = Carbon::parse('2026-03-31');

        $sourceSchedule = $this->createSchedule($provider, $branch, $sourceDate);
        $targetSchedule = $this->createSchedule($provider, $branch, $targetDate);

        $sourceSlotA = $this->createSlot($sourceSchedule, $provider, $branch, $sourceDate->copy()->setTime(10, 0), $sourceDate->copy()->setTime(10, 30));
        $sourceSlotB = $this->createSlot($sourceSchedule, $provider, $branch, $sourceDate->copy()->setTime(11, 0), $sourceDate->copy()->setTime(11, 30));
        $targetSlotA = $this->createSlot($targetSchedule, $provider, $branch, $targetDate->copy()->setTime(10, 0), $targetDate->copy()->setTime(10, 30));
        $occupiedTargetSlot = $this->createSlot($targetSchedule, $provider, $branch, $targetDate->copy()->setTime(11, 0), $targetDate->copy()->setTime(11, 30));

        $bookingA = $this->createBooking($customerA, $provider, $branch, $service, $sourceSlotA, 'BK-TEST-1001');
        $bookingB = $this->createBooking($customerB, $provider, $branch, $service, $sourceSlotB, 'BK-TEST-1002');
        $this->createBooking($conflictCustomer, $provider, $branch, $service, $occupiedTargetSlot, 'BK-TEST-9999');

        $response = $this->actingAs($provider)
            ->from(route('provider.availability.index'))
            ->post(route('provider.availability.blocks.store'), [
                'block_date' => $sourceDate->toDateString(),
                'reason' => 'Emergency closure',
                'reschedule_to_date' => $targetDate->toDateString(),
            ]);

        $response->assertRedirect(route('provider.availability.index'));
        $response->assertSessionHasErrors('reschedule_to_date');

        $this->assertDatabaseMissing('provider_unavailable_dates', [
            'provider_id' => $provider->id,
            'block_date' => $sourceDate->copy()->startOfDay()->format('Y-m-d H:i:s'),
        ]);

        $bookingA->refresh();
        $bookingB->refresh();
        $sourceSlotA->refresh();
        $sourceSlotB->refresh();
        $targetSlotA->refresh();

        $this->assertSame($sourceSlotA->id, $bookingA->slot_id);
        $this->assertSame($sourceSlotB->id, $bookingB->slot_id);
        $this->assertTrue($bookingA->scheduled_at->equalTo($sourceSlotA->start_at));
        $this->assertTrue($bookingB->scheduled_at->equalTo($sourceSlotB->start_at));
        $this->assertFalse($sourceSlotA->is_available);
        $this->assertFalse($sourceSlotB->is_available);
        $this->assertTrue($targetSlotA->is_available);
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
            'slug' => 'main-branch-'.Str::lower(Str::random(6)),
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
