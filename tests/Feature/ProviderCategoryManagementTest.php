<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProviderCategoryManagementTest extends TestCase
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

    public function test_provider_can_delete_a_category_by_reassigning_linked_services(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-01 09:00:00'));

        $provider = $this->createProvider('provider@example.com');
        $customer = $this->createCustomer('customer@example.com');
        $sourceCategory = $this->createCategory('Source Category');
        $targetCategory = $this->createCategory('Target Category');
        $service = $this->createService($provider, $sourceCategory);
        $booking = $this->createBooking($customer, $provider, $service);

        $response = $this->actingAs($provider)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->delete(route('provider.categories.destroy', $sourceCategory), [
                'reassign_service_category_id' => $targetCategory->id,
            ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'message' => 'Category deleted successfully. 1 linked service was moved to Target Category.',
        ]);

        $this->assertDatabaseMissing('service_categories', [
            'id' => $sourceCategory->id,
        ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'service_category_id' => $targetCategory->id,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_provider_cannot_delete_a_category_with_linked_services_without_selecting_a_target(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-01 09:00:00'));

        $provider = $this->createProvider('provider@example.com');
        $customer = $this->createCustomer('customer@example.com');
        $sourceCategory = $this->createCategory('Source Category');
        $this->createCategory('Target Category');
        $service = $this->createService($provider, $sourceCategory);
        $this->createBooking($customer, $provider, $service);

        $response = $this->actingAs($provider)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->delete(route('provider.categories.destroy', $sourceCategory));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('reassign_service_category_id');

        $this->assertDatabaseHas('service_categories', [
            'id' => $sourceCategory->id,
        ]);
        $this->assertDatabaseHas('services', [
            'service_category_id' => $sourceCategory->id,
        ]);
    }

    private function createProvider(string $email): User
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
            'branch_id' => null,
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

    private function createCategory(string $name): ServiceCategory
    {
        return ServiceCategory::query()->create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'description' => 'Test category',
            'status' => 'active',
            'is_active' => true,
            'display_order' => 0,
        ]);
    }

    private function createService(User $provider, ServiceCategory $category): Service
    {
        return Service::query()->create([
            'provider_profile_id' => $provider->providerProfile->id,
            'service_category_id' => $category->id,
            'branch_id' => null,
            'name' => 'Therapy Session',
            'slug' => 'therapy-session-'.Str::lower(Str::random(6)),
            'description' => 'Test service',
            'duration_minutes' => 30,
            'base_price' => 100,
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function createBooking(User $customer, User $provider, Service $service): Booking
    {
        return Booking::query()->create([
            'booking_number' => 'BK-'.Str::upper(Str::random(8)),
            'customer_id' => $customer->id,
            'provider_id' => $provider->id,
            'branch_id' => null,
            'service_id' => $service->id,
            'slot_id' => null,
            'scheduled_at' => now()->addDay(),
            'status' => Booking::STATUS_PENDING,
            'notes' => null,
        ]);
    }
}
