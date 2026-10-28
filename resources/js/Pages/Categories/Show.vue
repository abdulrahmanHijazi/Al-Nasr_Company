<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { slugOf, tr } from '@/lib/locale';
import type { Category } from '@/types';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import ProductCard from '@/Components/ProductCard.vue';

const props = defineProps<{ category: Category }>();

const name = computed(() => tr(props.category.name));
const products = computed(() => props.category.products ?? []);
const crumbs = computed(() => {
    const out = [{ label: 'Shop', href: '/shop' as string | undefined }];
    const parent = props.category.parent;
    if (parent) out.push({ label: tr(parent.name), href: `/categories/${slugOf(parent.slug, parent.id)}` });
    out.push({ label: name.value, href: undefined });
    return out;
});
</script>

<template>
    <Head :title="name" />

    <PageHeader
        :eyebrow="$t('Category')"
        :title="name"
        :description="tr(category.description)"
        :image="category.banner_url"
        :crumbs="crumbs.map((c) => ({ ...c, label: c.label === 'Shop' ? $t('Shop') : c.label }))"
    >
        <ul v-if="category.children?.length" class="flex flex-wrap gap-2">
            <li v-for="child in category.children" :key="child.id">
                <Link :href="`/categories/${slugOf(child.slug, child.id)}`" class="chip border-white/20 bg-white/10 text-white hover:border-secondary hover:text-secondary">
                    {{ tr(child.name) }}
                </Link>
            </li>
        </ul>
    </PageHeader>

    <div class="container-page py-10 sm:py-14">
        <div v-if="products.length" class="grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-3 lg:grid-cols-4">
            <ProductCard v-for="p in products" :key="p.id" :product="p" />
        </div>
        <EmptyState v-else :title="$t('No products in this category yet')">
            <Link href="/shop" class="btn btn-primary">{{ $t('Browse all products') }}</Link>
        </EmptyState>
    </div>
</template>
