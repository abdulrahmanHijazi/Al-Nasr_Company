<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { slugOf, tr } from '@/lib/locale';
import type { Category, HomeSection } from '@/types';
import CategoryTile from '@/Components/CategoryTile.vue';
import SectionShell from '@/Components/home/SectionShell.vue';
import SmartImage from '@/Components/SmartImage.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed(() => props.section.items as Category[]);
</script>

<template>
    <SectionShell v-if="items.length" :section="section" :eyebrow="$t('Browse')">
        <!-- Tiles -->
        <div v-if="section.design === 'tiles'" class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
            <div v-for="(c, i) in items" :key="c.id" v-reveal="(i % 4) * 60">
                <CategoryTile :category="c" />
            </div>
        </div>

        <!-- Pills -->
        <ul v-else class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            <li v-for="(c, i) in items" :key="c.id" v-reveal="(i % 4) * 50">
                <Link :href="`/categories/${slugOf(c.slug, c.id)}`" class="card card-hover group flex items-center gap-3 p-3">
                    <SmartImage :src="c.image_url" :alt="tr(c.name)" :seed="tr(c.name)" ratio="1 / 1" class="size-14 shrink-0 rounded-xl" />
                    <span class="min-w-0">
                        <span class="block truncate font-semibold text-ink group-hover:text-primary">{{ tr(c.name) }}</span>
                        <span v-if="c.products_count !== undefined" class="text-xs text-muted">{{ $t(':count products', { count: String(c.products_count) }) }}</span>
                    </span>
                </Link>
            </li>
        </ul>
    </SectionShell>
</template>
