<?php

use App\Models\Category;
use App\Models\Slide;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts::public')] #[Title('Course Library')] class extends Component {
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public ?string $category = null;

    #[Url]
    public string $view = 'icons';

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

    #[Computed]
    public function uncategorizedCount(): int
    {
        return Slide::query()->whereNull('category_id')->count();
    }

    #[Computed]
    public function totalCount(): int
    {
        return Slide::query()->count();
    }

    /**
     * @return Collection<int, Slide>
     */
    #[Computed]
    public function slides(): Collection
    {
        return Slide::query()
            ->with('category')
            ->when($this->search !== '', function (Builder $query): void {
                $query->where('title', 'like', '%'.$this->search.'%');
            })
            ->when($this->category === 'uncategorized', function (Builder $query): void {
                $query->whereNull('category_id');
            })
            ->when(filled($this->category) && $this->category !== 'uncategorized', function (Builder $query): void {
                $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $this->category));
            })
            ->orderBy('sort_order')
            ->orderByDesc('lesson_date')
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function currentFolderLabel(): string
    {
        if ($this->category === 'uncategorized') {
            return __('Unfiled');
        }

        if (filled($this->category)) {
            return $this->categories->firstWhere('slug', $this->category)?->name ?? __('Course Library');
        }

        return __('All modules');
    }

    public function selectFolder(?string $slug = null): void
    {
        $this->category = $slug;
    }

    public function setView(string $view): void
    {
        $this->view = in_array($view, ['icons', 'details'], true) ? $view : 'icons';
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'category');
    }
}; ?>

