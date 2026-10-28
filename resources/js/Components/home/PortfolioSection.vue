<script setup lang="ts">
import { computed } from 'vue';
import type { HomeSection, PortfolioItem } from '@/types';
import PortfolioCard from '@/Components/PortfolioCard.vue';
import SectionShell from '@/Components/home/SectionShell.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed(() => props.section.items as PortfolioItem[]);
const lead = computed(() => items.value[0]);
const rest = computed(() => items.value.slice(1));
</script>

<template>
    <SectionShell v-if="items.length" :section="section" tone="surface" :eyebrow="$t('Portfolio')">
        <!-- Grid -->
        <div v-if="section.design === 'grid' || items.length < 3" class="grid gap-5 md:grid-cols-3">
            <div v-for="(item, i) in items" :key="item.id" v-reveal="i * 80">
                <PortfolioCard :item="item" />
            </div>
        </div>

        <!-- Featured first: one large card, the rest stacked beside it -->
        <div v-else class="grid gap-5 lg:grid-cols-5">
            <div v-reveal class="lg:col-span-3">
                <PortfolioCard :item="lead" ratio="16 / 11" />
            </div>
            <div class="grid gap-5 lg:col-span-2">
                <div v-for="(item, i) in rest.slice(0, 2)" :key="item.id" v-reveal="(i + 1) * 80">
                    <PortfolioCard :item="item" ratio="16 / 8" />
                </div>
            </div>
        </div>
    </SectionShell>
</template>
