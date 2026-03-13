<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function missing_track_routes_show_a_track_specific_message(): void
    {
        $this->get('/track/does-not-exist')
            ->assertNotFound()
            ->assertSee('Track not found');
    }

    #[Test]
    public function missing_artist_routes_show_an_artist_specific_message(): void
    {
        $this->get('/artist/does-not-exist')
            ->assertNotFound()
            ->assertSee('Artist not found');
    }

    #[Test]
    public function unknown_generic_routes_use_a_prettified_page_label(): void
    {
        $this->get('/support-center')
            ->assertNotFound()
            ->assertSee('Support Center page not found');
    }
}
