<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_only_and_filters_by_centre_and_year(): void
    {
        Testimonial::create(['author_name' => 'Asha', 'center' => 'Hubballi', 'date' => '2024-02-11', 'body' => 'Loved the session', 'is_published' => true]);
        Testimonial::create(['author_name' => 'Ravi', 'center' => 'Raichur', 'date' => '2023-09-18', 'body' => 'Very helpful', 'is_published' => true]);
        Testimonial::create(['author_name' => 'Hidden', 'center' => 'Hubballi', 'date' => '2024-05-01', 'body' => 'Draft note', 'is_published' => false]);

        // Base page: published only.
        $this->get('/testimonials')->assertSuccessful()
            ->assertSee('Asha')->assertSee('Ravi')->assertDontSee('Hidden');

        // Centre filter.
        $this->get('/testimonials?center=Hubballi')->assertSuccessful()
            ->assertSee('Asha')->assertDontSee('Ravi');

        // Year filter.
        $this->get('/testimonials?year=2023')->assertSuccessful()
            ->assertSee('Ravi')->assertDontSee('Asha');
    }
}
