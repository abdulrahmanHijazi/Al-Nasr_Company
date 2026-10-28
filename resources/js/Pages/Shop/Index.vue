<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { PackageSearch, Search, SlidersHorizontal, X } from 'lucide-vue-next';
import { slugOf, tr } from '@/lib/locale';
import type { Brand, Category, Paginated, Product, ShopFilters } from '@/types';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Pagination from '@/Components/Pagination.vue';
import ProductCard from '@/Components/ProductCard.vue';

const props = withDefaults(
    defineProps<{
        products: Paginated<Product>;
        brands?: Brand[];
        categories?: Category[];
        filters?: ShopFilters;
    }>(),
    { brands: () => [], categories: () => [], filters: () => ({}) },
);

const toArray = (v: unknown): string[] => (Array.isArray(v) ? v.map(String) : v ? [String(v)] : []);

const state = reactive({
    q: props.filters.q ?? '',
    brand: toArray(props.filters.brand),
    category: toArray(props.filters.category),
    min_price: props.filters.min_price ? String(props.filters.min_price) : '',
    max_price: props.filters.max_price ? String(props.filters.max_price) : '',
    in_stock: !!props.filters.in_stock,
    sort: props.filters.sort ?? 'latest',
});

// Re-sync when Inertia hands us new filters (back/forward, chip removal, header search).
watch(
    () => props.filters,
    (f) => {
        state.q = f.q ?? '';
        state.brand = toArray(f.brand);
        state.category = toArray(f.category);
        state.min_price = f.min_price ? String(f.min_price) : '';
        state.max_price = f.max_price ? String(f.max_price) : '';
        state.in_stock = !!f.in_stock;
        state.sort = f.sort ?? 'latest';
    },
);

const query = computed(() => ({
    q: state.q.trim() || undefined,
    brand: state.brand.length ? state.brand : undefined,
    category: state.category.length ? state.category : undefined,
    min_price: state.min_price || undefined,
    max_price: state.max_price || undefined,
    in_stock: state.in_stock ? 1 : undefined,
    sort: state.sort !== 'latest' ? state.sort : undefined,
}));

function apply() {
    router.get('/shop', query.value as never, { preserveState: true, preserveScroll: true, replace: true });
}

let timer: ReturnType<typeof setTimeout>;
function applyDebounced() {
    clearTimeout(timer);
    timer = setTimeout(apply, 450);
}

function toggle(list: string[], value: string) {
    const i = list.indexOf(value);
    if (i === -1) list.push(value);
    else list.splice(i, 1);
    apply();
}

function reset() {
    state.q = '';
    state.brand = [];
    state.category = [];
    state.min_price = '';
    state.max_price = '';
    state.in_stock = false;
    state.sort = 'latest';
    apply();
}

const topCategories = computed(() => props.categories.filter((c) => !c.parent_id));
const childrenOf = (c: Category) => c.children ?? props.categories.filter((x) => x.parent_id === c.id);

const nameBySlug = computed(() => {
    const map = new Map<string, string>();
    props.brands.forEach((b) => map.set(`brand:${slugOf(b.slug, b.id)}`, tr(b.name)));
    props.categories.forEach((c) => map.set(`category:${slugOf(c.slug, c.id)}`, tr(c.name)));
    return map;
});

const chips = computed(() => {
    const out: { key: string; label: string; remove: () => void }[] = [];
    if (state.q.trim()) out.push({ key: 'q', label: `“${state.q.trim()}”`, remove: () => ((state.q = ''), apply()) });
    state.category.forEach((s) =>
        out.push({ key: `c-${s}`, label: nameBySlug.value.get(`category:${s}`) ?? s, remove: () => toggle(state.category, s) }),
    );
    state.brand.forEach((s) =>
        out.push({ key: `b-${s}`, label: nameBySlug.value.get(`brand:${s}`) ?? s, remove: () => toggle(state.brand, s) }),
    );
    if (state.min_price || state.max_price)
        out.push({
            key: 'price',
            label: `${state.min_price || 0} – ${state.max_price || '∞'}`,
            remove: () => ((state.min_price = ''), (state.max_price = ''), apply()),
        });
    if (state.in_stock) out.push({ key: 'stock', label: 'In stock', remove: () => ((state.in_stock = false), apply()) });
    return out;
});

const drawer = ref(false);
const sorts = [
    { value: 'latest', label: 'Newest' },
    { value: 'price_asc', label: 'Price: low to high' },
    { value: 'price_desc', label: 'Price: high to low' },
    { value: 'name', label: 'Name A–Z' },
] as const;
</script>

