<?php

namespace Tests\Feature;

use App\Models\ArtistProfile;
use App\Models\HomepageBannerSlide;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HomepageBannerAndArtistDashboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function homepage_falls_back_to_the_static_hero_when_no_banner_slides_exist(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('GrinMuzik')
            ->assertSee('Independent music. Real artists. Support the creators you love.');
    }

    #[Test]
    public function homepage_renders_admin_configured_banner_slides(): void
    {
        Storage::fake('public');

        HomepageBannerSlide::create([
            'name' => 'Launch Slide',
            'badge_text' => 'Fresh from admin',
            'heading' => 'Spotlight the next wave',
            'body' => 'Promote collections, premieres, or events without touching the template.',
            'primary_button_label' => 'Browse now',
            'primary_button_url' => route('browse'),
            'background_image' => 'homepage-banners/launch.jpg',
            'show_overlay_content' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Fresh from admin')
            ->assertSee('Spotlight the next wave')
            ->assertSee('Browse now');
    }

    #[Test]
    public function artist_dashboard_shows_total_downloads_metric(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('artist', 'web');
        $user->assignRole('artist');

        $profile = ArtistProfile::create([
            'user_id' => $user->id,
            'stage_name' => 'Dashboard Artist',
            'slug' => 'dashboard-artist',
            'is_approved' => true,
            'is_active' => true,
        ]);

        Track::create([
            'user_id' => $user->id,
            'artist_profile_id' => $profile->id,
            'title' => 'Downloaded Track',
            'slug' => 'downloaded-track',
            'is_published' => true,
            'is_free' => true,
            'downloads_count' => 42,
        ]);

        $this->actingAs($user);

        $this->get(route('artist.dashboard'))
            ->assertOk()
            ->assertSee('Total Downloads')
            ->assertSee('42');
    }
}
