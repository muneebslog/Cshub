<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
        {{-- Campus teacher desk is a light surface; keep Flux from applying dark text on light panels. --}}
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
    <body class="teacher-desk min-h-screen bg-paper text-ink antialiased scheme-light">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-ink/10 bg-white/90 text-ink backdrop-blur-md">
            <flux:sidebar.header>
                <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-col gap-0.5 px-1 text-ink" wire:navigate>
                    <span class="text-[10px] font-bold tracking-[0.2em] text-moss uppercase">{{ __('Teacher desk') }}</span>
                    <span class="font-display truncate text-lg font-semibold text-ink">{{ config('app.name', 'CS Hub') }}</span>
                </a>
                <flux:sidebar.collapse class="lg:hidden !text-ink" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="text-ink">
                <flux:sidebar.group :heading="__('Library tools')" class="grid !text-ink">
                    <flux:sidebar.item
                        icon="document-plus"
                        :href="route('admin.slides')"
                        :current="request()->routeIs('admin.slides')"
                        class="!text-ink hover:!text-moss data-current:!text-moss"
                        wire:navigate
                    >
                        {{ __('Lesson files') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="folder"
                        :href="route('admin.categories')"
                        :current="request()->routeIs('admin.categories')"
                        class="!text-ink hover:!text-moss data-current:!text-moss"
                        wire:navigate
                    >
                        {{ __('Course folders') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="globe-alt"
                        :href="route('home')"
                        class="!text-ink hover:!text-moss"
                        wire:navigate
                    >
                        {{ __('Student library') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="mx-2 mb-3 rounded-xl border border-ink/10 bg-gradient-to-br from-moss/10 to-file-soft/40 p-3 text-ink">
                <p class="font-display text-sm font-semibold text-ink">{{ __('Staff tip') }}</p>
                <p class="mt-1 text-xs leading-relaxed text-ink-soft">
                    {{ __('Drop HTML lessons into folders — students browse them like a campus drive.') }}
                </p>
            </div>

            <div class="text-ink">
                <x-desktop-user-menu class="hidden lg:block !text-ink" :name="auth()->user()->name" />
            </div>
        </flux:sidebar>

        <flux:header class="border-b border-ink/10 bg-white/80 text-ink backdrop-blur-md lg:hidden">
            <flux:sidebar.toggle class="lg:hidden !text-ink" icon="bars-2" inset="left" />

            <div class="ms-2 min-w-0 text-ink">
                <p class="text-[10px] font-bold tracking-[0.18em] text-moss uppercase">{{ __('Teacher desk') }}</p>
                <p class="truncate text-sm font-semibold text-ink">{{ $title ?? config('app.name') }}</p>
            </div>

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                    class="!text-ink"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
