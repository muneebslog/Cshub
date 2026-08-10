<?php

namespace App\Models;

use Database\Factories\SlideFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property string $title
 * @property string $slug
 * @property string $file_path
 * @property string $original_filename
 * @property Carbon|null $lesson_date
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Category|null $category
 */
#[Fillable(['user_id', 'category_id', 'title', 'slug', 'file_path', 'original_filename', 'lesson_date', 'sort_order'])]
class Slide extends Model
{
    /** @use HasFactory<SlideFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Slide $slide): void {
            if (blank($slide->slug)) {
                $slide->slug = static::uniqueSlug($slide->title);
            }
        });

        static::updating(function (Slide $slide): void {
            if ($slide->isDirty('title') && ! $slide->isDirty('slug')) {
                $slide->slug = static::uniqueSlug($slide->title, $slide->id);
            }
        });

        static::deleting(function (Slide $slide): void {
            if (filled($slide->file_path) && Storage::disk('local')->exists($slide->file_path)) {
                Storage::disk('local')->delete($slide->file_path);
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'slide';
        $slug = $base;
        $counter = 1;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'lesson_date' => 'date',
        ];
    }
}
