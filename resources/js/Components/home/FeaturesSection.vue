<script setup lang="ts">
import { computed } from 'vue';
import { iconFor } from '@/lib/icons';
import { tr } from '@/lib/locale';
import type { HomeSection } from '@/types';
import SectionShell from '@/Components/home/SectionShell.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed<any[]>(() => props.section.content?.items ?? []);
</script>

<template>
    <!-- Row: full-width strip, no card chrome -->
    <section v-if="section.design === 'row'" class="border-b border-line bg-surface" :style="section.background_color ? { backgroundColor: section.background_color } : undefined">
        <div class="container-page grid gap-px sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="(item, i) in items" :key="i" v-reveal="i * 70" class="flex items-start gap-4 py-6 lg:px-5 lg:first:ps-0">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-primary-50 text-primary">
                    <component :is="iconFor(item.icon)" class="size-5" />
                </span>
                <div>
                    <h3 class="font-semibold text-ink">{{ tr(item.title) }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-muted">{{ tr(item.text) }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cards -->
    <SectionShell v-else :section="section" tone="canvas">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="(item, i) in items" :key="i" v-reveal="i * 70" class="card p-6 text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-primary text-on-primary shadow-sm">
                    <component :is="iconFor(item.icon)" class="size-7" />
                </span>
                <h3 class="mt-4 font-semibold text-ink">{{ tr(item.title) }}</h3>
                <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ tr(item.text) }}</p>
            </div>
        </div>
    </SectionShell>
</template>
