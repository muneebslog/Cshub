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
        'lesson_date' => '2026-03-15',
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
        ->and($slide->original_filename)->toBe('week-1.html')
        ->and($slide->lesson_date?->toDateString())->toBe('2026-03-15');

    Storage::disk('local')->assertExists($slide->file_path);
    expect(Storage::disk('local')->get($slide->file_path))->toContain('Week 1');
});

test('lesson name is derived from filename when title is blank', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('admin.slides.store'), [
        'title' => '',
        'lesson_date' => '2026-04-01',
        'html_content' => '<!DOCTYPE html><html><body><h1>Welcome</h1></body></html>',
        'original_filename' => 'Computer Science Welcome.html',
    ])->assertRedirect(route('admin.slides'));

    expect(Slide::query()->first()?->title)->toBe('Computer Science Welcome');
});

test('upload requires a lesson date', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('admin.slides.store'), [
        'title' => 'Missing date',
        'html_content' => '<!DOCTYPE html><html><body></body></html>',
        'original_filename' => 'missing-date.html',
    ])->assertSessionHasErrors('lesson_date');

    expect(Slide::query()->count())->toBe(0);
});

test('upload rejects non html filenames', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('admin.slides.store'), [
        'title' => 'Bad file',
        'lesson_date' => '2026-03-15',
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
        'lesson_date' => '2026-01-10',
    ]);

    Livewire::actingAs($user)
        ->test('pages::admin.slide-inventory')
        ->call('startEditing', $slide->id)
        ->set('editTitle', 'New Title')
        ->set('editCategoryId', $category->id)
        ->set('editLessonDate', '2026-02-20')
        ->set('editSortOrder', 5)
        ->call('update')
        ->assertHasNoErrors();

    expect($slide->fresh())
        ->title->toBe('New Title')
        ->category_id->toBe($category->id)
        ->sort_order->toBe(5)
        ->and($slide->fresh()->lesson_date?->toDateString())->toBe('2026-02-20');

    $path = $slide->file_path;

    Livewire::actingAs($user)
        ->test('pages::admin.slide-inventory')
        ->call('delete', $slide->id);

    expect(Slide::query()->find($slide->id))->toBeNull();
    Storage::disk('local')->assertMissing($path);
});
