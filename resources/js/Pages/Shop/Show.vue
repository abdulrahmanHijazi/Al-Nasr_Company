<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Check, FileText, MessageCircle, PackageCheck, PackageX, Phone, ShieldCheck, Truck } from 'lucide-vue-next';
import { slugOf, tr } from '@/lib/locale';
import { telLink, whatsappLink } from '@/lib/contact';
import { trans } from 'laravel-vue-i18n';
import type { Bundle, Product, SharedProps } from '@/types';
import BundleCard from '@/Components/BundleCard.vue';
import Breadcrumbs, { type Crumb } from '@/Components/Breadcrumbs.vue';
import Price from '@/Components/Price.vue';
import ProductCard from '@/Components/ProductCard.vue';
import SectionHeading from '@/Components/SectionHeading.vue';
import SmartImage from '@/Components/SmartImage.vue';

const props = withDefaults(
    defineProps<{ product: Product & { bundles?: Bundle[] }; related?: Product[] }>(),
    { related: () => [] },
);

const page = usePage<SharedProps>();
const settings = computed(() => page.props.settings);

const name = computed(() => tr(props.product.name));
const inStock = computed(() => props.product.stock_qty > 0);
const lowStock = computed(() => inStock.value && props.product.stock_qty <= 5);

// Gallery ---------------------------------------------------------------------
const images = computed(() => {
    const list = [props.product.cover_url, ...(props.product.gallery ?? [])].filter((u): u is string => !!u);
    return [...new Set(list)];
});
const current = ref(0);
watch(
    () => props.product.id,
    () => (current.value = 0),
);

// Tabs ------------------------------------------------------------------------
const tab = ref<'description' | 'details'>('description');
const hasDescription = computed(() => !!tr(props.product.description));
watch(
    hasDescription,
    (has) => {
        if (!has) tab.value = 'details';
    },
    { immediate: true },
);

const details = computed(() => {
    const rows: { label: string; value: string }[] = [{ label: 'SKU', value: props.product.sku }];
    if (props.product.brand) rows.push({ label: 'Brand', value: tr(props.product.brand.name) });
    if (props.product.categories?.length) rows.push({ label: 'Categories', value: props.product.categories.map((c) => tr(c.name)).join(', ') });
    rows.push({ label: 'Availability', value: inStock.value ? 'In stock' : 'Out of stock' });
    return rows;
});

// Actions ---------------------------------------------------------------------
const quoteHref = computed(() => `/contact?subject=${encodeURIComponent(`${name.value} (${props.product.sku})`)}`);
const waHref = computed(() =>
    whatsappLink(
        settings.value.whatsapp_phone,
        `${name.value} (${props.product.sku})${typeof window !== 'undefined' ? `\n${window.location.href}` : ''}`,
    ),
);

const crumbs = computed<Crumb[]>(() => {
    const out: Crumb[] = [{ label: trans('Shop'), href: '/shop' }];
    const cat = props.product.categories?.[0];
    if (cat) out.push({ label: tr(cat.name), href: `/categories/${slugOf(cat.slug, cat.id)}` });
    out.push({ label: name.value });
    return out;
});
</script>

