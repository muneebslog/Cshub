<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('teacher can upload html through the store endpoint', function () {
    Storage::fake('local');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.slides.store'), [
            'title' => 'Lecture',
            'html_content' => '<!DOCTYPE html><html><body><h1>Hi</h1></body></html>',
            'original_filename' => 'lecture.html',
        ])
        ->assertRedirect(route('admin.slides'))
        ->assertSessionHasNoErrors();
});
