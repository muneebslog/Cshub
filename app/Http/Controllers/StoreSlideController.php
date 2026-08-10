<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSlideRequest;
use App\Models\Slide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreSlideController extends Controller
{
    /**
     * Store a newly uploaded HTML slide in the course library.
     */
    public function __invoke(StoreSlideRequest $request): RedirectResponse
    {
        $filename = $request->validated('original_filename');
        $path = 'slides/'.Str::uuid()->toString().'.html';

        Storage::disk('local')->put($path, $request->validated('html_content'));

        Slide::query()->create([
            'user_id' => $request->user()->id,
            'category_id' => $request->validated('category_id'),
            'title' => $request->validated('title'),
            'file_path' => $path,
            'original_filename' => $filename,
            'sort_order' => (int) Slide::query()->max('sort_order') + 1,
        ]);

        return redirect()
            ->route('admin.slides')
            ->with('status', __('Slide uploaded.'));
    }
}
