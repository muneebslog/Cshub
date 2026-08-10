<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<Slide>
 */
class SlideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'file_path' => 'slides/'.Str::uuid().'.html',
            'original_filename' => Str::slug($title).'.html',
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Slide $slide): void {
            Storage::disk('local')->put(
                $slide->file_path,
                '<!DOCTYPE html><html><head><title>'.$slide->title.'</title></head><body><h1>'.$slide->title.'</h1></body></html>',
            );
        });
    }

    public function forCategory(?Category $category = null): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category?->id ?? Category::factory(),
        ]);
    }
}
