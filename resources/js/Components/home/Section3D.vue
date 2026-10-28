<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import type { SectionViewer } from '@/lib/bg3d/section';
import type { SectionModel3d } from '@/types';

/**
 * The 3D model an admin picked for a home section (Filament → Home page sections → 3D model).
 * Sits in the section's corner, behind the content, from `lg` up. Three.js loads lazily and a viewer
 * exists only while the section is on screen.
 *
 * `band`  — the empty strip beside a section heading
 * `hero`  — the full height of a hero section
 * `inline` — a box that takes part in the parent's flex layout (call-to-action)
 */
const props = withDefaults(defineProps<{ model: SectionModel3d; size?: 'band' | 'hero' | 'inline' }>(), { size: 'band' });

const host = ref<HTMLElement | null>(null);
const canvas = ref<HTMLCanvasElement | null>(null);
const active = ref(false);
const ready = ref(false);

let viewer: SectionViewer | null = null;
let observer: IntersectionObserver | null = null;
let token = 0;

async function start() {
    const mine = ++token;
    active.value = true;
    await nextTick(); // the <canvas> only exists while active

    if (!canvas.value) return;

    try {
        const { createSectionViewer } = await import('@/lib/bg3d/section');
        const created = await createSectionViewer(canvas.value, { model: props.model.model, customUrl: props.model.url });

        if (mine !== token) return created?.dispose(); // scrolled away while loading
        viewer = created;
        ready.value = !!created;
    } catch (error) {
        console.warn('[section3d] 3D model disabled:', error);
    }
}

function stop() {
    token++;
    viewer?.dispose();
    viewer = null;
    ready.value = false;
    active.value = false;
}

onMounted(() => {
    if (!host.value || !('IntersectionObserver' in window)) return;

    // a hidden host (below `lg`) never intersects, so phones never load Three.js for this
    observer = new IntersectionObserver(([entry]) => (entry.isIntersecting ? start() : stop()), { rootMargin: '120px' });
    observer.observe(host.value);
});
onBeforeUnmount(() => {
    observer?.disconnect();
    stop();
});
</script>

<template>
    <div v-if="size === 'inline'" ref="host" class="pointer-events-none hidden h-48 w-60 shrink-0 lg:block" aria-hidden="true">
        <canvas v-if="active" ref="canvas" class="size-full transition-opacity duration-700" :class="ready ? 'opacity-100' : 'opacity-0'" />
    </div>
    <div v-else class="pointer-events-none absolute inset-x-0 -z-10 hidden lg:block" :class="size === 'hero' ? 'inset-y-0' : 'top-3'" aria-hidden="true">
        <div class="container-page relative" :class="size === 'hero' ? 'h-full' : ''">
            <div
                ref="host"
                class="flex"
                :class="[model.side === 'start' ? 'justify-start' : 'justify-end', size === 'hero' ? 'h-full items-center' : '']"
            >
                <div :class="size === 'hero' ? 'h-[85%] w-5/12' : 'h-64 w-72 xl:w-80'">
                    <canvas v-if="active" ref="canvas" class="size-full transition-opacity duration-700" :class="ready ? 'opacity-100' : 'opacity-0'" />
                </div>
            </div>
        </div>
    </div>
</template>
