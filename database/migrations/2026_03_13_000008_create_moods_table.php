<?php

use App\Models\Mood;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moods', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('related_genres')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('moods')->insert(
            collect(Mood::defaultDefinitions())
                ->values()
                ->map(function (array $definition, int $index) use ($now): array {
                    return [
                        'name' => $definition['name'],
                        'slug' => \Illuminate\Support\Str::slug($definition['name']),
                        'description' => null,
                        'related_genres' => json_encode($definition['related_genres'], JSON_THROW_ON_ERROR),
                        'sort_order' => $index,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })
                ->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('moods');
    }
};
