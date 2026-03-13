<?php

namespace Tests\Feature;

use App\Models\ArtistProfile;
use App\Models\SiteSetting;
use App\Models\Track;
use App\Models\User;
use App\Support\UploadLimits;
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

    #[Test]
    public function artist_cannot_upload_audio_larger_than_ten_megabytes(): void
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
            ->set('title', 'Too Large')
            ->set('audioFile', UploadedFile::fake()->create('oversized.mp3', UploadLimits::DEFAULT_AUDIO_KB + 1, 'audio/mpeg'))
            ->call('save')
            ->assertHasErrors(['audioFile' => 'max']);
    }

    #[Test]
    public function artist_cannot_upload_cover_art_larger_than_two_megabytes(): void
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
            ->set('title', 'Huge Cover')
            ->set('audioFile', UploadedFile::fake()->create('valid.mp3', 1024, 'audio/mpeg'))
            ->set('coverFile', UploadedFile::fake()->image('cover.jpg')->size(UploadLimits::DEFAULT_IMAGE_KB + 1))
            ->call('save')
            ->assertHasErrors(['coverFile' => 'max']);
    }

    #[Test]
    public function artist_sees_a_friendly_error_when_media_storage_fails(): void
    {
        config()->set('media-library.disk_name', 'missing-disk');
        Storage::fake('local');

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
            ->set('title', 'Storage Failure')
            ->set('audioFile', UploadedFile::fake()->create('storage-failure.mp3', 1024, 'audio/mpeg'))
            ->call('save')
            ->assertHasErrors('audioFile')
            ->assertSee("We couldn't process that file for upload. Please check the file type and try again.");

        $this->assertDatabaseCount('tracks', 0);
    }

    #[Test]
    public function admin_upload_limit_settings_override_the_track_upload_caps(): void
    {
        SiteSetting::current()->update([
            'audio_upload_limit_kb' => 2048,
            'image_upload_limit_kb' => 1024,
        ]);

        UploadLimits::resetCache();

        $user = User::factory()->create();
        Role::findOrCreate('artist', 'web');
        $user->assignRole('artist');

        ArtistProfile::create([
            'user_id' => $user->id,
            'stage_name' => 'Configurable Artist',
            'slug' => 'configurable-artist',
            'is_approved' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Volt::test('pages.artist.upload-track')
            ->set('title', 'Configurable Audio Limit')
            ->set('audioFile', UploadedFile::fake()->create('over-custom-limit.mp3', 2049, 'audio/mpeg'))
            ->call('save')
            ->assertHasErrors(['audioFile' => 'max']);

        Volt::test('pages.artist.upload-track')
            ->set('title', 'Configurable Image Limit')
            ->set('audioFile', UploadedFile::fake()->create('ok.mp3', 1024, 'audio/mpeg'))
            ->set('coverFile', UploadedFile::fake()->image('cover.jpg')->size(1025))
            ->call('save')
            ->assertHasErrors(['coverFile' => 'max']);
    }
}
