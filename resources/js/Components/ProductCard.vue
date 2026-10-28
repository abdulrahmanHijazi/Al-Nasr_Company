<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { discountPercent, slugOf, tr } from '@/lib/locale';
import type { Product } from '@/types';
import Price from '@/Components/Price.vue';
import SmartImage from '@/Components/SmartImage.vue';

const props = defineProps<{ product: Product }>();

const href = computed(() => `/products/${slugOf(props.product.slug, props.product.id)}`);
const name = computed(() => tr(props.product.name));
const percent = computed(() => discountPercent(props.product.price, props.product.compare_price));
const inStock = computed(() => props.product.stock_qty > 0);
</script>

<template>
    <Link :href="href" class="card card-hover group flex h-full flex-col overflow-hidden">
        <SmartImage :src="product.cover_url" :alt="name" :seed="name" ratio="1 / 1" class="[&_img]:group-hover:scale-105">
            <div class="absolute start-3 top-3 flex flex-col items-start gap-1.5">
                <span v-if="percent" class="badge num bg-secondary text-on-secondary shadow-sm">−{{ percent }}%</span>
                <span v-if="product.is_featured && !percent" class="badge bg-header text-on-header shadow-sm">{{ $t('Featured') }}</span>
            </div>
            <span v-if="!inStock" class="absolute inset-x-0 bottom-0 bg-ink/75 py-1.5 text-center text-xs font-semibold text-white backdrop-blur-sm">
                {{ $t('Out of stock') }}
            </span>
        </SmartImage>

        <div class="flex flex-1 flex-col gap-1.5 p-4">
            <p v-if="product.brand" class="truncate text-xs font-semibold uppercase tracking-wide text-primary rtl:tracking-normal">
                {{ tr(product.brand.name) }}
            </p>
            <h3 class="line-clamp-2 min-h-[2.75rem] text-[0.95rem] font-semibold leading-snug text-ink group-hover:text-primary">
                {{ name }}
            </h3>
            <div class="flex flex-wrap items-center justify-between gap-x-2 text-xs">
                <span class="num text-muted">{{ product.sku }}</span>
                <span v-if="inStock" class="flex items-center gap-1 font-medium text-success">
                    <span class="size-1.5 rounded-full bg-success" />{{ $t('In stock') }}
                </span>
            </div>
            <div class="mt-auto pt-3">
                <Price :amount="product.price" :compare="product.compare_price" size="md" />
            </div>
        </div>
    </Link>
</template>
