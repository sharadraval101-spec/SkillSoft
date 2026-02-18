<?php

namespace Tests\Feature;

use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_requires_photo_if_user_has_no_existing_photo(): void
    {
        $user = User::query()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
        ]);

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertSessionHasErrors('profile_photo');
    }

    public function test_user_can_update_profile_photo_and_details(): void
    {
        Storage::fake('public');

        $user = User::query()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
        ]);

        $image = UploadedFile::fake()->createWithContent(
            'avatar.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFAAH/e+m+7wAAAABJRU5ErkJggg==')
        );

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'profile_photo' => $image,
        ]);

        $response->assertRedirect();

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    public function test_uploaded_profile_photo_can_be_rendered_from_profile_photo_route(): void
    {
        Storage::fake('public');

        $user = User::query()->create([
            'name' => 'User One',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'profile_photo_path' => 'profile-photos/test.png',
        ]);

        Storage::disk('public')->put(
            $user->profile_photo_path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFAAH/e+m+7wAAAABJRU5ErkJggg==')
        );

        $response = $this->actingAs($user)->get(route('profile.photo.show', $user));

        $response->assertOk();
    }

    public function test_user_can_request_reset_code_by_email(): void
    {
        Mail::fake();

        $user = User::query()->create([
            'name' => 'User One',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
        ]);

        $response = $this->actingAs($user)->post(route('profile.password.send_code'));

        $response->assertRedirect();
        $response->assertSessionHas('code_sent');
        $this->assertDatabaseCount('password_reset_codes', 1);

        Mail::assertSent(PasswordResetCodeMail::class, function (PasswordResetCodeMail $mail) use ($user) {
            return $mail->user->id === $user->id;
        });
    }

    public function test_user_can_reset_password_with_valid_code(): void
    {
        $user = User::query()->create([
            'name' => 'User One',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
        ]);

        $resetCode = PasswordResetCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($user)->post(route('profile.password.reset_by_code'), [
            'code' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('password_reset_success');

        $user->refresh();
        $resetCode->refresh();

        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertNotNull($resetCode->used_at);
    }

    public function test_invalid_reset_code_does_not_update_password(): void
    {
        $user = User::query()->create([
            'name' => 'User One',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
        ]);

        PasswordResetCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($user)->post(route('profile.password.reset_by_code'), [
            'code' => '000000',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('code');

        $user->refresh();
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
