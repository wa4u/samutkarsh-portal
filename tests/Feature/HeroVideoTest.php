<?php

namespace Tests\Feature;

use App\Models\HomeSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_youtube_link_renders_as_background_iframe(): void
    {
        HomeSection::updateOrCreate(
            ['key' => 'hero'],
            ['label' => 'Hero banner', 'is_enabled' => true, 'sort' => 0,
             'content' => ['video' => 'https://www.youtube.com/watch?v=kMloAceCLc4']],
        );

        $resp = $this->get('/');

        $resp->assertSuccessful();
        $resp->assertSee('youtube.com/embed/kMloAceCLc4', false);
        $resp->assertSee('autoplay=1', false);
    }

    public function test_mp4_link_renders_as_video_tag(): void
    {
        HomeSection::updateOrCreate(
            ['key' => 'hero'],
            ['label' => 'Hero banner', 'is_enabled' => true, 'sort' => 0,
             'content' => ['video' => 'https://example.com/clip.mp4']],
        );

        $resp = $this->get('/');

        $resp->assertSuccessful();
        $resp->assertSee('clip.mp4', false);
        $resp->assertSee('<video', false);
    }
}
