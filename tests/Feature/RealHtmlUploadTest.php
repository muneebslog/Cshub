<?php

use App\Models\Slide;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('real welcome html file content can be stored', function () {
    Storage::fake('local');

    $path = 'C:\\Users\\pc\\Downloads\\Computer Science Welcome.html';
    expect(file_exists($path))->toBeTrue();

    $user = User::factory()->create();
    $html = file_get_contents($path);

    $this->actingAs($user)
        ->post(route('admin.slides.store'), [
            'title' => 'Computer Science Welcome',
            'lesson_date' => '2026-08-10',
            'html_content' => $html,
            'original_filename' => 'Computer Science Welcome.html',
        ])
        ->assertRedirect(route('admin.slides'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    $slide = Slide::query()->first();

    expect($slide)->not->toBeNull();
    Storage::disk('local')->assertExists($slide->file_path);
    expect(Storage::disk('local')->get($slide->file_path))->toContain('Computer Science');
});
