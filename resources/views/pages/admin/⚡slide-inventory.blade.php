<?php

use App\Models\Category;
use App\Models\Slide;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public ?int $editingId = null;

    public string $editTitle = '';

    public ?int $editCategoryId = null;

    public int $editSortOrder = 0;

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Slide>
     */
    #[Computed]
    public function slides(): Collection
    {
        return Slide::query()
            ->with('category')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();
    }

    public function startEditing(int $slideId): void
    {
        $slide = Slide::query()->findOrFail($slideId);

        $this->editingId = $slide->id;
        $this->editTitle = $slide->title;
        $this->editCategoryId = $slide->category_id;
        $this->editSortOrder = $slide->sort_order;
    }

    public function cancelEditing(): void
    {
        $this->reset('editingId', 'editTitle', 'editCategoryId', 'editSortOrder');
    }

    public function update(): void
    {
        $this->editCategoryId = $this->editCategoryId ?: null;

        $validated = $this->validate([
            'editingId' => ['required', 'integer', 'exists:slides,id'],
            'editTitle' => ['required', 'string', 'max:255'],
            'editCategoryId' => ['nullable', 'integer', 'exists:categories,id'],
            'editSortOrder' => ['required', 'integer', 'min:0'],
        ]);

        $slide = Slide::query()->findOrFail($validated['editingId']);

        $slide->update([
            'title' => $validated['editTitle'],
            'category_id' => $validated['editCategoryId'],
            'sort_order' => $validated['editSortOrder'],
        ]);

        $this->cancelEditing();
        unset($this->slides);

        Flux::toast(variant: 'success', text: __('Slide updated.'));
    }

    public function delete(int $slideId): void
    {
        $slide = Slide::query()->findOrFail($slideId);
        $slide->delete();

        if ($this->editingId === $slideId) {
            $this->cancelEditing();
        }

        unset($this->slides);

        Flux::toast(variant: 'success', text: __('Slide deleted.'));
    }
}; ?>

<div class="desk-panel">
    <div class="desk-titlebar">
        <div class="explorer-traffic" aria-hidden="true">
            <span class="bg-[#e26d5c]"></span>
            <span class="bg-[#f0c75e]"></span>
            <span class="bg-[#6bbf8a]"></span>
        </div>
        <div class="min-w-0 flex-1 text-center text-sm font-medium text-ink-soft">
            {{ __('Library inventory') }}
        </div>
        <div class="w-12"></div>
    </div>

    <div class="flex items-center gap-2 border-b border-ink/10 bg-paper/50 px-3 py-2 text-xs text-ink-soft">
        <span class="rounded bg-moss/10 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-moss uppercase">{{ __('Path') }}</span>
        <span class="font-medium text-ink">{{ __('Teacher Desk') }}</span>
        <span class="opacity-40">›</span>
        <span>{{ __('Lesson files') }}</span>
    </div>

    @if ($this->slides->isEmpty())
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <span class="file-glyph mb-3 opacity-50" aria-hidden="true"></span>
            <p class="font-display text-lg font-semibold text-ink">{{ __('No lessons on the shelf yet') }}</p>
            <p class="mt-1 max-w-sm text-sm text-ink-soft">{{ __('Publish your first HTML file above — it shows up for students instantly.') }}</p>
        </div>
    @else
        <ul class="divide-y divide-ink/5">
            @foreach ($this->slides as $slide)
                <li class="explorer-row px-3 py-3 sm:px-4" wire:key="slide-{{ $slide->id }}">
                    @if ($editingId === $slide->id)
                        <form wire:submit="update" class="space-y-3 rounded-xl border border-moss/20 bg-moss/[0.04] p-3">
                            <div class="grid gap-3 sm:grid-cols-3">
                                <flux:field>
                                    <flux:label>{{ __('Name') }}</flux:label>
                                    <flux:input wire:model="editTitle" type="text" required />
                                    <flux:error name="editTitle" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('Folder') }}</flux:label>
                                    <flux:select wire:model="editCategoryId">
                                        <flux:select.option value="">{{ __('Unfiled') }}</flux:select.option>
                                        @foreach ($this->categories as $category)
                                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="editCategoryId" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('Sort order') }}</flux:label>
                                    <flux:input wire:model="editSortOrder" type="number" min="0" />
                                    <flux:error name="editSortOrder" />
                                </flux:field>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" class="rounded-lg bg-moss px-3 py-1.5 text-xs font-semibold text-white hover:bg-moss-bright">
                                    {{ __('Save') }}
                                </button>
                                <button type="button" wire:click="cancelEditing" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-ink-soft hover:bg-ink/5">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="file-glyph !size-10 shrink-0 scale-90" aria-hidden="true"></span>
                                <div class="min-w-0 space-y-1">
                                    <p class="truncate font-medium text-ink">{{ $slide->title }}</p>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-ink-soft">
                                        @if ($slide->category)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-folder/15 px-2 py-0.5 font-semibold text-folder-deep">
                                                <span class="folder-glyph !size-4 scale-50" aria-hidden="true"></span>
                                                {{ $slide->category->name }}
                                            </span>
                                        @else
                                            <span class="rounded-full bg-ink/5 px-2 py-0.5">{{ __('Unfiled') }}</span>
                                        @endif
                                        <span>{{ $slide->created_at?->toFormattedDateString() }}</span>
                                        <span class="tabular-nums">#{{ $slide->sort_order }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1.5 sm:justify-end">
                                <a
                                    href="{{ route('slides.show', $slide) }}"
                                    target="_blank"
                                    class="rounded-lg border border-ink/10 bg-white px-2.5 py-1.5 text-xs font-semibold text-ink transition hover:border-moss/30 hover:text-moss"
                                >
                                    {{ __('Preview') }}
                                </a>
                                <button
                                    type="button"
                                    wire:click="startEditing({{ $slide->id }})"
                                    class="rounded-lg border border-ink/10 bg-white px-2.5 py-1.5 text-xs font-semibold text-ink transition hover:border-moss/30 hover:text-moss"
                                >
                                    {{ __('Rename') }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="delete({{ $slide->id }})"
                                    wire:confirm="{{ __('Delete this slide?') }}"
                                    class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-100"
                                >
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        <footer class="flex items-center justify-between gap-3 border-t border-ink/10 bg-gradient-to-r from-ink to-ink-soft px-3 py-2 text-[11px] text-white/90 sm:text-xs">
            <span>{{ trans_choice(':count lesson on shelf|:count lessons on shelf', $this->slides->count(), ['count' => $this->slides->count()]) }}</span>
            <span class="hidden sm:inline">{{ __('Ready · Teacher publish mode') }}</span>
        </footer>
    @endif
</div>
