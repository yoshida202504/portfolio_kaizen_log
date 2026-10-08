<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_attempts_are_limited_after_five_failures(): void
    {
        $user = User::factory()->create([
            'email' => 'rate-limit@example.com',
            'password' => Hash::make('CorrectPassword1'),
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->from(route('login'))
                ->post(route('login.store'), [
                    'email' => $user->email,
                    'password' => 'IncorrectPassword1',
                ])
                ->assertRedirect(route('login'))
                ->assertSessionHasErrors('email');
        }

        $this->postJson(route('login.store'), [
            'email' => $user->email,
            'password' => 'IncorrectPassword1',
        ])
            ->assertTooManyRequests()
            ->assertJsonValidationErrors('email');
    }

    public function test_successful_login_clears_previous_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'clear-rate-limit@example.com',
            'password' => Hash::make('CorrectPassword1'),
        ]);

        foreach (range(1, 4) as $attempt) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'IncorrectPassword1',
            ]);
        }

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'CorrectPassword1',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));

        foreach (range(1, 5) as $attempt) {
            $this->from(route('login'))
                ->post(route('login.store'), [
                    'email' => $user->email,
                    'password' => 'IncorrectPassword1',
                ])->assertRedirect(route('login'));
        }

        $this->postJson(route('login.store'), [
            'email' => $user->email,
            'password' => 'IncorrectPassword1',
        ])->assertTooManyRequests();
    }
}
