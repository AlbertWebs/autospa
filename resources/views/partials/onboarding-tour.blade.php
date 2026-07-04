@auth
    @if (auth()->user()->needsOnboarding())
        <div
            x-data="onboardingTour()"
            x-show="$store.onboarding.active"
            x-cloak
            class="fixed inset-0 z-[100]"
            @keydown.escape.window="$store.onboarding.skip()"
        >
            <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" @click="$store.onboarding.skip()"></div>

            <template x-if="$store.onboarding.currentStep">
                <div
                    class="asp-tour-highlight"
                    x-show="$store.onboarding.highlightStyle"
                    x-bind:style="$store.onboarding.highlightStyle"
                    x-transition.opacity
                ></div>
            </template>

            <div
                class="asp-tour-card"
                x-bind:style="$store.onboarding.cardStyle"
                x-transition
                @click.stop
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-primary">
                            Getting started · Step <span x-text="$store.onboarding.step + 1"></span> of <span x-text="$store.onboarding.steps.length"></span>
                        </p>
                        <h2 class="mt-1 font-display text-xl font-bold text-slate-900 dark:text-white" x-text="$store.onboarding.currentStep?.title"></h2>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-brand-surface-high dark:hover:text-slate-200"
                        @click="$store.onboarding.skip()"
                        aria-label="Skip tour"
                    >
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300" x-text="$store.onboarding.currentStep?.body"></p>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <button
                        type="button"
                        class="asp-btn asp-btn-ghost !px-3 text-sm"
                        @click="$store.onboarding.skip()"
                    >
                        Skip tour
                    </button>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="asp-btn asp-btn-secondary !px-4 text-sm"
                            x-show="$store.onboarding.step > 0"
                            @click="$store.onboarding.previous()"
                        >
                            Back
                        </button>
                        <button
                            type="button"
                            class="asp-btn asp-btn-primary !px-4 text-sm"
                            @click="$store.onboarding.next()"
                            x-text="$store.onboarding.isLastStep ? 'Finish' : 'Next'"
                        ></button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('onboarding').start(@js(config('onboarding')));
            });
        </script>
    @endif
@endauth