<div class="flex h-[min(78vh,820px)] min-h-[520px] flex-col lg:h-[min(82vh,860px)]">
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-ink/10 bg-white/80 px-3 py-2.5">
        <div class="flex items-center gap-1">
            <button
                type="button"
                wire:click="clearFilters"
                class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-ink-soft transition hover:bg-ink/5 hover:text-ink"
                title="{{ __('Up to library root') }}"
            >
                ↑ {{ __('Up') }}
            </button>
            <span class="hidden h-4 w-px bg-ink/10 sm:block"></span>
            <button
                type="button"
                wire:click="setView('icons')"
                @class([
                    'rounded-md px-2.5 py-1.5 text-xs font-semibold transition',
                    'bg-moss/10 text-moss' => $view === 'icons',
                    'text-ink-soft hover:bg-ink/5 hover:text-ink' => $view !== 'icons',
                ])
            >
                {{ __('Icons') }}
            </button>
            <button
                type="button"
                wire:click="setView('details')"
                @class([
                    'rounded-md px-2.5 py-1.5 text-xs font-semibold transition',
                    'bg-moss/10 text-moss' => $view === 'details',
                    'text-ink-soft hover:bg-ink/5 hover:text-ink' => $view !== 'details',
                ])
            >
                {{ __('Details') }}
            </button>
        </div>

        <div class="ms-auto flex min-w-[12rem] flex-1 items-center gap-2 sm:max-w-sm">
            <label class="sr-only" for="library-search">{{ __('Search') }}</label>
            <div class="relative w-full">
                <span class="pointer-events-none absolute inset-y-0 start-2.5 flex items-center text-ink-soft/70">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.473 9.8l3.114 3.113a.75.75 0 1 0 1.06-1.06l-3.113-3.114A5.5 5.5 0 0 0 8.5 3Zm-4 5.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input
                    id="library-search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search lessons…') }}"
                    class="w-full rounded-lg border border-ink/10 bg-paper/80 py-2 pe-3 ps-8 text-sm text-ink outline-none transition placeholder:text-ink-soft/50 focus:border-moss/40 focus:bg-white focus:ring-2 focus:ring-moss/20"
                />
            </div>
            @if ($search !== '' || filled($category))
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="shrink-0 text-xs font-semibold text-moss hover:underline"
                >
                    {{ __('Clear') }}
                </button>
            @endif
        </div>
    </div>

    {{-- Address bar --}}
    <div class="flex items-center gap-2 border-b border-ink/10 bg-paper/50 px-3 py-2 text-xs sm:text-sm">
        <span class="rounded bg-moss/10 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-moss uppercase">{{ __('Path') }}</span>
        <nav class="flex min-w-0 flex-wrap items-center gap-1.5 text-ink-soft" aria-label="{{ __('Breadcrumb') }}">
            <button type="button" wire:click="selectFolder(null)" class="font-medium text-ink transition hover:text-moss">
                {{ __('Course Library') }}
            </button>
            @if (filled($category))
                <span class="opacity-40">›</span>
                <span class="truncate font-medium text-ink">{{ $this->currentFolderLabel }}</span>
            @endif
            @if ($search !== '')
                <span class="opacity-40">›</span>
                <span class="truncate italic">{{ __('Search') }}: “{{ $search }}”</span>
            @endif
        </nav>
    </div>

    <div class="flex min-h-0 flex-1">
        {{-- Sidebar navigation tree --}}
        <aside class="hidden w-56 shrink-0 flex-col border-e border-ink/10 bg-gradient-to-b from-white to-paper/80 sm:flex md:w-64">
            <div class="border-b border-ink/5 px-3 py-3">
                <p class="text-[10px] font-bold tracking-[0.18em] text-ink-soft uppercase">{{ __('Quick access') }}</p>
            </div>

            <nav class="flex-1 space-y-0.5 overflow-y-auto p-2" aria-label="{{ __('Folders') }}">
                <button
                    type="button"
                    wire:click="selectFolder(null)"
                    @class([
                        'flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-start text-sm transition',
                        'bg-moss/12 font-semibold text-moss ring-1 ring-moss/20' => blank($category),
                        'text-ink hover:bg-ink/5' => filled($category),
                    ])
                >
                    <span class="folder-glyph !size-8 scale-75" aria-hidden="true"></span>
                    <span class="min-w-0 flex-1 truncate">{{ __('All modules') }}</span>
                    <span class="text-xs tabular-nums opacity-60">{{ $this->totalCount }}</span>
                </button>

                <p class="px-2.5 pt-3 pb-1 text-[10px] font-bold tracking-[0.18em] text-ink-soft uppercase">
                    {{ __('Course folders') }}
                </p>

                @forelse ($this->categories as $item)
                    <button
                        type="button"
                        wire:click="selectFolder('{{ $item->slug }}')"
                        wire:key="folder-{{ $item->id }}"
                        @class([
                            'explorer-row flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-start text-sm transition',
                            'bg-moss/12 font-semibold text-moss ring-1 ring-moss/20' => $category === $item->slug,
                            'text-ink hover:bg-ink/5' => $category !== $item->slug,
                        ])
                    >
                        <span class="folder-glyph !size-8 scale-75" aria-hidden="true"></span>
                        <span class="min-w-0 flex-1 truncate">{{ $item->name }}</span>
                        <span class="text-xs tabular-nums opacity-60">{{ $item->slides_count }}</span>
                    </button>
                @empty
                    <p class="px-2.5 py-2 text-xs text-ink-soft">{{ __('No course folders yet.') }}</p>
                @endforelse

                @if ($this->uncategorizedCount > 0)
                    <button
                        type="button"
                        wire:click="selectFolder('uncategorized')"
                        @class([
                            'explorer-row mt-1 flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-start text-sm transition',
                            'bg-moss/12 font-semibold text-moss ring-1 ring-moss/20' => $category === 'uncategorized',
                            'text-ink hover:bg-ink/5' => $category !== 'uncategorized',
                        ])
                    >
                        <span class="folder-glyph !size-8 scale-75 opacity-70" aria-hidden="true"></span>
                        <span class="min-w-0 flex-1 truncate">{{ __('Unfiled') }}</span>
                        <span class="text-xs tabular-nums opacity-60">{{ $this->uncategorizedCount }}</span>
                    </button>
                @endif
            </nav>

            <div class="border-t border-ink/10 bg-ink/[0.03] px-3 py-3">
                <p class="font-display text-sm font-semibold text-ink">{{ __('Learning shelf') }}</p>
                <p class="mt-0.5 text-xs leading-relaxed text-ink-soft">
                    {{ __('Browse modules like folders. Double-click energy optional — one click opens the lesson.') }}
                </p>
            </div>
        </aside>

        {{-- Main files pane --}}
        <section class="flex min-w-0 flex-1 flex-col bg-panel">
            {{-- Mobile folder chips --}}
            <div class="flex gap-2 overflow-x-auto border-b border-ink/10 px-3 py-2 sm:hidden">
                <button
                    type="button"
                    wire:click="selectFolder(null)"
                    @class([
                        'shrink-0 rounded-full px-3 py-1 text-xs font-semibold',
                        'bg-moss text-white' => blank($category),
                        'bg-paper text-ink' => filled($category),
                    ])
                >
                    {{ __('All') }}
                </button>
                @foreach ($this->categories as $item)
                    <button
                        type="button"
                        wire:click="selectFolder('{{ $item->slug }}')"
                        @class([
                            'shrink-0 rounded-full px-3 py-1 text-xs font-semibold',
                            'bg-moss text-white' => $category === $item->slug,
                            'bg-paper text-ink' => $category !== $item->slug,
                        ])
                    >
                        {{ $item->name }}
                    </button>
                @endforeach
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-3 sm:p-4">
                @if ($this->slides->isEmpty())
                    <div class="flex h-full min-h-64 flex-col items-center justify-center rounded-xl border border-dashed border-ink/15 bg-paper/40 px-6 text-center">
                        <span class="folder-glyph mb-3 opacity-60" aria-hidden="true"></span>
                        <p class="font-display text-lg font-semibold text-ink">{{ __('This folder is empty') }}</p>
                        <p class="mt-1 max-w-sm text-sm text-ink-soft">
                            {{ __('Check back after your teacher drops HTML lessons into the library.') }}
                        </p>
                    </div>
                @elseif ($view === 'icons')
                    <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
                        @foreach ($this->slides as $slide)
                            <li class="explorer-row" wire:key="slide-icon-{{ $slide->id }}">
                                <a
                                    href="{{ route('slides.show', $slide) }}"
                                    wire:navigate
                                    class="group flex h-full flex-col items-center rounded-xl border border-transparent bg-transparent p-3 text-center transition hover:-translate-y-0.5 hover:border-moss/20 hover:bg-white hover:shadow-[0_12px_28px_-18px_rgb(20_54_58_/_0.45)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-moss/40"
                                >
                                    <span class="file-glyph mb-2 transition duration-300 group-hover:scale-105" aria-hidden="true"></span>
                                    <span class="line-clamp-2 text-sm font-medium text-ink group-hover:text-moss">
                                        {{ $slide->title }}
                                    </span>
                                    <span class="mt-1 text-[11px] text-ink-soft">
                                        {{ $slide->lesson_date?->timezone(config('app.timezone'))->format('M j, Y') }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="overflow-hidden rounded-xl border border-ink/10 bg-white">
                        <div class="grid grid-cols-[minmax(0,1fr)_7rem_8rem] gap-2 border-b border-ink/10 bg-paper/80 px-3 py-2 text-[11px] font-bold tracking-wide text-ink-soft uppercase sm:grid-cols-[minmax(0,1fr)_9rem_8rem_7rem]">
                            <span>{{ __('Name') }}</span>
                            <span class="hidden sm:block">{{ __('Module') }}</span>
                            <span>{{ __('Lesson date') }}</span>
                            <span class="text-end">{{ __('Open') }}</span>
                        </div>
                        <ul class="divide-y divide-ink/5">
                            @foreach ($this->slides as $slide)
                                <li class="explorer-row" wire:key="slide-detail-{{ $slide->id }}">
                                    <div class="grid grid-cols-[minmax(0,1fr)_7rem_8rem] items-center gap-2 px-3 py-2.5 transition hover:bg-moss/[0.04] sm:grid-cols-[minmax(0,1fr)_9rem_8rem_7rem]">
                                        <a href="{{ route('slides.show', $slide) }}" class="flex min-w-0 items-center gap-2.5" wire:navigate>
                                            <span class="file-glyph !size-9 shrink-0 scale-75" aria-hidden="true"></span>
                                            <span class="truncate text-sm font-medium text-ink hover:text-moss">{{ $slide->title }}</span>
                                        </a>
                                        <span class="hidden truncate text-xs text-ink-soft sm:block">
                                            {{ $slide->category?->name ?? __('Unfiled') }}
                                        </span>
                                        <span class="text-xs text-ink-soft">
                                            {{ $slide->lesson_date?->timezone(config('app.timezone'))->format('M j, Y') }}
                                        </span>
                                        <div class="flex justify-end gap-1">
                                            <a
                                                href="{{ route('slides.show', $slide) }}"
                                                class="rounded-md bg-ink px-2 py-1 text-[11px] font-semibold text-white hover:bg-ink-soft"
                                                wire:navigate
                                            >
                                                {{ __('Open') }}
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Status bar --}}
            <footer class="flex items-center justify-between gap-3 border-t border-ink/10 bg-gradient-to-r from-ink to-ink-soft px-3 py-2 text-[11px] text-white/90 sm:text-xs">
                <span>
                    {{ trans_choice(':count item|:count items', $this->slides->count(), ['count' => $this->slides->count()]) }}
                    · {{ $this->currentFolderLabel }}
                </span>
                <span class="hidden sm:inline">{{ __('Ready · LMS library mode') }}</span>
            </footer>
        </section>
    </div>
</div>
