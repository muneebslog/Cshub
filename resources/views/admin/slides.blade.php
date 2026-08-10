<x-layouts::app :title="__('Lesson files')">
    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-[10px] font-bold tracking-[0.2em] text-moss uppercase">{{ __('Teacher desk') }}</p>
                <h1 class="font-display text-3xl font-semibold tracking-tight text-ink">{{ __('Lesson files') }}</h1>
                <p class="mt-1 max-w-xl text-sm text-ink-soft">
                    {{ __('Upload HTML, pick the lesson date, and publish. Names come from the file.') }}
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
                <input type="hidden" name="title" id="title" value="{{ old('title') }}">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <label for="lesson_date" class="text-sm font-medium text-ink">{{ __('Lesson date') }}</label>
                        <input
                            id="lesson_date"
                            name="lesson_date"
                            type="date"
                            required
                            value="{{ old('lesson_date', now()->toDateString()) }}"
                            class="w-full rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm text-ink outline-none focus:border-moss/40 focus:ring-2 focus:ring-moss/20"
                        />
                    </div>

                    <div class="grid gap-2">
                        <label for="category_id" class="text-sm font-medium text-ink">{{ __('Course folder') }}</label>
                        <div class="flex gap-2">
                            <select
                                id="category_id"
                                name="category_id"
                                class="min-w-0 flex-1 rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm text-ink outline-none focus:border-moss/40 focus:ring-2 focus:ring-moss/20"
                            >
                                <option value="">{{ __('Unfiled') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button
                                type="button"
                                id="open-folder-modal"
                                class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg border border-ink/15 bg-white text-lg font-semibold text-ink transition hover:border-moss/40 hover:text-moss"
                                title="{{ __('New folder') }}"
                                aria-label="{{ __('New folder') }}"
                            >
                                +
                            </button>
                        </div>
                    </div>
                </div>

                <div class="drop-zone p-5">
                    <div class="flex flex-col items-center gap-3 text-center sm:flex-row sm:text-start">
                        <span class="file-glyph shrink-0" aria-hidden="true"></span>
                        <div class="min-w-0 flex-1 space-y-1">
                            <p class="font-display text-base font-semibold text-ink">{{ __('Choose an .html lesson file') }}</p>
                            <p class="text-xs text-ink-soft">{{ __('Lesson name is taken from the filename — max 30MB.') }}</p>
                            <p id="selected-file-name" class="truncate text-xs font-semibold text-moss"></p>
                            <p id="derived-lesson-name" class="truncate text-xs text-ink-soft"></p>
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
                        {{ __('Manage all folders') }}
                    </a>
                </div>
            </form>

            <dialog
                id="folder-create-dialog"
                class="w-[min(100%,24rem)] rounded-2xl border border-ink/10 bg-paper p-0 text-ink shadow-xl backdrop:bg-ink/40"
            >
                <form id="folder-create-form" method="dialog" class="space-y-4 p-5">
                    <div>
                        <p class="text-[10px] font-bold tracking-[0.2em] text-moss uppercase">{{ __('New folder') }}</p>
                        <h2 class="font-display text-xl font-semibold text-ink">{{ __('Create course folder') }}</h2>
                        <p class="mt-1 text-sm text-ink-soft">{{ __('It appears in the list immediately.') }}</p>
                    </div>

                    <div class="grid gap-2">
                        <label for="new_folder_name" class="text-sm font-medium text-ink">{{ __('Folder name') }}</label>
                        <input
                            id="new_folder_name"
                            type="text"
                            required
                            maxlength="255"
                            placeholder="{{ __('e.g. Computer Science') }}"
                            class="w-full rounded-lg border border-ink/15 bg-white px-3 py-2 text-sm text-ink outline-none focus:border-moss/40 focus:ring-2 focus:ring-moss/20"
                        />
                        <p id="folder-create-error" class="hidden text-sm text-red-600" role="alert"></p>
                    </div>

                    <div class="flex flex-wrap justify-end gap-2">
                        <button
                            type="button"
                            id="cancel-folder-modal"
                            class="rounded-lg px-3 py-2 text-sm font-semibold text-ink-soft hover:bg-ink/5"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            id="folder-create-submit"
                            class="rounded-lg bg-moss px-3 py-2 text-sm font-semibold text-white hover:bg-moss-bright"
                        >
                            {{ __('Create') }}
                        </button>
                    </div>
                </form>
            </dialog>

            <script>
                (() => {
                    const form = document.getElementById('slide-upload-form');
                    const picker = document.getElementById('html_file_picker');
                    const contentInput = document.getElementById('html_content');
                    const nameInput = document.getElementById('original_filename');
                    const titleInput = document.getElementById('title');
                    const selectedLabel = document.getElementById('selected-file-name');
                    const derivedLabel = document.getElementById('derived-lesson-name');
                    const submitButton = document.getElementById('slide-upload-submit');
                    const categorySelect = document.getElementById('category_id');
                    const openFolderModal = document.getElementById('open-folder-modal');
                    const folderDialog = document.getElementById('folder-create-dialog');
                    const folderForm = document.getElementById('folder-create-form');
                    const folderNameInput = document.getElementById('new_folder_name');
                    const folderError = document.getElementById('folder-create-error');
                    const folderSubmit = document.getElementById('folder-create-submit');
                    const cancelFolderModal = document.getElementById('cancel-folder-modal');
                    const maxBytes = 30 * 1024 * 1024;
                    const categoryStoreUrl = @json(route('admin.categories.store'));
                    const csrfToken = @json(csrf_token());

                    if (! form || ! picker) {
                        return;
                    }

                    const titleFromFilename = (filename) => {
                        const base = filename.replace(/\\/g, '/').split('/').pop() || filename;

                        return base.replace(/\.(html?|HTML?)$/i, '').trim();
                    };

                    picker.addEventListener('change', async () => {
                        const file = picker.files?.[0];

                        if (! file) {
                            contentInput.value = '';
                            nameInput.value = '';
                            titleInput.value = '';
                            selectedLabel.textContent = '';
                            derivedLabel.textContent = '';
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
                        derivedLabel.textContent = '';

                        try {
                            contentInput.value = await file.text();
                            nameInput.value = file.name;
                            titleInput.value = titleFromFilename(file.name);
                            selectedLabel.textContent = file.name;
                            derivedLabel.textContent = @json(__('Lesson name:')) + ' ' + titleInput.value;
                        } catch (error) {
                            contentInput.value = '';
                            nameInput.value = '';
                            titleInput.value = '';
                            selectedLabel.textContent = '';
                            derivedLabel.textContent = '';
                            alert(@json(__('Could not read that HTML file. Please try again.')));
                        } finally {
                            submitButton.disabled = false;
                        }
                    });

                    form.addEventListener('submit', (event) => {
                        if (! contentInput.value || ! nameInput.value || ! titleInput.value) {
                            event.preventDefault();
                            alert(@json(__('Please choose an HTML lesson file.')));
                        }
                    });

                    openFolderModal?.addEventListener('click', () => {
                        folderError.classList.add('hidden');
                        folderError.textContent = '';
                        folderNameInput.value = '';
                        folderDialog.showModal();
                        folderNameInput.focus();
                    });

                    cancelFolderModal?.addEventListener('click', () => {
                        folderDialog.close();
                    });

                    folderForm?.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        const name = folderNameInput.value.trim();

                        if (! name) {
                            return;
                        }

                        folderSubmit.disabled = true;
                        folderError.classList.add('hidden');
                        folderError.textContent = '';

                        try {
                            const response = await fetch(categoryStoreUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({ name }),
                            });

                            const payload = await response.json().catch(() => ({}));

                            if (! response.ok) {
                                const message = payload.message
                                    || payload.errors?.name?.[0]
                                    || @json(__('Could not create that folder.'));
                                folderError.textContent = message;
                                folderError.classList.remove('hidden');
                                return;
                            }

                            const option = document.createElement('option');
                            option.value = String(payload.id);
                            option.textContent = payload.name;
                            option.selected = true;
                            categorySelect.appendChild(option);
                            categorySelect.value = String(payload.id);
                            folderDialog.close();
                        } catch (error) {
                            folderError.textContent = @json(__('Could not create that folder.'));
                            folderError.classList.remove('hidden');
                        } finally {
                            folderSubmit.disabled = false;
                        }
                    });
                })();
            </script>
        </div>

        <livewire:pages::admin.slide-inventory />
    </div>
</x-layouts::app>
