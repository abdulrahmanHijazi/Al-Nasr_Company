<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import { tr } from '@/lib/locale';
import type { HomeSection } from '@/types';
import SectionShell from '@/Components/home/SectionShell.vue';
import SmartImage from '@/Components/SmartImage.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed<any[]>(() => props.section.content?.items ?? []);

const grid = computed(
    () => ({ wide: 'grid-cols-1', split: 'grid-cols-1 md:grid-cols-2', triple: 'grid-cols-1 md:grid-cols-3' })[props.section.design] ?? 'grid-cols-1 md:grid-cols-2',
);
const ratio = computed(() => ({ wide: '21 / 7', split: '16 / 8', triple: '4 / 3' })[props.section.design] ?? '16 / 8');
</script>

<template>
    <SectionShell v-if="items.length" :section="section">
        <div class="grid gap-4 sm:gap-5" :class="grid">
            <component
                :is="item.url ? Link : 'div'"
                v-for="(item, i) in items"
                :key="i"
                v-reveal="i * 70"
                :href="item.url || undefined"
                class="group relative block overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)]"
            >
                <SmartImage :src="item.image" :alt="tr(item.title)" :seed="tr(item.title)" :ratio="ratio" class="[&_img]:group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-header/85 via-header/30 to-transparent" />
                    <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-4 p-5 text-white sm:p-7">
                        <div class="min-w-0">
                            <h3 v-if="tr(item.title)" class="text-xl font-bold sm:text-2xl">{{ tr(item.title) }}</h3>
                            <p v-if="tr(item.text)" class="mt-1 text-sm text-white/80">{{ tr(item.text) }}</p>
                        </div>
                        <span v-if="tr(item.button_label)" class="btn btn-secondary !min-h-10 shrink-0">
                            {{ tr(item.button_label) }}<ArrowRight class="rtl-flip size-4" />
                        </span>
                    </div>
                </SmartImage>
            </component>
        </div>
    </SectionShell>
</template>
