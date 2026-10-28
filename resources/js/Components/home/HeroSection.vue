<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { activeLocale, slugOf, tr } from '@/lib/locale';
import type { HomeSection, Product, SharedProps } from '@/types';
import Price from '@/Components/Price.vue';
import SmartImage from '@/Components/SmartImage.vue';
import Section3D from '@/Components/home/Section3D.vue';

/**
 * Designs: split (text + floating featured products), slider (full-bleed
 * slides) and centered (headline over a gradient with stats).
 */
const props = defineProps<{ section: HomeSection }>();

const page = usePage<SharedProps>();
const settings = computed(() => page.props.settings);
const c = computed(() => props.section.content ?? {});
const products = computed(() => (props.section.items as Product[]).slice(0, 3));

const description = computed(
    () =>
        tr(c.value.text) ||
        (activeLocale() === 'ar' ? settings.value.seo_description_ar : settings.value.seo_description_en),
);
const headline = computed(() => tr(c.value.headline) || settings.value.site_name_en);

const stats = computed(() =>
    [
        { value: props.section.stats?.products, label: 'Products' },
        { value: props.section.stats?.brands, label: 'Brands' },
        { value: props.section.stats?.projects, label: 'Projects delivered' },
    ].filter((s) => s.value),
);

// slider ----------------------------------------------------------------------
const slides = computed<any[]>(() => (c.value.slides ?? []).filter((s: any) => s.image || tr(s.title)));
const design = computed(() => (props.section.design === 'slider' && !slides.value.length ? 'centered' : props.section.design));
const current = ref(0);
let timer: ReturnType<typeof setInterval> | undefined;

