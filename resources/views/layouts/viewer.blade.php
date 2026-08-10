<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
        <script>
            (() => {
                const root = document.documentElement;
                const lockLight = () => {
                    if (root.classList.contains('dark') || ! root.classList.contains('light')) {
                        root.classList.remove('dark');
                        root.classList.add('light');
                    }
                };
                lockLight();
                new MutationObserver(lockLight).observe(root, { attributes: true, attributeFilter: ['class'] });
            })();
        </script>
    </head>
    <body class="campus-shell text-ink antialiased scheme-light">
        <div class="campus-grid flex h-dvh flex-col p-2 sm:p-3">
            <div class="explorer-window flex min-h-0 flex-1 flex-col">
                <div class="explorer-titlebar">
                    <div class="explorer-traffic" aria-hidden="true">
                        <span class="bg-[#e26d5c]"></span>
                        <span class="bg-[#f0c75e]"></span>
                        <span class="bg-[#6bbf8a]"></span>
                    </div>

                    <div class="flex min-w-0 flex-1 items-center justify-center gap-2 px-2">
                        <span class="hidden rounded bg-file-soft px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-file sm:inline">HTML</span>
                        <span class="truncate text-sm font-medium text-ink">
                            {{ $title ?? __('Slide') }}
                        </span>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5">
                        <a
                            href="{{ route('home') }}"
                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-ink-soft transition hover:bg-ink/5 hover:text-ink"
                            wire:navigate
                        >
                            {{ __('Back') }}
                        </a>
                        @isset($fileUrl)
                            <a
                                href="{{ $fileUrl }}"
                                target="_blank"
                                class="rounded-md bg-moss px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-moss-bright"
                            >
                                {{ __('Fullscreen') }}
                            </a>
                        @endisset
                    </div>
                </div>

                <div class="flex items-center gap-2 border-b border-ink/10 bg-paper/70 px-3 py-2 text-xs text-ink-soft">
                    <span class="font-medium text-ink">{{ __('Path') }}</span>
                    <span class="opacity-40">›</span>
                    <a href="{{ route('home') }}" class="hover:text-moss" wire:navigate>{{ __('Course Library') }}</a>
                    <span class="opacity-40">›</span>
                    <span class="truncate text-ink">{{ $title ?? __('Slide') }}</span>
                </div>

                <main class="min-h-0 flex-1 bg-white">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
