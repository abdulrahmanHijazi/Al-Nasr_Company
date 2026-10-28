<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import type { BackgroundHandle } from '@/lib/bg3d/scene';
import type { SharedProps } from '@/types';

/**
 * Fixed, click-through 3D model behind the storefront (Site settings → 3D background).
 * Three.js is imported only when the background is actually shown.
 */
const page = usePage<SharedProps>();
const settings = computed(() => page.props.settings);

const canvas = ref<HTMLCanvasElement | null>(null);
const ready = ref(false);
const heroHeight = ref(0);
let handle: BackgroundHandle | null = null;
let starting = false;

const wanted = computed(() => {
    const s = settings.value;
    if (!s?.bg3d_enabled || !s.bg3d_model) return false;
    if (s.bg3d_model === 'custom' && !s.bg3d_custom_file) return false;
    if (s.bg3d_pages === 'home' && page.component !== 'Shop/Home') return false;

    return true;
});

const options = computed(() => ({
    model: settings.value.bg3d_model ?? 'solar-panel',
    customUrl: settings.value.bg3d_custom_file ?? null,
    position: (settings.value.bg3d_position ?? 'right') as 'left' | 'center' | 'right',
    speed: Number(settings.value.bg3d_speed ?? 1),
    interactive: !!settings.value.bg3d_interactive,
}));

const opacity = computed(() => Math.max(0.1, Math.min(1, Number(settings.value.bg3d_opacity ?? 55) / 100)));

async function start() {
    if (handle || starting || !canvas.value) return;
    starting = true;

    try {
        const { createBackground, webglAvailable } = await import('@/lib/bg3d/scene');
        if (!webglAvailable() || !canvas.value || !wanted.value) return;

        handle = await createBackground(canvas.value, options.value);
        ready.value = true;
    } catch (error) {
        // a broken GPU must never break the store — log and carry on without the scene
        console.warn('[bg3d] 3D background disabled:', error);
        ready.value = false;
    } finally {
        starting = false;
    }
}

function stop() {
    handle?.dispose();
    handle = null;
    ready.value = false;
}

/**
 * The hero is a dark band; with the 3D scene on, it turns transparent and this navy
 * backdrop (exactly as tall as the hero) sits behind the canvas instead, so the model
 * is visible on the hero and white hero text keeps its contrast.
 */
function measureHero() {
    const hero = document.querySelector('.hero-section');
    heroHeight.value = hero ? Math.round(hero.getBoundingClientRect().bottom + window.scrollY) : 0;
}

let resizeObserver: ResizeObserver | null = null;
let stopRouterListener: (() => void) | null = null;

// <html class="has-bg3d"> lets the stylesheet make hero and bands see-through.
watch(ready, (value) => document.documentElement.classList.toggle('has-bg3d', value), { immediate: true });
watch(
    () => [ready.value, options.value.position] as const,
    ([isReady, position]) => {
        // the hero's floating product cards would sit on top of a model placed on the right
        document.documentElement.dataset.bg3dPosition = isReady ? position : '';
    },
    { immediate: true },
);

watch(wanted, async (value) => {
    if (!value) return stop();
    await nextTick(); // the <canvas> only exists once `wanted` is true
    start();
});
watch(options, (value) => handle?.update(value), { deep: true });

onMounted(() => {
    wanted.value && start();
    measureHero();
    resizeObserver = new ResizeObserver(measureHero);
    resizeObserver.observe(document.body);
    stopRouterListener = router.on('finish', () => requestAnimationFrame(measureHero));
});
onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    stopRouterListener?.();
    stop();
    document.documentElement.classList.remove('has-bg3d');
    delete document.documentElement.dataset.bg3dPosition;
});
</script>

<template>
    <template v-if="wanted">
        <div
            v-if="ready && heroHeight"
            class="pointer-events-none absolute inset-x-0 top-0 -z-20 bg-header"
            :style="{ height: `${heroHeight}px` }"
            aria-hidden="true"
        />
        <div class="pointer-events-none fixed inset-0 -z-10" aria-hidden="true">
            <canvas
                ref="canvas"
                class="size-full transition-opacity duration-1000"
                :style="{ opacity: ready ? opacity : 0 }"
            />
        </div>
    </template>
</template>
