<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'commentable_type' => Track::class,
            'commentable_id' => function (array $attributes) {
                return Track::factory()->create()->id;
            },
            'content' => fake()->paragraph(),
            'is_pinned' => false,
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
        ]);
    }
}
