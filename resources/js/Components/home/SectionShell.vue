<script setup lang="ts">
import { computed } from 'vue';
import { tr } from '@/lib/locale';
import { readableOn } from '@/lib/theme';
import type { HomeSection } from '@/types';
import SectionHeading from '@/Components/SectionHeading.vue';
import Section3D from '@/Components/home/Section3D.vue';

/**
 * Wrapper every home section renders inside: background colour (with automatic
 * text contrast), vertical rhythm and the heading row.
 */
const props = withDefaults(
    defineProps<{
        section: HomeSection;
        /** surface the section sits on when the admin sets no colour */
        tone?: 'canvas' | 'surface' | 'dark';
        eyebrow?: string;
        noHeading?: boolean;
    }>(),
    { tone: 'canvas', noHeading: false },
);

const DEFAULT_LINKS: Partial<Record<HomeSection['type'], string>> = {
    categories: '/shop',
    products: '/shop',
    bundles: '/offers',
    brands: '/brands',
    portfolio: '/portfolio',
};

const bg = computed(() => props.section.background_color || null);
const onBg = computed(() => (bg.value ? readableOn(bg.value) : null));
const dark = computed(() => (bg.value ? onBg.value === '#ffffff' : props.tone === 'dark'));

const surface = computed(() => {
    if (bg.value) return '';
    return { canvas: '', surface: 'bg-surface', dark: 'bg-header text-on-header' }[props.tone];
});

const title = computed(() => tr(props.section.title));
const subtitle = computed(() => tr(props.section.subtitle));
const href = computed(() => DEFAULT_LINKS[props.section.type]);

defineExpose({ dark });
</script>

<template>
    <section
        class="section relative isolate overflow-hidden"
        :class="surface"
        :style="bg ? { backgroundColor: bg, color: onBg ?? undefined } : undefined"
    >
        <div v-if="dark" class="bg-grid absolute inset-0 -z-10 opacity-60" aria-hidden="true" />
        <Section3D v-if="section.model_3d" :model="section.model_3d" />
        <div class="container-page">
            <!-- the model fills the empty strip beside the heading, so the heading keeps clear of it -->
            <div :class="section.model_3d && (section.model_3d.side === 'start' ? 'lg:ps-80' : 'lg:pe-80')">
                <SectionHeading
                    v-if="!noHeading && title"
                    :dark="dark"
                    :eyebrow="eyebrow"
                    :title="title"
                    :subtitle="subtitle || undefined"
                    :href="href"
                />
            </div>
            <slot :dark="dark" />
        </div>
    </section>
</template>
