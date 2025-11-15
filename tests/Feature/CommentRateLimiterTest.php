<?php

namespace Tests\Feature;

use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentRateLimiterTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_rate_limit_blocks_fast_posts(): void
    {
        $user = User::factory()->create();
        $track = Track::factory()->create();

        $payload = [
            'content' => 'Legit comment',
            'parent_id' => null,
            'commentable_type' => 'track',
            'commentable_id' => $track->id,
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)
                ->postJson(route('tracks.comments.store', $track), $payload)
                ->assertStatus(200);
        }

        $this->actingAs($user)
            ->postJson(route('tracks.comments.store', $track), $payload)
            ->assertStatus(429)
            ->assertJson([
                'message' => 'You are commenting too quickly. Please slow down and try again shortly.'
            ]);
    }
}
