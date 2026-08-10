<?php

use App\Models\Category;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

test('teacher can upload a slide via form post', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.slides.store'), [
        'title' => 'Week 1 Lecture',
        'category_id' => $category->id,
        'html_content' => '<!DOCTYPE html><html><body><h1>Week 1</h1></body></html>',
        'original_filename' => 'week-1.html',
    ]);

    $response->assertRedirect(route('admin.slides'));
    $response->assertSessionHas('status');

    $slide = Slide::query()->first();

    expect($slide)->not->toBeNull()
        ->and($slide->title)->toBe('Week 1 Lecture')
        ->and($slide->category_id)->toBe($category->id)
        ->and($slide->user_id)->toBe($user->id)
        ->and($slide->original_filename)->toBe('week-1.html');

    Storage::disk('local')->assertExists($slide->file_path);
    expect(Storage::disk('local')->get($slide->file_path))->toContain('Week 1');
});

test('upload rejects non html filenames', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('admin.slides.store'), [
        'title' => 'Bad file',
        'html_content' => 'just text',
        'original_filename' => 'notes.txt',
    ])->assertSessionHasErrors('original_filename');

    expect(Slide::query()->count())->toBe(0);
});

test('teacher can update and delete a slide', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $slide = Slide::factory()->create([
        'user_id' => $user->id,
        'title' => 'Old Title',
        'category_id' => null,
    ]);

    Livewire::actingAs($user)
        ->test('pages::admin.slide-inventory')
        ->call('startEditing', $slide->id)
        ->set('editTitle', 'New Title')
        ->set('editCategoryId', $category->id)
        ->set('editSortOrder', 5)
        ->call('update')
        ->assertHasNoErrors();

    expect($slide->fresh())
        ->title->toBe('New Title')
        ->category_id->toBe($category->id)
        ->sort_order->toBe(5);

    $path = $slide->file_path;

    Livewire::actingAs($user)
        ->test('pages::admin.slide-inventory')
        ->call('delete', $slide->id);

    expect(Slide::query()->find($slide->id))->toBeNull();
    Storage::disk('local')->assertMissing($path);
});
