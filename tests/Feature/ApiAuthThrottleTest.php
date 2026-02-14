<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_five_attempts_per_minute(): void
    {
        $payload = [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/login', $payload)
                ->assertStatus(422);
        }

        $this->postJson('/api/auth/login', $payload)
            ->assertStatus(429);
    }
}
