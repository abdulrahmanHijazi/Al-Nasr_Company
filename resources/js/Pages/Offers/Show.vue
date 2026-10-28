<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CalendarClock, FileText, MessageCircle, Tag } from 'lucide-vue-next';
import { formatDate, money, slugOf, tr } from '@/lib/locale';
import { whatsappLink } from '@/lib/contact';
import type { Bundle, SharedProps } from '@/types';
import PageHeader from '@/Components/PageHeader.vue';
import SmartImage from '@/Components/SmartImage.vue';

const props = defineProps<{ bundle: Bundle }>();

const page = usePage<SharedProps>();
const currency = computed(() => page.props.settings?.currency || 'USD');
const name = computed(() => tr(props.bundle.name));

const lines = computed(() =>
    (props.bundle.products ?? []).map((p) => {
        const qty = Number(p.pivot?.qty ?? 1);
        const unit = Number(p.price);
        const offer = p.pivot?.price_override != null ? Number(p.pivot.price_override) : null;
        return { product: p, qty, unit, offer, total: unit * qty };
    }),
);
const original = computed(() => props.bundle.original_total ?? lines.value.reduce((s, l) => s + l.total, 0));
const savings = computed(() => props.bundle.savings ?? Math.max(0, original.value - Number(props.bundle.bundle_price)));
const percent = computed(() => (original.value > 0 ? Math.round((savings.value / original.value) * 100) : 0));
const wa = computed(() => whatsappLink(page.props.settings.whatsapp_phone, name.value));
const quote = computed(() => `/contact?subject=${encodeURIComponent(name.value)}`);
</script>

<template>
    <Head :title="name" />

    <PageHeader
        :eyebrow="$t('Special offer')"
        :title="name"
        :description="tr(bundle.description)"
        :crumbs="[{ label: $t('Offers'), href: '/offers' }, { label: name }]"
    />

    <div class="container-page py-10 sm:py-14">
        <div class="grid gap-8 lg:grid-cols-[1fr_22rem]">
            <div class="space-y-8">
                <div class="card overflow-hidden">
                    <SmartImage :src="bundle.cover_url" :alt="name" :seed="name" ratio="21 / 9" eager />
                </div>

                <section>
                    <h2 class="mb-4 text-xl font-bold">{{ $t('What is included') }}</h2>
                    <ul class="card divide-y divide-line">
                        <li v-for="l in lines" :key="l.product.id" class="flex items-center gap-4 p-4">
                            <SmartImage :src="l.product.cover_url" :alt="tr(l.product.name)" :seed="tr(l.product.name)" ratio="1 / 1" class="w-16 shrink-0 rounded-xl sm:w-20" />
                            <div class="min-w-0 flex-1">
                                <Link :href="`/products/${slugOf(l.product.slug, l.product.id)}`" class="line-clamp-2 font-semibold text-ink hover:text-primary">
                                    {{ tr(l.product.name) }}
                                </Link>
                                <p class="num mt-0.5 text-xs text-muted">{{ l.product.sku }}</p>
                            </div>
                            <div class="text-end">
                                <p class="num text-sm font-semibold text-ink">{{ money(l.total, currency) }}</p>
                                <p v-if="l.qty > 1" class="num text-xs text-muted">{{ l.qty }} × {{ money(l.unit, currency) }}</p>
                            </div>
                        </li>
                    </ul>
                </section>
            </div>

            <aside>
                <div class="card overflow-hidden lg:sticky lg:top-28">
                    <div class="bg-header p-6 text-on-header">
                        <p v-if="percent" class="badge bg-secondary text-on-secondary"><Tag class="size-3.5" />{{ $t('Save :percent%', { percent: String(percent) }) }}</p>
                        <p class="num mt-3 text-4xl font-bold">{{ money(bundle.bundle_price, currency) }}</p>
                        <p v-if="savings > 0" class="num mt-1 text-sm text-white/60 line-through">{{ money(original, currency) }}</p>
                    </div>
                    <div class="space-y-4 p-6">
                        <dl class="space-y-2.5 text-sm">
                            <div class="flex justify-between"><dt class="text-muted">{{ $t('Items') }}</dt><dd class="num font-semibold">{{ lines.reduce((s, l) => s + l.qty, 0) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-muted">{{ $t('Bought separately') }}</dt><dd class="num font-semibold">{{ money(original, currency) }}</dd></div>
                            <div class="flex justify-between border-t border-line pt-2.5">
                                <dt class="font-semibold text-success">{{ $t('You save') }}</dt>
                                <dd class="num font-bold text-success">{{ money(savings, currency) }}</dd>
                            </div>
                        </dl>

                        <p v-if="bundle.ends_at" class="flex items-start gap-2 rounded-xl bg-secondary-50 p-3 text-sm text-secondary-600">
                            <CalendarClock class="mt-0.5 size-4 shrink-0" />
                            {{ $t('Offer valid until :date', { date: formatDate(bundle.ends_at, { dateStyle: 'long' }) }) }}
                        </p>

                        <Link :href="quote" class="btn btn-primary btn-lg w-full"><FileText class="size-5" />{{ $t('Request this bundle') }}</Link>
                        <a v-if="wa" :href="wa" target="_blank" rel="noopener" class="btn btn-outline w-full">
                            <MessageCircle class="size-5 text-[#25d366]" />{{ $t('Ask on WhatsApp') }}
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
