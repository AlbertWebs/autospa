<x-layouts.app title="User Manual">
    <x-slot name="header">
        <span class="hidden sm:inline">Help</span>
    </x-slot>

    <div x-data="{ activeSection: @js($sections[0]['title'] ?? '') }">
        <x-ui.section-header eyebrow="Help">
            <button
                type="button"
                class="asp-btn asp-btn-secondary"
                @click="$store.onboarding.restart(@js(config('onboarding')))"
            >
                <span class="material-symbols-outlined text-lg">map</span>
                Start guided tour
            </button>
        </x-ui.section-header>

        <div class="grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <aside class="asp-panel h-fit lg:sticky lg:top-24">
                <div class="asp-panel-header">
                    <h2 class="asp-panel-title">Contents</h2>
                </div>
                <nav class="asp-panel-body !space-y-1 !p-4">
                    @foreach ($sections as $section)
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm transition"
                            :class="activeSection === @js($section['title'])
                                ? 'bg-indigo-500/10 font-semibold text-indigo-700 dark:text-indigo-300'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-brand-surface-high'"
                            @click="activeSection = @js($section['title'])"
                        >
                            <x-ui.nav-icon :name="$section['icon']" class="h-4 w-4 shrink-0 opacity-70" />
                            <span>{{ $section['title'] }}</span>
                        </button>
                    @endforeach
                </nav>
            </aside>

            <div class="space-y-4">
                @foreach ($sections as $section)
                    <section
                        x-show="activeSection === @js($section['title'])"
                        x-cloak
                        class="asp-panel"
                    >
                        <div class="asp-panel-header">
                            <div>
                                <div class="flex items-center gap-2">
                                    <x-ui.nav-icon :name="$section['icon']" class="h-5 w-5 text-brand-primary" />
                                    <h2 class="asp-panel-title">{{ $section['title'] }}</h2>
                                </div>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $section['summary'] }}</p>
                            </div>
                        </div>
                        <div class="asp-panel-body space-y-6">
                            @foreach ($section['topics'] as $topic)
                                <div>
                                    <h3 class="font-display text-base font-semibold text-slate-900 dark:text-white">
                                        {{ $topic['heading'] }}
                                    </h3>
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                                        {{ $topic['body'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
