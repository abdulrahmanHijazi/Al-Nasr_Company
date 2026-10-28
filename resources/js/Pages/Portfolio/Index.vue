<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import type { PortfolioItem } from '@/types';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PortfolioCard from '@/Components/PortfolioCard.vue';

const props = defineProps<{ items: PortfolioItem[] }>();

// Featured projects lead the page with a larger card.
const featured = computed(() => props.items.filter((i) => i.is_featured).slice(0, 2));
const rest = computed(() => props.items.filter((i) => !featured.value.includes(i)));
</script>

<template>
    <Head :title="$t('Portfolio')" />

    <PageHeader
        :eyebrow="$t('Portfolio')"
        :title="$t('Projects we have delivered')"
        :description="$t('A look at the work behind the catalogue — real projects for real clients.')"
        :crumbs="[{ label: $t('Portfolio') }]"
    />

    <div class="container-page py-10 sm:py-14">
        <template v-if="items.length">
            <div v-if="featured.length" class="mb-5 grid gap-5 md:grid-cols-2">
                <div v-for="(item, i) in featured" :key="item.id" v-reveal="i * 80">
                    <PortfolioCard :item="item" ratio="16 / 9" />
                </div>
            </div>
            <div v-if="rest.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="(item, i) in rest" :key="item.id" v-reveal="(i % 3) * 60">
                    <PortfolioCard :item="item" />
                </div>
            </div>
        </template>
        <EmptyState v-else :title="$t('Projects will appear here soon')" />
    </div>
</template>