function go(delta: number) {
    const n = slides.value.length;
    if (n) current.value = (current.value + delta + n) % n;
}
function restart() {
    clearInterval(timer);
    if (design.value === 'slider' && slides.value.length > 1) timer = setInterval(() => go(1), 6500);
}
onMounted(restart);
onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <!-- Slider -->
    <section v-if="design === 'slider'" class="hero-section relative isolate overflow-hidden bg-header text-on-header" aria-roledescription="carousel">
        <div class="relative min-h-[28rem] sm:min-h-[34rem] lg:min-h-[38rem]">
            <div
                v-for="(slide, i) in slides"
                :key="i"
                class="absolute inset-0 transition-opacity duration-700"
                :class="i === current ? 'opacity-100' : 'pointer-events-none opacity-0'"
                :aria-hidden="i !== current"
            >
                <img v-if="slide.image" :src="slide.image" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover" />
                <div class="absolute inset-0 -z-10 bg-gradient-to-r from-header/90 via-header/60 to-header/20 rtl:bg-gradient-to-l" />
                <div class="container-page flex h-full min-h-[28rem] items-center py-16 sm:min-h-[34rem] sm:!px-20 lg:min-h-[38rem]">
                    <div class="max-w-2xl">
                        <h1 v-if="i === 0" class="text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl">{{ tr(slide.title) }}</h1>
                        <h2 v-else class="text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl">{{ tr(slide.title) }}</h2>
                        <p v-if="tr(slide.subtitle)" class="mt-4 text-base leading-relaxed text-white/80 sm:text-lg">{{ tr(slide.subtitle) }}</p>
                        <Link v-if="slide.button_url && tr(slide.button_label)" :href="slide.button_url" class="btn btn-secondary btn-lg mt-8">
                            {{ tr(slide.button_label) }}<ArrowRight class="rtl-flip size-5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <template v-if="slides.length > 1">
            <div class="absolute inset-x-0 bottom-5 z-10 flex items-center justify-center gap-2">
                <button
                    v-for="(_, i) in slides"
                    :key="i"
                    type="button"
                    class="h-2 rounded-full transition-all"
                    :class="i === current ? 'w-8 bg-secondary' : 'w-2 bg-white/50 hover:bg-white/80'"
                    :aria-label="`Slide ${i + 1}`"
                    @click="current = i; restart()"
                />
            </div>
            <button type="button" class="absolute start-3 top-1/2 z-10 hidden size-11 -translate-y-1/2 place-items-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/25 sm:grid" aria-label="Previous slide" @click="go(-1); restart()">
                <ChevronLeft class="rtl-flip size-6" />
            </button>
            <button type="button" class="absolute end-3 top-1/2 z-10 hidden size-11 -translate-y-1/2 place-items-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/25 sm:grid" aria-label="Next slide" @click="go(1); restart()">
                <ChevronRight class="rtl-flip size-6" />
            </button>
        </template>
    </section>

    <!-- Centered -->
    <section v-else-if="design === 'centered'" class="hero-section relative isolate overflow-hidden bg-header text-on-header">
        <div class="bg-grid absolute inset-0 -z-10" aria-hidden="true" />
        <div class="absolute start-1/2 top-0 -z-10 size-[34rem] -translate-x-1/2 rounded-full bg-primary/40 blur-[130px]" aria-hidden="true" />
        <Section3D v-if="section.model_3d" :model="section.model_3d" size="hero" />
        <div class="container-page py-20 text-center sm:py-28">
            <span v-if="tr(c.badge)" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-semibold text-white/85 backdrop-blur">
                <span class="size-1.5 rounded-full bg-secondary" />{{ tr(c.badge) }}
            </span>
            <h1 class="mx-auto mt-6 max-w-4xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">{{ headline }}</h1>
            <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-white/70 sm:text-lg">{{ description }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <Link v-if="tr(c.primary_label)" :href="c.primary_url || '/shop'" class="btn btn-secondary btn-lg">
                    {{ tr(c.primary_label) }}<ArrowRight class="rtl-flip size-5" />
                </Link>
                <Link v-if="tr(c.secondary_label)" :href="c.secondary_url || '/contact'" class="btn btn-on-dark btn-lg">{{ tr(c.secondary_label) }}</Link>
            </div>
            <dl v-if="stats.length" class="mx-auto mt-14 grid max-w-xl grid-cols-3 gap-6 border-t border-white/10 pt-8">
                <div v-for="s in stats" :key="s.label">
                    <dd class="num text-3xl font-bold">{{ s.value }}<span class="text-secondary">+</span></dd>
                    <dt class="mt-1 text-xs text-white/55">{{ $t(s.label) }}</dt>
                </div>
            </dl>
        </div>
    </section>

    <!-- Split (default) -->
    <section v-else class="hero-section relative isolate overflow-hidden bg-header text-on-header">
        <div class="bg-grid absolute inset-0 -z-10" aria-hidden="true" />
        <div class="absolute -start-32 top-10 -z-10 size-[28rem] rounded-full bg-primary/40 blur-[120px]" aria-hidden="true" />
        <div class="absolute -end-20 bottom-0 -z-10 size-96 rounded-full bg-secondary/25 blur-[110px]" aria-hidden="true" />
        <Section3D v-if="section.model_3d" :model="section.model_3d" size="hero" />

        <div class="container-page grid items-center gap-12 py-16 sm:py-20 lg:grid-cols-12 lg:gap-10 lg:py-28">
            <div class="lg:col-span-7">
                <span v-if="tr(c.badge)" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-semibold text-white/85 backdrop-blur">
                    <span class="size-1.5 rounded-full bg-secondary" />{{ tr(c.badge) }}
                </span>
                <h1 class="mt-6 text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl lg:text-[3.5rem]">{{ headline }}</h1>
                <p class="mt-5 max-w-xl text-base leading-relaxed text-white/70 sm:text-lg">{{ description }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link v-if="tr(c.primary_label)" :href="c.primary_url || '/shop'" class="btn btn-secondary btn-lg">
                        {{ tr(c.primary_label) }}<ArrowRight class="rtl-flip size-5" />
                    </Link>
                    <Link v-if="tr(c.secondary_label)" :href="c.secondary_url || '/contact'" class="btn btn-on-dark btn-lg">{{ tr(c.secondary_label) }}</Link>
                </div>

                <dl v-if="stats.length" class="mt-12 grid max-w-md grid-cols-3 gap-6 border-t border-white/10 pt-8">
                    <div v-for="s in stats" :key="s.label">
                        <dd class="num text-3xl font-bold text-white">{{ s.value }}<span class="text-secondary">+</span></dd>
                        <dt class="mt-1 text-xs text-white/55">{{ $t(s.label) }}</dt>
                    </div>
                </dl>
            </div>

            <!-- a 3D model chosen for this section takes the place of the floating product cards -->
            <div v-if="products.length && !section.model_3d" class="hero-cards relative hidden lg:col-span-5 lg:block">
                <div class="relative mx-auto h-[33rem] max-w-md">
                    <Link
                        v-for="(p, i) in products"
                        :key="p.id"
                        :href="`/products/${slugOf(p.slug, p.id)}`"
                        class="absolute w-60 rounded-2xl border border-white/15 bg-white p-2.5 text-ink shadow-[var(--shadow-lift)] transition duration-300 hover:z-10 hover:-translate-y-2"
                        :class="[i === 0 && 'end-0 top-0 rotate-3', i === 1 && 'start-0 top-40 -rotate-3', i === 2 && 'end-6 bottom-0 rotate-1']"
                    >
                        <SmartImage :src="p.cover_url" :alt="tr(p.name)" :seed="tr(p.name)" ratio="16 / 11" class="rounded-xl" />
                        <div class="px-1.5 pb-1 pt-3">
                            <p class="line-clamp-1 text-sm font-semibold">{{ tr(p.name) }}</p>
                            <div class="mt-1"><Price :amount="p.price" :compare="p.compare_price" size="sm" /></div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