<template>
    <Head :title="name" />

    <Breadcrumbs :crumbs="crumbs" />

    <div class="container-page py-8 sm:py-12">
        <div class="grid gap-8 lg:grid-cols-2 lg:gap-14">
            <!-- Gallery -->
            <div class="lg:sticky lg:top-28 lg:self-start">
                <div class="card overflow-hidden">
                    <SmartImage :src="images[current]" :alt="name" :seed="name" ratio="1 / 1" fit="contain" eager />
                </div>
                <ul v-if="images.length > 1" class="mt-3 grid grid-cols-5 gap-2.5">
                    <li v-for="(src, i) in images" :key="src">
                        <button
                            type="button"
                            class="block w-full overflow-hidden rounded-xl border-2 transition"
                            :class="i === current ? 'border-primary' : 'border-transparent opacity-70 hover:opacity-100'"
                            :aria-label="`${name} ${i + 1}`"
                            @click="current = i"
                        >
                            <SmartImage :src="src" :alt="''" :seed="name" ratio="1 / 1" />
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Info -->
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        v-if="product.brand"
                        :href="`/brands/${slugOf(product.brand.slug, product.brand.id)}`"
                        class="badge bg-primary-50 text-primary hover:bg-primary-100"
                    >
                        {{ tr(product.brand.name) }}
                    </Link>
                    <span class="num text-sm text-muted">SKU {{ product.sku }}</span>
                </div>

                <h1 class="mt-3 text-2xl font-bold leading-tight tracking-tight text-ink sm:text-3xl">{{ name }}</h1>
                <p v-if="product.short_description" class="mt-3 text-base leading-relaxed text-ink-soft">{{ tr(product.short_description) }}</p>

                <div class="mt-6 flex flex-wrap items-center gap-4 border-y border-line py-5">
                    <Price :amount="product.price" :compare="product.compare_price" size="xl" show-discount />
                    <span
                        class="badge ms-auto gap-1.5 px-3 py-1.5 text-sm"
                        :class="inStock ? (lowStock ? 'bg-secondary-100 text-secondary-600' : 'bg-success/10 text-success') : 'bg-danger/10 text-danger'"
                    >
                        <component :is="inStock ? PackageCheck : PackageX" class="size-4" />
                        {{ !inStock ? $t('Out of stock') : lowStock ? $t('Only :count left', { count: String(product.stock_qty) }) : $t('In stock') }}
                    </span>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <Link :href="quoteHref" class="btn btn-primary btn-lg flex-1"><FileText class="size-5" />{{ $t('Request a quote') }}</Link>
                    <a v-if="waHref" :href="waHref" target="_blank" rel="noopener" class="btn btn-outline btn-lg flex-1">
                        <MessageCircle class="size-5 text-[#25d366]" />{{ $t('Ask on WhatsApp') }}
                    </a>
                    <a v-else-if="telLink(settings.contact_phone)" :href="telLink(settings.contact_phone)!" class="btn btn-outline btn-lg flex-1">
                        <Phone class="size-5" />{{ $t('Call us') }}
                    </a>
                </div>

                <ul class="mt-6 grid gap-3 text-sm text-ink-soft sm:grid-cols-2">
                    <li class="flex items-center gap-2.5"><ShieldCheck class="size-5 text-primary" />{{ $t('Genuine, brand-backed product') }}</li>
                    <li class="flex items-center gap-2.5"><Truck class="size-5 text-primary" />{{ $t('Delivery arranged with your quote') }}</li>
                </ul>

                <!-- Tabs -->
                <div class="mt-10">
                    <div class="flex gap-1 border-b border-line" role="tablist">
                        <button
                            v-if="hasDescription"
                            role="tab"
                            :aria-selected="tab === 'description'"
                            class="-mb-px border-b-2 px-4 py-3 text-sm font-semibold transition"
                            :class="tab === 'description' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-ink'"
                            @click="tab = 'description'"
                        >
                            {{ $t('Description') }}
                        </button>
                        <button
                            role="tab"
                            :aria-selected="tab === 'details'"
                            class="-mb-px border-b-2 px-4 py-3 text-sm font-semibold transition"
                            :class="tab === 'details' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-ink'"
                            @click="tab = 'details'"
                        >
                            {{ $t('Details') }}
                        </button>
                    </div>

                    <!-- Admin-authored rich text from Filament -->
                    <div v-if="tab === 'description'" class="prose prose-slate mt-6 max-w-none prose-a:text-primary" v-html="tr(product.description)" />
                    <dl v-else class="mt-2 divide-y divide-line">
                        <div v-for="row in details" :key="row.label" class="grid grid-cols-3 gap-4 py-3.5 text-sm">
                            <dt class="text-muted">{{ $t(row.label) }}</dt>
                            <dd class="num col-span-2 flex items-center gap-1.5 font-medium text-ink">
                                <Check v-if="row.label === 'Availability' && inStock" class="size-4 text-success" />{{ $t(row.value) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- In bundles -->
        <section v-if="product.bundles?.length" class="mt-16">
            <SectionHeading :eyebrow="$t('Save more')" :title="$t('Also available in bundles')" />
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <BundleCard v-for="b in product.bundles" :key="b.id" :bundle="b" />
            </div>
        </section>

        <!-- Related -->
        <section v-if="related.length" class="mt-16">
            <SectionHeading :eyebrow="$t('Keep exploring')" :title="$t('You may also like')" />
            <div class="grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-4">
                <ProductCard v-for="p in related.slice(0, 4)" :key="p.id" :product="p" />
            </div>
        </section>
    </div>
</template>
