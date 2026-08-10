<x-layouts::app :title="__('Lesson files')">
    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-[10px] font-bold tracking-[0.2em] text-moss uppercase">{{ __('Teacher desk') }}</p>
                <h1 class="font-display text-3xl font-semibold tracking-tight text-ink">{{ __('Lesson files') }}</h1>
                <p class="mt-1 max-w-xl text-sm text-ink-soft">
                    {{ __('Upload HTML like dropping files onto a network drive. Dates stamp themselves.') }}
                </p>
            </div>

            <div class="flex gap-2">
                <div class="rounded-xl border border-ink/10 bg-white/70 px-3 py-2 text-center shadow-sm backdrop-blur">
                    <p class="text-[10px] font-bold tracking-wide text-ink-soft uppercase">{{ __('Files') }}</p>
                    <p class="font-display text-xl font-semibold text-ink tabular-nums">{{ $slideCount }}</p>
                </div>
                <div class="rounded-xl border border-ink/10 bg-white/70 px-3 py-2 text-center shadow-sm backdrop-blur">
                    <p class="text-[10px] font-bold tracking-wide text-ink-soft uppercase">{{ __('Folders') }}</p>
                    <p class="font-display text-xl font-semibold text-ink tabular-nums">{{ $categoryCount }}</p>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-moss/25 bg-moss/10 px-4 py-3 text-sm font-medium text-moss" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <ul class="list-disc space-y-1 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="desk-panel">
            <div class="desk-titlebar">
                <div class="explorer-traffic" aria-hidden="true">
                    <span class="bg-[#e26d5c]"></span>
                    <span class="bg-[#f0c75e]"></span>
                    <span class="bg-[#6bbf8a]"></span>
                </div>
                <div class="min-w-0 flex-1 text-center text-sm font-medium text-ink-soft">
                    {{ __('New lesson · Drop zone') }}
                </div>
                <div class="w-12"></div>
            </div>

            <form
                id="slide-upload-form"
                method="POST"
                action="{{ route('admin.slides.store') }}"
                class="space-y-4 p-4 sm:p-6"
            >
                @csrf

                <input type="hidden" name="html_content" id="html_content" value="">
                <input type="hidden" name="original_filename" id="original_filename" value="">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <label for="title" class="text-sm font-medium text-ink">{{ __('Lesson name') }}</label>
                        <input
                            id="title"
                            name="title"
                            type="text"
                            required
                            value="{{ old('title') }}"
                            placeholder="{{ __('e.g. Week 3 — Sorting') }}"
                            class="w-full rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm text-ink outline-none focus:border-moss/40 focus:ring-2 focus:ring-moss/20"
                        />
                    </div>

                    <div class="grid gap-2">
                        <label for="category_id" class="text-sm font-medium text-ink">{{ __('Course folder') }}</label>
                        <select
                            id="category_id"
                            name="category_id"
                            class="w-full rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm text-ink outline-none focus:border-moss/40 focus:ring-2 focus:ring-moss/20"
                        >
                            <option value="">{{ __('Unfiled') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="drop-zone p-5">
                    <div class="flex flex-col items-center gap-3 text-center sm:flex-row sm:text-start">
                        <span class="file-glyph shrink-0" aria-hidden="true"></span>
                        <div class="min-w-0 flex-1 space-y-1">
                            <p class="font-display text-base font-semibold text-ink">{{ __('Choose an .html lesson file') }}</p>
                            <p class="text-xs text-ink-soft">{{ __('Students will open it in-browser — max 30MB.') }}</p>
                            <p id="selected-file-name" class="truncate text-xs font-semibold text-moss"></p>
                        </div>
                        <input
                            id="html_file_picker"
                            type="file"
                            accept=".html,.htm,text/html"
                            class="block w-full max-w-xs text-sm text-ink file:me-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-ink file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-ink-soft"
                        />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        id="slide-upload-submit"
                        class="rounded-lg bg-moss px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-moss-bright"
                    >
                        {{ __('Publish to library') }}
                    </button>
                    <a href="{{ route('admin.categories') }}" class="text-sm font-medium text-ink-soft underline-offset-2 hover:text-moss hover:underline">
                        {{ __('Need a folder first?') }}
                    </a>
                </div>
            </form>

            <script>
                (() => {
                    const form = document.getElementById('slide-upload-form');
                    const picker = document.getElementById('html_file_picker');
                    const contentInput = document.getElementById('html_content');
                    const nameInput = document.getElementById('original_filename');
                    const selectedLabel = document.getElementById('selected-file-name');
                    const submitButton = document.getElementById('slide-upload-submit');
                    const maxBytes = 30 * 1024 * 1024;

                    if (! form || ! picker) {
                        return;
                    }

                    picker.addEventListener('change', async () => {
                        const file = picker.files?.[0];

                        if (! file) {
                            contentInput.value = '';
                            nameInput.value = '';
                            selectedLabel.textContent = '';
                            return;
                        }

                        const extension = file.name.split('.').pop()?.toLowerCase();

                        if (! ['html', 'htm'].includes(extension || '')) {
                            alert(@json(__('The file must be an .html or .htm document.')));
                            picker.value = '';
                            return;
                        }

                        if (file.size > maxBytes) {
                            alert(@json(__('The HTML file may not be larger than 30MB.')));
                            picker.value = '';
                            return;
                        }

                        submitButton.disabled = true;
                        selectedLabel.textContent = @json(__('Reading file…'));

                        try {
                            contentInput.value = await file.text();
                            nameInput.value = file.name;
                            selectedLabel.textContent = file.name;
                        } catch (error) {
                            contentInput.value = '';
                            nameInput.value = '';
                            selectedLabel.textContent = '';
                            alert(@json(__('Could not read that HTML file. Please try again.')));
                        } finally {
                            submitButton.disabled = false;
                        }
                    });

                    form.addEventListener('submit', (event) => {
                        if (! contentInput.value || ! nameInput.value) {
                            event.preventDefault();
                            alert(@json(__('Please choose an HTML lesson file.')));
                        }
                    });
                })();
            </script>
        </div>

        <livewire:pages::admin.slide-inventory />
    </div>
</x-layouts::app>
