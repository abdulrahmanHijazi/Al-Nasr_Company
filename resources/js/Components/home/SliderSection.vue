<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { tr } from '@/lib/locale';
import type { HomeSection } from '@/types';

/**
 * Image slider (like the hero carousel on felicitysolar.nl): big slides, arrows at the sides, dots at the
 * bottom, auto-play that pauses on hover/focus and is off for people who prefer reduced motion, swipe on touch.
 *
 * Designs: full (full-bleed photo with text over it), product (dark slide with the product picture beside
 * the text), cards (the same as full, but a rounded card inside the page container).
 */
const props = defineProps<{ section: HomeSection }>();

const c = computed(() => props.section.content ?? {});
const slides = computed<any[]>(() => (c.value.slides ?? []).filter((s: any) => s.image || s.product_image || tr(s.title)));
const design = computed(() => (['full', 'product', 'cards'].includes(props.section.design) ? props.section.design : 'full'));

const current = ref(0);
const paused = ref(false);
const reducedMotion = ref(false);
let timer: ReturnType<typeof setInterval> | undefined;

const interval = computed(() => Math.min(20, Math.max(2, Number(c.value.interval) || 6)) * 1000);
const autoplay = computed(() => c.value.autoplay !== false && slides.value.length > 1 && !reducedMotion.value);

function go(to: number) {
    const n = slides.value.length;
    if (n) current.value = (to + n) % n;
}
function step(delta: number) {
    go(current.value + delta);
    restart();
}
function restart() {
    clearInterval(timer);
    if (autoplay.value && !paused.value) timer = setInterval(() => go(current.value + 1), interval.value);
}

// swipe
let startX = 0;
function down(e: PointerEvent) {
    startX = e.clientX;
}
function up(e: PointerEvent) {
    const dx = e.clientX - startX;
    if (Math.abs(dx) > 50) step((dx < 0 ? 1 : -1) * (document.dir === 'rtl' ? -1 : 1));
}

onMounted(() => {
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    restart();
});
onBeforeUnmount(() => clearInterval(timer));
watch([paused, autoplay, interval], restart);

const heights = 'min-h-[26rem] sm:min-h-[30rem] lg:min-h-[34rem]';
</script>

<template>
    <section
        v-if="slides.length"
        class="slider-section"
        :class="design === 'cards' ? 'container-page py-6 sm:py-8' : ''"
        aria-roledescription="carousel"
        :aria-label="tr(section.title) || $t('Slider')"
        @mouseenter="paused = true"
        @mouseleave="paused = false"
        @focusin="paused = true"
        @focusout="paused = false"
    >
        <div
            class="relative isolate overflow-hidden bg-header text-on-header"
            :class="[design === 'cards' ? 'rounded-3xl shadow-xl' : '', heights]"
            @pointerdown="down"
            @pointerup="up"
        >
            <div
                v-for="(slide, i) in slides"
                :key="i"
                class="absolute inset-0 transition-all duration-700 ease-out motion-reduce:transition-none"
                :class="i === current ? 'translate-x-0 opacity-100' : 'pointer-events-none opacity-0 ltr:translate-x-6 rtl:-translate-x-6'"
                role="group"
                aria-roledescription="slide"
                :aria-label="`${i + 1} / ${slides.length}`"
                :aria-hidden="i !== current"
            >
                <!-- background -->
                <img
                    v-if="slide.image"
                    :src="slide.image"
                    alt=""
                    class="absolute inset-0 -z-10 h-full w-full object-cover"
                    :loading="i === 0 ? 'eager' : 'lazy'"
                    draggable="false"
                />
                <template v-if="design === 'product'">
                    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-header via-header to-primary/60" />
                    <div class="absolute -end-24 top-1/2 -z-10 size-[30rem] -translate-y-1/2 rounded-full bg-secondary/25 blur-[110px]" aria-hidden="true" />
                    <div class="bg-grid absolute inset-0 -z-10 opacity-60" aria-hidden="true" />
                </template>
                <div
                    v-else
                    class="absolute inset-0 -z-10 rtl:bg-gradient-to-l"
                    :class="slide.image ? 'bg-gradient-to-r from-header/90 via-header/55 to-header/10' : 'bg-gradient-to-br from-header to-primary'"
                />

                <div
                    class="container-page grid h-full items-center gap-6 py-14 sm:!px-20"
                    :class="[heights, design === 'product' ? 'lg:grid-cols-2 lg:gap-10' : '']"
                >
                    <div class="max-w-2xl">
                        <span
                            v-if="tr(slide.badge)"
                            class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-xs font-semibold backdrop-blur"
                        >
                            <span class="size-1.5 rounded-full bg-secondary" />{{ tr(slide.badge) }}
                        </span>
                        <h2 class="mt-4 text-3xl font-bold leading-[1.1] tracking-tight sm:text-4xl lg:text-5xl">{{ tr(slide.title) }}</h2>
                        <p v-if="tr(slide.subtitle)" class="mt-4 text-base leading-relaxed text-white/80 sm:text-lg">{{ tr(slide.subtitle) }}</p>
                        <Link v-if="slide.button_url && tr(slide.button_label)" :href="slide.button_url" class="btn btn-secondary btn-lg mt-7">
                            {{ tr(slide.button_label) }}<ArrowRight class="rtl-flip size-5" />
                        </Link>
                    </div>

                    <div v-if="design === 'product' && slide.product_image" class="flex items-center justify-center lg:justify-end">
                        <img
                            :src="slide.product_image"
                            :alt="tr(slide.title)"
                            class="max-h-52 w-auto object-contain drop-shadow-[0_30px_40px_rgba(0,0,0,.45)] sm:max-h-72 lg:max-h-[27rem]"
                            :loading="i === 0 ? 'eager' : 'lazy'"
                            draggable="false"
                        />
                    </div>
                </div>
            </div>

            <template v-if="slides.length > 1">
                <button
                    type="button"
                    class="absolute start-2 top-1/2 z-10 grid size-10 -translate-y-1/2 place-items-center rounded-full bg-white/15 text-white backdrop-blur transition hover:bg-white/30 sm:start-4 sm:size-12"
                    :aria-label="$t('Previous slide')"
                    @click="step(-1)"
                >
                    <ChevronLeft class="rtl-flip size-6" />
                </button>
                <button
                    type="button"
                    class="absolute end-2 top-1/2 z-10 grid size-10 -translate-y-1/2 place-items-center rounded-full bg-white/15 text-white backdrop-blur transition hover:bg-white/30 sm:end-4 sm:size-12"
                    :aria-label="$t('Next slide')"
                    @click="step(1)"
                >
                    <ChevronRight class="rtl-flip size-6" />
                </button>
                <div class="absolute inset-x-0 bottom-4 z-10 flex items-center justify-center gap-2">
                    <button
                        v-for="(_, i) in slides"
                        :key="i"
                        type="button"
                        class="h-2 rounded-full transition-all"
                        :class="i === current ? 'w-8 bg-secondary' : 'w-2 bg-white/50 hover:bg-white/80'"
                        :aria-label="`${$t('Slide')} ${i + 1}`"
                        :aria-current="i === current"
                        @click="go(i); restart()"
                    />
                </div>
            </template>
        </div>
    </section>
</template>
