<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Gift } from 'lucide-vue-next';
import type { Bundle } from '@/types';
import BundleCard from '@/Components/BundleCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';

defineProps<{ bundles: Bundle[] }>();
</script>

<template>
    <Head :title="$t('Special offers')" />

    <PageHeader
        :eyebrow="$t('Special offers')"
        :title="$t('Bundles that save you more')"
        :description="$t('Everything you need in one package, priced below buying each item separately.')"
        :crumbs="[{ label: $t('Offers') }]"
    />

    <div class="container-page py-10 sm:py-14">
        <div v-if="bundles.length" class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <div v-for="(b, i) in bundles" :key="b.id" v-reveal="(i % 3) * 70">
                <BundleCard :bundle="b" />
            </div>
        </div>
        <EmptyState v-else :icon="Gift" :title="$t('No active offers right now')" :text="$t('Check back soon — new bundles are added regularly.')" />
    </div>
</template>
