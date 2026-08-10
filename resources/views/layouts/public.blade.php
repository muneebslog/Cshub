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
    <body class="campus-shell min-h-screen text-ink antialiased scheme-light">
        <div class="campus-grid min-h-screen">
            <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-3 py-4 sm:px-5 sm:py-6 lg:px-8">
                <header class="mb-4 flex flex-wrap items-end justify-between gap-4 px-1">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold tracking-[0.22em] text-moss uppercase">
                            {{ __('Campus course library') }}
                        </p>
                        <a href="{{ route('home') }}" class="group inline-flex items-baseline gap-2" wire:navigate>
                            <span class="font-display text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                                {{ config('app.name', 'CS Hub') }}
                            </span>
                            <span class="translate-y-0.5 text-sm font-medium text-ink-soft opacity-80 transition group-hover:opacity-100">
                                {{ __('Explorer') }}
                            </span>
                        </a>
                    </div>

                    <nav class="flex items-center gap-2">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="rounded-lg border border-ink/10 bg-white/70 px-3 py-2 text-sm font-medium text-ink shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:border-moss/30 hover:bg-white"
                                wire:navigate
                            >
                                {{ __('Teacher desk') }}
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="rounded-lg bg-ink px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-ink-soft"
                                data-test="teacher-login"
                            >
                                {{ __('Teacher login') }}
                            </a>
                        @endauth
                    </nav>
                </header>

                <div class="explorer-window flex min-h-0 flex-1 flex-col">
                    <div class="explorer-titlebar">
                        <div class="explorer-traffic" aria-hidden="true">
                            <span class="bg-[#e26d5c]"></span>
                            <span class="bg-[#f0c75e]"></span>
                            <span class="bg-[#6bbf8a]"></span>
                        </div>
                        <div class="min-w-0 flex-1 text-center text-sm font-medium text-ink-soft">
                            {{ __('Course Library') }} — {{ config('app.name', 'CS Hub') }}
                        </div>
                        <div class="w-12"></div>
                    </div>

                    <div class="min-h-0 flex-1">
                        {{ $slot }}
                    </div>
                </div>

                <p class="mt-3 px-1 text-center text-xs text-ink-soft/80">
                    {{ __('Open slides in-browser — no Drive downloads, no Notion text dump.') }}
                </p>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
