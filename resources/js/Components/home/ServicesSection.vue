<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import { iconFor } from '@/lib/icons';
import { tr } from '@/lib/locale';
import type { HomeSection } from '@/types';
import SectionShell from '@/Components/home/SectionShell.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed<any[]>(() => props.section.content?.items ?? []);

const href = (item: any) => `/contact?subject=${encodeURIComponent(tr(item.subject) || tr(item.title))}`;
</script>

<template>
    <SectionShell v-if="items.length" :section="section" tone="surface" :eyebrow="$t('Installation services')">
        <!-- Cards -->
        <div v-if="section.design === 'cards'" class="grid gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-4">
            <Link v-for="(item, i) in items" :key="i" v-reveal="i * 70" :href="href(item)" class="card card-hover group flex flex-col p-6">
                <span class="grid size-12 place-items-center rounded-2xl bg-primary-50 text-primary transition group-hover:bg-primary group-hover:text-on-primary">
                    <component :is="iconFor(item.icon)" class="size-6" />
                </span>
                <h3 class="mt-5 text-lg font-bold text-ink">{{ tr(item.title) }}</h3>
                <p class="mt-2 flex-1 text-sm leading-relaxed text-muted">{{ tr(item.text) }}</p>
                <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-primary">
                    {{ $t('Get a quote') }}<ArrowRight class="rtl-flip size-4 transition group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                </span>
            </Link>
        </div>

        <!-- Numbered list -->
        <ol v-else class="divide-y divide-line overflow-hidden rounded-[var(--radius-card)] border border-line bg-surface">
            <li v-for="(item, i) in items" :key="i" v-reveal="i * 60">
                <Link :href="href(item)" class="group flex flex-col gap-4 p-5 transition hover:bg-primary-50 sm:flex-row sm:items-center sm:p-6">
                    <span class="num text-3xl font-bold text-secondary sm:w-14">{{ String(i + 1).padStart(2, '0') }}</span>
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-primary-50 text-primary">
                        <component :is="iconFor(item.icon)" class="size-6" />
                    </span>
                    <span class="flex-1">
                        <span class="block text-lg font-bold text-ink group-hover:text-primary">{{ tr(item.title) }}</span>
                        <span class="mt-1 block text-sm leading-relaxed text-muted">{{ tr(item.text) }}</span>
                    </span>
                    <ArrowRight class="rtl-flip hidden size-5 shrink-0 text-muted transition group-hover:translate-x-1 group-hover:text-primary sm:block rtl:group-hover:-translate-x-1" />
                </Link>
            </li>
        </ol>
    </SectionShell>
</template>
