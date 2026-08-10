<?php

use App\Models\Slide;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

new #[Layout('layouts::viewer')] class extends Component {
    #[Locked]
    public Slide $slide;

    public function mount(Slide $slide): void
    {
        $this->slide = $slide->loadMissing('category');
    }

    public function title(): string
    {
        return $this->slide->title;
    }

    public function rendering($view): void
    {
        $view->layoutData([
            'title' => $this->slide->title,
            'fileUrl' => route('slides.file', $this->slide),
        ]);
    }
}; ?>

<iframe
    title="{{ $slide->title }}"
    src="{{ route('slides.file', $slide) }}"
    class="h-full w-full border-0 bg-white"
></iframe>
