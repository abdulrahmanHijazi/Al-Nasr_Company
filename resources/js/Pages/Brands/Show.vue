<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';
import { tr } from '@/lib/locale';
import type { Brand } from '@/types';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import ProductCard from '@/Components/ProductCard.vue';
import SmartImage from '@/Components/SmartImage.vue';

const props = defineProps<{ brand: Brand }>();

const name = computed(() => tr(props.brand.name));
const products = computed(() => props.brand.products ?? []);
</script>

<template>
    <Head :title="name" />

    <PageHeader
        :eyebrow="$t('Brand')"
        :title="name"
        :description="tr(brand.description)"
        :image="brand.cover_url"
        :crumbs="[{ label: $t('Brands'), href: '/brands' }, { label: name }]"
    >
        <div class="flex flex-wrap items-center gap-4">
            <div v-if="brand.logo_url" class="w-28 rounded-xl bg-white p-2.5">
                <SmartImage :src="brand.logo_url" :alt="name" :seed="name" fit="contain" ratio="3 / 2" class="!bg-transparent" />
            </div>
            <a v-if="brand.website_url" :href="brand.website_url" target="_blank" rel="noopener noreferrer" class="btn btn-on-dark">
                {{ $t('Visit website') }}<ExternalLink class="size-4" />
            </a>
        </div>
    </PageHeader>

    <div class="container-page py-10 sm:py-14">
        <h2 class="mb-6 text-xl font-bold">
            {{ $t('Products by :brand', { brand: name }) }}
            <span class="num ms-1 text-base font-medium text-muted">({{ products.length }})</span>
        </h2>
        <div v-if="products.length" class="grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-3 lg:grid-cols-4">
            <ProductCard v-for="p in products" :key="p.id" :product="p" />
        </div>
        <EmptyState v-else :title="$t('No products from this brand yet')" />
    </div>
</template>
