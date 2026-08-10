<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class StoreCategoryController extends Controller
{
    /**
     * Quickly create a course folder from the upload form modal.
     */
    public function __invoke(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::query()->create([
            'name' => $request->validated('name'),
            'sort_order' => (int) Category::query()->max('sort_order') + 1,
        ]);

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ], 201);
    }
}
