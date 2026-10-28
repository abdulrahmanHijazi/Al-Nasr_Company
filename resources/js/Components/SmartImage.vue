<script setup lang="ts">
import { computed, ref, watch } from 'vue';

/**
 * Image with a designed fallback. Until a real photo is uploaded in Filament
 * (or if it fails to load) we render a deterministic, brand-coloured artwork
 * so grids never look broken or empty.
 */
const props = withDefaults(
    defineProps<{
        src?: string | null;
        alt?: string;
        /** Text used to seed the fallback artwork (usually the item name). */
        seed?: string;
        ratio?: string;
        fit?: 'cover' | 'contain';
        eager?: boolean;
    }>(),
    { alt: '', seed: '', ratio: '4 / 3', fit: 'cover', eager: false },
);

const failed = ref(false);
watch(
    () => props.src,
    () => (failed.value = false),
);

const showImage = computed(() => !!props.src && !failed.value);

function hash(text: string): number {
    let h = 2166136261;
    for (let i = 0; i < text.length; i++) {
        h ^= text.charCodeAt(i);
        h = Math.imul(h, 16777619);
    }
    return h >>> 0;
}

const art = computed(() => {
    const h = hash(props.seed || props.alt || 'al-nasr');
    const angle = 120 + (h % 90);
    const mix = 14 + (h % 18);
    const shape = h % 3;
    const initial = (props.seed || props.alt || 'N').trim().charAt(0).toUpperCase();
    return { angle, mix, shape, initial, x: 20 + (h % 60), y: 20 + ((h >> 3) % 60) };
});
</script>

<template>
    <div class="relative isolate overflow-hidden bg-primary-50" :style="{ aspectRatio: ratio }">
        <img
            v-if="showImage"
            :src="src!"
            :alt="alt"
            :loading="eager ? 'eager' : 'lazy'"
            decoding="async"
            class="h-full w-full transition duration-500"
            :class="fit === 'cover' ? 'object-cover' : 'object-contain p-4'"
            @error="failed = true"
        />
        <div
            v-else
            class="absolute inset-0 grid place-items-center"
            :style="{
                background: `linear-gradient(${art.angle}deg, color-mix(in oklab, var(--store-primary) ${art.mix + 8}%, white), color-mix(in oklab, var(--store-secondary) ${art.mix}%, white))`,
            }"
            role="img"
            :aria-label="alt || seed"
        >
            <svg class="absolute inset-0 h-full w-full text-primary/10" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <defs>
                    <pattern id="sm-grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M10 0H0V10" fill="none" stroke="currentColor" stroke-width="0.4" />
                    </pattern>
                </defs>
                <rect width="100" height="100" fill="url(#sm-grid)" />
                <circle v-if="art.shape === 0" :cx="art.x" :cy="art.y" r="26" fill="currentColor" />
                <rect v-else-if="art.shape === 1" :x="art.x - 22" :y="art.y - 22" width="44" height="44" rx="8" fill="currentColor" transform="rotate(12 50 50)" />
                <path v-else :d="`M${art.x - 26} ${art.y + 22} L${art.x} ${art.y - 26} L${art.x + 26} ${art.y + 22}Z`" fill="currentColor" />
            </svg>
            <span class="relative text-4xl font-bold text-primary/55 sm:text-5xl" aria-hidden="true">{{ art.initial }}</span>
        </div>
        <slot />
    </div>
</template>
