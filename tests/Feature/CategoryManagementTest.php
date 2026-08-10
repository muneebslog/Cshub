<?php

use App\Models\Category;
use App\Models\Slide;
use App\Models\User;
use Livewire\Livewire;

test('teacher can create update and delete categories', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::admin.categories')
        ->set('name', 'Operating Systems')
        ->set('sort_order', 2)
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::query()->first();

    expect($category)->not->toBeNull()
        ->and($category->name)->toBe('Operating Systems')
        ->and($category->slug)->toBe('operating-systems')
        ->and($category->sort_order)->toBe(2);

    Livewire::actingAs($user)
        ->test('pages::admin.categories')
        ->call('startEditing', $category->id)
        ->set('editName', 'OS')
        ->set('editSortOrder', 1)
        ->call('update')
        ->assertHasNoErrors();

    expect($category->fresh())
        ->name->toBe('OS')
        ->slug->toBe('os')
        ->sort_order->toBe(1);
});

test('deleting a category uncategorized its slides', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $slide = Slide::factory()->forCategory($category)->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::admin.categories')
        ->call('delete', $category->id);

    expect(Category::query()->find($category->id))->toBeNull()
        ->and($slide->fresh()->category_id)->toBeNull();
});
