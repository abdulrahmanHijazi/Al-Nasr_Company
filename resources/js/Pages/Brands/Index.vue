<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { Brand } from '@/types';
import BrandCard from '@/Components/BrandCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';

defineProps<{ brands: Brand[] }>();
</script>

<template>
    <Head :title="$t('Brands')" />

    <PageHeader
        :eyebrow="$t('Partners')"
        :title="$t('Our brands')"
        :description="$t('We work directly with leading manufacturers so you get genuine products and dependable support.')"
        :crumbs="[{ label: $t('Brands') }]"
    />

    <div class="container-page py-10 sm:py-14">
        <div v-if="brands.length" class="grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-3 lg:grid-cols-4">
            <div v-for="(b, i) in brands" :key="b.id" v-reveal="(i % 4) * 50">
                <BrandCard :brand="b" />
            </div>
        </div>
        <EmptyState v-else :title="$t('No brands yet')" />
    </div>
</template>
