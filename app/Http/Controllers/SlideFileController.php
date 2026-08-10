<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SlideFileController extends Controller
{
    /**
     * Stream the slide HTML so browsers render it instead of downloading as text.
     */
    public function __invoke(Slide $slide): Response
    {
        abort_unless(Storage::disk('local')->exists($slide->file_path), 404);

        return response(
            Storage::disk('local')->get($slide->file_path),
            200,
            [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Content-Disposition' => 'inline; filename="'.$slide->original_filename.'"',
            ],
        );
    }
}
