<?php

use App\Models\Category;
use App\Models\User;

test('teacher can quickly create a folder via json', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('admin.categories.store'), [
        'name' => 'Computer Science',
    ]);

    $response->assertCreated()
        ->assertJsonPath('name', 'Computer Science');

    $category = Category::query()->first();

    expect($category)->not->toBeNull()
        ->and($category->name)->toBe('Computer Science')
        ->and($response->json('id'))->toBe($category->id);
});

test('guests cannot create folders', function () {
    $this->postJson(route('admin.categories.store'), [
        'name' => 'Blocked',
    ])->assertUnauthorized();

    expect(Category::query()->count())->toBe(0);
});

test('folder name is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('admin.categories.store'), [
        'name' => '',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});
