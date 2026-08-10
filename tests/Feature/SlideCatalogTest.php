<?php

use App\Models\Category;
use App\Models\Slide;
use Livewire\Livewire;

test('guests can view the slide catalog', function () {
    $category = Category::factory()->create(['name' => 'Algorithms']);
    $slide = Slide::factory()->forCategory($category)->create(['title' => 'Sorting Basics']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Sorting Basics')
        ->assertSee('Algorithms')
        ->assertSee('Teacher login');
});

test('guests can filter slides by category', function () {
    $matching = Category::factory()->create(['name' => 'Networks', 'slug' => 'networks']);
    $other = Category::factory()->create(['name' => 'Databases', 'slug' => 'databases']);

    Slide::factory()->forCategory($matching)->create(['title' => 'TCP Slides']);
    Slide::factory()->forCategory($other)->create(['title' => 'SQL Slides']);

    Livewire::test('pages::home')
        ->call('selectFolder', 'networks')
        ->assertSee('TCP Slides')
        ->assertDontSee('SQL Slides');
});

test('guests can switch explorer view modes', function () {
    Slide::factory()->create(['title' => 'Lesson Alpha']);

    Livewire::test('pages::home')
        ->call('setView', 'details')
        ->assertSet('view', 'details')
        ->assertSee('Lesson date')
        ->assertSee('Lesson Alpha');
});

test('guests can open the slide viewer and html file', function () {
    $slide = Slide::factory()->create(['title' => 'Intro HTML']);

    $this->get(route('slides.show', $slide))
        ->assertOk()
        ->assertSee('Intro HTML')
        ->assertSee(route('slides.file', $slide), false);

    $this->get(route('slides.file', $slide))
        ->assertOk()
        ->assertHeader('content-type', 'text/html; charset=UTF-8')
        ->assertSee('<h1>Intro HTML</h1>', false);
});

test('registration is disabled', function () {
    $this->get('/register')->assertNotFound();
});

test('guests cannot manage slides or categories', function () {
    $this->get(route('admin.slides'))->assertRedirect(route('login'));
    $this->get(route('admin.categories'))->assertRedirect(route('login'));
});
