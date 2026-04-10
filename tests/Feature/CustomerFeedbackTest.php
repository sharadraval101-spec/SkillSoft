<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'sqlite') {
            return;
        }

        Schema::dropIfExists('reviews');
        Schema::create('reviews', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('booking_id')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('service_id');
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false)->index();
            $table->timestamps();
        });
    }

    public function test_customer_can_submit_feedback_for_completed_booking(): void
    {
        $this->seed(RoleSeeder::class);

        $customer = $this->createUser('customer@example.com', User::ROLE_CUSTOMER);
        $booking = $this->createBookingForCustomer($customer, Booking::STATUS_COMPLETED);

        $response = $this->actingAs($customer)->get(route('customer.feedback.edit', $booking));

        $response->assertOk();
        $response->assertSee('Rate your completed service');

        $saveResponse = $this->actingAs($customer)->put(route('customer.feedback.update', $booking), [
            'rating' => 5,
            'title' => 'Excellent session',
            'comment' => 'Very smooth and professional from start to finish.',
        ]);

        $saveResponse->assertRedirect(route('customer.feedback.index'));

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'provider_id' => $booking->provider_id,
            'service_id' => $booking->service_id,
            'rating' => 5,
            'title' => 'Excellent session',
            'is_approved' => true,
        ]);
    }

    public function test_customer_cannot_submit_feedback_for_incomplete_booking(): void
    {
        $this->seed(RoleSeeder::class);

        $customer = $this->createUser('customer@example.com', User::ROLE_CUSTOMER);
        $booking = $this->createBookingForCustomer($customer, Booking::STATUS_PENDING);

        $response = $this->actingAs($customer)->get(route('customer.feedback.edit', $booking));

        $response->assertRedirect(route('customer.feedback.index'));

        $updateResponse = $this->actingAs($customer)->put(route('customer.feedback.update', $booking), [
            'rating' => 4,
            'title' => 'Good visit',
            'comment' => 'This should not be saved yet.',
        ]);

        $updateResponse->assertRedirect(route('customer.feedback.index'));
        $this->assertDatabaseMissing('reviews', [
            'booking_id' => $booking->id,
        ]);
    }

    private function createUser(string $email, int $role): User
    {
        return User::query()->create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function createBookingForCustomer(User $customer, string $status): Booking
    {
        $provider = $this->createUser('provider-'.Str::random(6).'@example.com', User::ROLE_PROVIDER);

        $branch = Branch::query()->create([
            'name' => 'North Studio',
            'slug' => 'north-studio-'.Str::lower(Str::random(6)),
            'city' => 'Delhi',
            'state' => 'Delhi',
            'country' => 'IN',
            'is_active' => true,
        ]);

        $providerProfile = ProviderProfile::query()->create([
            'user_id' => $provider->id,
            'branch_id' => $branch->id,
            'business_name' => 'North Studio Wellness',
            'status' => 'active',
            'commission_rate' => 10,
        ]);

        $category = ServiceCategory::query()->create([
            'name' => 'Massage Therapy',
            'slug' => 'massage-therapy-'.Str::lower(Str::random(6)),
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'provider_profile_id' => $providerProfile->id,
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Deep Tissue Massage',
            'slug' => 'deep-tissue-massage-'.Str::lower(Str::random(6)),
            'description' => 'A restorative full-body session.',
            'duration_minutes' => 60,
            'type' => '1-on-1',
            'base_price' => 1200,
            'status' => 'active',
            'is_active' => true,
        ]);

        return Booking::query()->create([
            'booking_number' => 'BK-'.Str::upper(Str::random(8)),
            'customer_id' => $customer->id,
            'provider_id' => $provider->id,
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'scheduled_at' => now()->subDay(),
            'status' => $status,
        ]);
    }
}
