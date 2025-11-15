<?php

namespace Tests\Feature;

use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentSpamProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_spammy_comment_is_rejected(): void
    {
        $user = User::factory()->create();
        $track = Track::factory()->create();

        $response = $this->actingAs($user)->postJson(route('tracks.comments.store', $track), [
            'content' => 'Buy now at https://spam.test',
            'parent_id' => null,
            'commentable_type' => 'track',
            'commentable_id' => $track->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }
}
