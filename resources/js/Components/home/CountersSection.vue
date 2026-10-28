<script setup lang="ts">
import { computed } from 'vue';
import { tr } from '@/lib/locale';
import type { HomeSection } from '@/types';
import SectionShell from '@/Components/home/SectionShell.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed<any[]>(() => props.section.content?.items ?? []);
</script>

<template>
    <!-- Colour band -->
    <section
        v-if="items.length && section.design === 'band'"
        class="relative isolate overflow-hidden bg-primary py-12 text-on-primary sm:py-14"
        :style="section.background_color ? { backgroundColor: section.background_color } : undefined"
    >
        <div class="bg-grid absolute inset-0 -z-10" aria-hidden="true" />
        <dl class="container-page grid grid-cols-2 gap-8 text-center lg:grid-cols-4">
            <div v-for="(item, i) in items" :key="i" v-reveal="i * 70">
                <dd class="num text-4xl font-bold sm:text-5xl">{{ item.value }}<span class="text-secondary">{{ item.suffix }}</span></dd>
                <dt class="mt-2 text-sm opacity-80">{{ tr(item.label) }}</dt>
            </div>
        </dl>
    </section>

    <!-- Cards -->
    <SectionShell v-else-if="items.length" :section="section">
        <dl class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div v-for="(item, i) in items" :key="i" v-reveal="i * 70" class="card p-6 text-center">
                <dd class="num text-4xl font-bold text-primary">{{ item.value }}<span class="text-secondary">{{ item.suffix }}</span></dd>
                <dt class="mt-2 text-sm text-muted">{{ tr(item.label) }}</dt>
            </div>
        </dl>
    </SectionShell>
</template>
