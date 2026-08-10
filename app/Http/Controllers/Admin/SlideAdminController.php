<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Slide;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SlideAdminController extends Controller
{
    /**
     * Show the teacher lesson upload desk and library inventory.
     */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.slides', [
            'categories' => $categories,
            'slideCount' => Slide::query()->count(),
            'categoryCount' => $categories->count(),
        ]);
    }
}
