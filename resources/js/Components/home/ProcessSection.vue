<script setup lang="ts">
import { computed } from 'vue';
import { tr } from '@/lib/locale';
import type { HomeSection } from '@/types';
import SectionShell from '@/Components/home/SectionShell.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed<any[]>(() => props.section.content?.items ?? []);
</script>

<template>
    <SectionShell v-if="items.length" :section="section" :no-heading="!section.title">
        <!-- Grid -->
        <ol v-if="section.design === 'grid'" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <li v-for="(step, i) in items" :key="i" v-reveal="i * 70" class="relative rounded-2xl border border-dashed border-line bg-surface p-5">
                <span class="num grid size-8 place-items-center rounded-full bg-secondary text-sm font-bold text-on-secondary">{{ i + 1 }}</span>
                <h3 class="mt-3 font-semibold text-ink">{{ tr(step.title) }}</h3>
                <p class="mt-1 text-sm text-muted">{{ tr(step.text) }}</p>
            </li>
        </ol>

        <!-- Timeline -->
        <ol v-else class="relative mx-auto max-w-3xl space-y-8 border-s-2 border-line ps-8">
            <li v-for="(step, i) in items" :key="i" v-reveal="i * 70" class="relative">
                <span class="num absolute -start-[2.9rem] grid size-9 place-items-center rounded-full border-4 border-canvas bg-primary text-sm font-bold text-on-primary">{{ i + 1 }}</span>
                <h3 class="text-lg font-bold text-ink">{{ tr(step.title) }}</h3>
                <p class="mt-1 text-sm leading-relaxed text-muted">{{ tr(step.text) }}</p>
            </li>
        </ol>
    </SectionShell>
</template>
