<?php

namespace Tests\Feature;

use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthForgotPasswordOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_request_four_digit_forgot_password_otp(): void
    {
        Mail::fake();

        $user = User::query()->create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
        ]);

        $response = $this->postJson(route('password.forgot.send_otp'), [
            'email' => $user->email,
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => '4-digit OTP sent to your email.',
        ]);

        $this->assertDatabaseCount('password_reset_codes', 1);

        Mail::assertSent(PasswordResetCodeMail::class, function (PasswordResetCodeMail $mail) use ($user) {
            return $mail->user->id === $user->id
                && preg_match('/^\d{4}$/', $mail->code) === 1;
        });
    }

    public function test_guest_can_verify_valid_forgot_password_otp(): void
    {
        $user = User::query()->create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
        ]);

        $resetCode = PasswordResetCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('1234'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson(route('password.forgot.verify_otp'), [
            'email' => $user->email,
            'otp' => '1234',
        ]);

        $response->assertOk();
        $response->assertJson([
            'verified' => true,
            'message' => 'OTP verified. Enter your new password.',
        ]);

        $resetCode->refresh();
        $this->assertNull($resetCode->used_at);
    }

    public function test_guest_gets_validation_error_for_invalid_forgot_password_otp(): void
    {
        $user = User::query()->create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
        ]);

        PasswordResetCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('1234'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson(route('password.forgot.verify_otp'), [
            'email' => $user->email,
            'otp' => '9999',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('otp');
    }

    public function test_guest_can_reset_password_with_valid_forgot_password_otp(): void
    {
        $user = User::query()->create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
        ]);

        $resetCode = PasswordResetCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('1234'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson(route('password.forgot.reset'), [
            'email' => $user->email,
            'otp' => '1234',
            'password' => 'Newpassword@123',
            'password_confirmation' => 'Newpassword@123',
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Password reset successful. Please login with your new password.',
            'redirect' => route('login'),
        ]);

        $user->refresh();
        $resetCode->refresh();

        $this->assertTrue(Hash::check('Newpassword@123', $user->password));
        $this->assertNotNull($resetCode->used_at);
    }

    public function test_guest_cannot_reset_password_with_invalid_otp(): void
    {
        $user = User::query()->create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
        ]);

        PasswordResetCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('1234'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson(route('password.forgot.reset'), [
            'email' => $user->email,
            'otp' => '9999',
            'password' => 'Newpassword@123',
            'password_confirmation' => 'Newpassword@123',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('otp');

        $user->refresh();
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
