<?php

use App\Models\Category;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Course folders')] class extends Component {
    public string $name = '';

    public int $sort_order = 0;

    public ?int $editingId = null;

    public string $editName = '';

    public int $editSortOrder = 0;

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->withCount('slides')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        Category::query()->create($validated);

        $this->reset('name', 'sort_order');
        unset($this->categories);

        Flux::toast(variant: 'success', text: __('Category created.'));
    }

    public function startEditing(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);

        $this->editingId = $category->id;
        $this->editName = $category->name;
        $this->editSortOrder = $category->sort_order;
    }

    public function cancelEditing(): void
    {
        $this->reset('editingId', 'editName', 'editSortOrder');
    }

    public function update(): void
    {
        $validated = $this->validate([
            'editingId' => ['required', 'integer', 'exists:categories,id'],
            'editName' => ['required', 'string', 'max:255'],
            'editSortOrder' => ['required', 'integer', 'min:0'],
        ]);

        $category = Category::query()->findOrFail($validated['editingId']);

        $category->update([
            'name' => $validated['editName'],
            'sort_order' => $validated['editSortOrder'],
        ]);

        $this->cancelEditing();
        unset($this->categories);

        Flux::toast(variant: 'success', text: __('Category updated.'));
    }

    public function delete(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);
        $category->delete();

        if ($this->editingId === $categoryId) {
            $this->cancelEditing();
        }

        unset($this->categories);

        Flux::toast(variant: 'success', text: __('Category deleted. Slides in it are now uncategorized.'));
    }
}; ?>

<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[10px] font-bold tracking-[0.2em] text-moss uppercase">{{ __('Teacher desk') }}</p>
            <h1 class="font-display text-3xl font-semibold tracking-tight text-ink">{{ __('Course folders') }}</h1>
            <p class="mt-1 max-w-xl text-sm text-ink-soft">
                {{ __('Build the left-rail modules students click through in the library explorer.') }}
            </p>
        </div>

        <div class="rounded-xl border border-ink/10 bg-white/70 px-4 py-2 text-center shadow-sm backdrop-blur">
            <p class="text-[10px] font-bold tracking-wide text-ink-soft uppercase">{{ __('Folders') }}</p>
            <p class="font-display text-xl font-semibold text-ink tabular-nums">{{ $this->categories->count() }}</p>
        </div>
    </div>

    <div class="desk-panel">
        <div class="desk-titlebar">
            <div class="explorer-traffic" aria-hidden="true">
                <span class="bg-[#e26d5c]"></span>
                <span class="bg-[#f0c75e]"></span>
                <span class="bg-[#6bbf8a]"></span>
            </div>
            <div class="min-w-0 flex-1 text-center text-sm font-medium text-ink-soft">
                {{ __('Create folder') }}
            </div>
            <div class="w-12"></div>
        </div>

        <form wire:submit="save" class="grid gap-4 p-4 sm:grid-cols-[1fr_8rem_auto] sm:items-end sm:p-6">
            <flux:field>
                <flux:label>{{ __('Folder name') }}</flux:label>
                <flux:input wire:model="name" type="text" required placeholder="{{ __('e.g. Algorithms') }}" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Order') }}</flux:label>
                <flux:input wire:model="sort_order" type="number" min="0" />
                <flux:error name="sort_order" />
            </flux:field>

            <button
                type="submit"
                class="rounded-lg bg-moss px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-moss-bright"
            >
                {{ __('Add folder') }}
            </button>
        </form>
    </div>

    <div class="desk-panel">
        <div class="desk-titlebar">
            <div class="explorer-traffic" aria-hidden="true">
                <span class="bg-[#e26d5c]"></span>
                <span class="bg-[#f0c75e]"></span>
                <span class="bg-[#6bbf8a]"></span>
            </div>
            <div class="min-w-0 flex-1 text-center text-sm font-medium text-ink-soft">
                {{ __('Folder shelf') }}
            </div>
            <div class="w-12"></div>
        </div>

        <div class="flex items-center gap-2 border-b border-ink/10 bg-paper/50 px-3 py-2 text-xs text-ink-soft">
            <span class="rounded bg-moss/10 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-moss uppercase">{{ __('Path') }}</span>
            <span class="font-medium text-ink">{{ __('Teacher Desk') }}</span>
            <span class="opacity-40">›</span>
            <span>{{ __('Course folders') }}</span>
        </div>

        @if ($this->categories->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <span class="folder-glyph mb-3 opacity-60" aria-hidden="true"></span>
                <p class="font-display text-lg font-semibold text-ink">{{ __('No folders yet') }}</p>
                <p class="mt-1 max-w-sm text-sm text-ink-soft">{{ __('Create modules like Operating Systems, Networks, or Lab Work.') }}</p>
            </div>
        @else
            <ul class="grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->categories as $category)
                    <li class="explorer-row" wire:key="category-{{ $category->id }}">
                        @if ($editingId === $category->id)
                            <form wire:submit="update" class="h-full space-y-3 rounded-xl border border-moss/25 bg-moss/[0.05] p-4">
                                <flux:field>
                                    <flux:label>{{ __('Name') }}</flux:label>
                                    <flux:input wire:model="editName" type="text" required />
                                    <flux:error name="editName" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('Order') }}</flux:label>
                                    <flux:input wire:model="editSortOrder" type="number" min="0" />
                                    <flux:error name="editSortOrder" />
                                </flux:field>

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
                            <div class="group flex h-full flex-col rounded-xl border border-ink/10 bg-gradient-to-b from-white to-paper/80 p-4 transition hover:-translate-y-0.5 hover:border-folder/40 hover:shadow-[0_14px_30px_-20px_rgb(184_134_11_/_0.55)]">
                                <div class="flex items-start gap-3">
                                    <span class="folder-glyph shrink-0 transition duration-300 group-hover:scale-110" aria-hidden="true"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-display text-lg font-semibold text-ink">{{ $category->name }}</p>
                                        <p class="mt-1 text-xs text-ink-soft">
                                            {{ __('Order') }} #{{ $category->sort_order }}
                                            · {{ trans_choice(':count lesson|:count lessons', $category->slides_count, ['count' => $category->slides_count]) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-auto flex flex-wrap gap-1.5 pt-4">
                                    <button
                                        type="button"
                                        wire:click="startEditing({{ $category->id }})"
                                        class="rounded-lg border border-ink/10 bg-white px-2.5 py-1.5 text-xs font-semibold text-ink transition hover:border-moss/30 hover:text-moss"
                                    >
                                        {{ __('Rename') }}
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="delete({{ $category->id }})"
                                        wire:confirm="{{ __('Delete this category? Slides will become uncategorized.') }}"
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
                <span>{{ trans_choice(':count course folder|:count course folders', $this->categories->count(), ['count' => $this->categories->count()]) }}</span>
                <span class="hidden sm:inline">{{ __('Ready · Folder organize mode') }}</span>
            </footer>
        @endif
    </div>
</div>
