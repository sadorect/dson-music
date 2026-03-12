<?php

namespace Tests\Feature;

use App\Models\ArtistProfile;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtistTrackUploadTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function artist_can_upload_a_track_without_relying_on_livewire_tmp_paths(): void
    {
        config()->set('media-library.disk_name', 'public');
        Storage::fake('local');
        Storage::fake('public');

        $user = User::factory()->create();
        Role::findOrCreate('artist', 'web');
        $user->assignRole('artist');

        ArtistProfile::create([
            'user_id' => $user->id,
            'stage_name' => 'Test Artist',
            'slug' => 'test-artist',
            'is_approved' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Volt::test('pages.artist.upload-track')
            ->set('title', 'March 02')
            ->set('audioFile', UploadedFile::fake()->create('Mark02.mp3', 1024, 'audio/mpeg'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('artist.tracks'));

        $track = Track::query()->firstOrFail();
        $media = $track->getFirstMedia('audio');

        $this->assertNotNull($media);
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
        $this->assertSame([], Storage::disk('local')->allFiles('tmp/media-imports'));
    }

    #[Test]
    public function artist_can_upload_a_track_when_the_temp_file_reports_a_generic_mime_type(): void
    {
        config()->set('media-library.disk_name', 'public');
        Storage::fake('local');
        Storage::fake('public');

        $user = User::factory()->create();
        Role::findOrCreate('artist', 'web');
        $user->assignRole('artist');

        ArtistProfile::create([
            'user_id' => $user->id,
            'stage_name' => 'Test Artist',
            'slug' => 'test-artist',
            'is_approved' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Volt::test('pages.artist.upload-track')
            ->set('title', 'Generic Mime Track')
            ->set('audioFile', UploadedFile::fake()->create('GenericMime.mp3', 1024, 'application/octet-stream'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('artist.tracks'));

        $track = Track::query()->where('title', 'Generic Mime Track')->firstOrFail();

        $this->assertNotNull($track->getFirstMedia('audio'));
        $this->assertSame([], Storage::disk('local')->allFiles('tmp/media-imports'));
    }

    #[Test]
    public function upload_page_renders_audio_and_cover_progress_labels(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('artist', 'web');
        $user->assignRole('artist');

        ArtistProfile::create([
            'user_id' => $user->id,
            'stage_name' => 'Test Artist',
            'slug' => 'test-artist',
            'is_approved' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Volt::test('pages.artist.upload-track')
            ->assertSee('Uploading audio file...')
            ->assertSee('Uploading cover art...');
    }
}