<template>
    <Head :title="$t('Shop')" />

    <PageHeader :eyebrow="$t('Catalogue')" :title="$t('Shop')" :description="$t('Browse every product, filter by brand or category, and request a quote in a click.')" :crumbs="[{ label: $t('Shop') }]" />

    <div class="container-page py-8 sm:py-10">
        <div class="grid gap-8 lg:grid-cols-[17rem_1fr]">
            <!-- Filters -->
            <aside>
                <div v-if="drawer" class="fixed inset-0 z-40 bg-ink/50 lg:hidden" @click="drawer = false" />
                <div
                    class="space-y-7 lg:sticky lg:top-28 lg:block lg:self-start"
                    :class="
                        drawer
                            ? 'fixed inset-y-0 start-0 z-50 w-[85%] max-w-sm overflow-y-auto bg-surface p-5 shadow-2xl'
                            : 'hidden'
                    "
                >
                    <div class="flex items-center justify-between lg:hidden">
                        <h2 class="text-lg font-bold">{{ $t('Filters') }}</h2>
                        <button type="button" class="grid size-9 place-items-center rounded-lg hover:bg-canvas" :aria-label="$t('Close')" @click="drawer = false">
                            <X class="size-5" />
                        </button>
                    </div>

                    <form class="relative" role="search" @submit.prevent="apply">
                        <Search class="pointer-events-none absolute start-3.5 top-1/2 size-4 -translate-y-1/2 text-muted" />
                        <input v-model="state.q" type="search" class="field !ps-10" :placeholder="$t('Search products, SKUs, brands…')" />
                    </form>

                    <fieldset v-if="topCategories.length">
                        <legend class="mb-3 text-sm font-bold text-ink">{{ $t('Categories') }}</legend>
                        <ul class="space-y-1">
                            <li v-for="c in topCategories" :key="c.id">
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 text-sm hover:bg-canvas">
                                    <input
                                        type="checkbox"
                                        class="size-4 rounded border-line text-primary accent-[var(--store-primary)]"
                                        :checked="state.category.includes(slugOf(c.slug, c.id))"
                                        @change="toggle(state.category, slugOf(c.slug, c.id))"
                                    />
                                    <span class="flex-1">{{ tr(c.name) }}</span>
                                    <span v-if="c.products_count !== undefined" class="num text-xs text-muted">{{ c.products_count }}</span>
                                </label>
                                <ul v-if="childrenOf(c).length" class="ms-6 border-s border-line ps-2">
                                    <li v-for="child in childrenOf(c)" :key="child.id">
                                        <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 text-sm text-ink-soft hover:bg-canvas">
                                            <input
                                                type="checkbox"
                                                class="size-4 rounded border-line accent-[var(--store-primary)]"
                                                :checked="state.category.includes(slugOf(child.slug, child.id))"
                                                @change="toggle(state.category, slugOf(child.slug, child.id))"
                                            />
                                            <span class="flex-1">{{ tr(child.name) }}</span>
                                        </label>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </fieldset>

                    <fieldset v-if="brands.length">
                        <legend class="mb-3 text-sm font-bold text-ink">{{ $t('Brands') }}</legend>
                        <ul class="space-y-1">
                            <li v-for="b in brands" :key="b.id">
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 text-sm hover:bg-canvas">
                                    <input
                                        type="checkbox"
                                        class="size-4 rounded border-line accent-[var(--store-primary)]"
                                        :checked="state.brand.includes(slugOf(b.slug, b.id))"
                                        @change="toggle(state.brand, slugOf(b.slug, b.id))"
                                    />
                                    <span class="flex-1">{{ tr(b.name) }}</span>
                                    <span v-if="b.products_count !== undefined" class="num text-xs text-muted">{{ b.products_count }}</span>
                                </label>
                            </li>
                        </ul>
                    </fieldset>

                    <fieldset>
                        <legend class="mb-3 text-sm font-bold text-ink">{{ $t('Price') }}</legend>
                        <div class="flex items-center gap-2">
                            <input v-model="state.min_price" type="number" min="0" inputmode="decimal" class="field num !px-3 !py-2" :placeholder="$t('Min')" @input="applyDebounced" />
                            <span class="text-muted">–</span>
                            <input v-model="state.max_price" type="number" min="0" inputmode="decimal" class="field num !px-3 !py-2" :placeholder="$t('Max')" @input="applyDebounced" />
                        </div>
                    </fieldset>

                    <label class="flex cursor-pointer items-center gap-3 text-sm font-medium">
                        <input v-model="state.in_stock" type="checkbox" class="size-4 rounded border-line accent-[var(--store-primary)]" @change="apply" />
                        {{ $t('In stock only') }}
                    </label>

                    <button v-if="chips.length" type="button" class="btn btn-outline w-full" @click="reset">{{ $t('Clear all filters') }}</button>
                    <button type="button" class="btn btn-primary w-full lg:hidden" @click="drawer = false">
                        {{ $t('Show :count results', { count: String(products.total) }) }}
                    </button>
                </div>
            </aside>

            <!-- Results -->
            <section>
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <button type="button" class="btn btn-outline !min-h-10 lg:hidden" @click="drawer = true">
                            <SlidersHorizontal class="size-4" />{{ $t('Filters') }}
                            <span v-if="chips.length" class="num grid size-5 place-items-center rounded-full bg-primary text-xs text-on-primary">{{ chips.length }}</span>
                        </button>
                        <p class="text-sm text-muted">
                            <span class="num font-semibold text-ink">{{ products.total }}</span> {{ $t('products') }}
                        </p>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <span class="hidden text-muted sm:inline">{{ $t('Sort by') }}</span>
                        <select v-model="state.sort" class="field !w-auto !py-2 pe-9" @change="apply">
                            <option v-for="s in sorts" :key="s.value" :value="s.value">{{ $t(s.label) }}</option>
                        </select>
                    </label>
                </div>

                <ul v-if="chips.length" class="mb-5 flex flex-wrap gap-2">
                    <li v-for="c in chips" :key="c.key">
                        <button type="button" class="chip chip-active !py-1 text-xs" @click="c.remove">
                            {{ $t(c.label) }}<X class="size-3.5" />
                        </button>
                    </li>
                </ul>

                <div v-if="products.data.length" class="grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-3">
                    <ProductCard v-for="p in products.data" :key="p.id" :product="p" />
                </div>
                <EmptyState v-else :icon="PackageSearch" :title="$t('No products match your filters')" :text="$t('Try removing a filter or searching for something else.')">
                    <button type="button" class="btn btn-primary" @click="reset">{{ $t('Clear all filters') }}</button>
                </EmptyState>

                <Pagination :paginator="products" path="/shop" :query="query" />
            </section>
        </div>
    </div>
</template>
