<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_forgotten_account_can_be_recovered_by_phone_number(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'juan.delacruz@example.com',
            'phone' => '+639171234567',
        ]);

        // Local 09 format matches +639 stored format
        $response = $this->from('/forgot-password')->post('/recover-account', [
            'phone' => '0917 123 4567',
        ]);

        $response
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status', 'Account found (ju***@example.com)! We sent a password reset link to your email.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_account_recovery_rejects_unknown_phone_number(): void
    {
        $this->from('/forgot-password')
            ->post('/recover-account', ['phone' => '09998887777'])
            ->assertRedirect('/forgot-password')
            ->assertSessionHasErrors('phone');
    }
}
