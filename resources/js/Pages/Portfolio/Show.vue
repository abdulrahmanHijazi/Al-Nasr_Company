<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, ExternalLink, User } from 'lucide-vue-next';
import { formatDate, tr } from '@/lib/locale';
import type { PortfolioItem } from '@/types';
import PageHeader from '@/Components/PageHeader.vue';
import SmartImage from '@/Components/SmartImage.vue';

const props = defineProps<{ item: PortfolioItem }>();

const title = computed(() => tr(props.item.title));
const gallery = computed(() => props.item.gallery ?? []);
const lightbox = ref<string | null>(null);
</script>

<template>
    <Head :title="title" />

    <PageHeader
        :eyebrow="$t('Case study')"
        :title="title"
        :image="item.cover_url"
        :crumbs="[{ label: $t('Portfolio'), href: '/portfolio' }, { label: title }]"
    />

    <div class="container-page py-10 sm:py-14">
        <div class="grid gap-10 lg:grid-cols-[1fr_20rem]">
            <article>
                <div class="card overflow-hidden">
                    <SmartImage :src="item.cover_url" :alt="title" :seed="title" ratio="16 / 9" eager />
                </div>
                <div v-if="item.description" class="prose prose-slate mt-8 max-w-none prose-a:text-primary" v-html="tr(item.description)" />

                <div v-if="gallery.length" class="mt-10">
                    <h2 class="mb-4 text-xl font-bold">{{ $t('Gallery') }}</h2>
                    <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <li v-for="(src, i) in gallery" :key="src">
                            <button type="button" class="block w-full overflow-hidden rounded-xl" :aria-label="`${title} ${i + 1}`" @click="lightbox = src">
                                <SmartImage :src="src" :alt="title" :seed="title" ratio="4 / 3" class="transition hover:opacity-90" />
                            </button>
                        </li>
                    </ul>
                </div>
            </article>

            <aside>
                <div class="card space-y-5 p-6 lg:sticky lg:top-28">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-primary rtl:tracking-normal">{{ $t('Project details') }}</h2>
                    <dl class="space-y-4 text-sm">
                        <div v-if="item.client_name" class="flex gap-3">
                            <User class="mt-0.5 size-4 shrink-0 text-muted" />
                            <div><dt class="text-muted">{{ $t('Client') }}</dt><dd class="font-semibold">{{ tr(item.client_name) }}</dd></div>
                        </div>
                        <div v-if="item.completed_at" class="flex gap-3">
                            <Calendar class="mt-0.5 size-4 shrink-0 text-muted" />
                            <div><dt class="text-muted">{{ $t('Completed') }}</dt><dd class="font-semibold">{{ formatDate(item.completed_at) }}</dd></div>
                        </div>
                    </dl>
                    <a v-if="item.project_url" :href="item.project_url" target="_blank" rel="noopener noreferrer" class="btn btn-outline w-full">
                        {{ $t('View project') }}<ExternalLink class="size-4" />
                    </a>
                    <Link href="/contact" class="btn btn-primary w-full">{{ $t('Start a similar project') }}</Link>
                </div>
                <Link href="/portfolio" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-primary">
                    <ArrowLeft class="rtl-flip size-4" />{{ $t('All projects') }}
                </Link>
            </aside>
        </div>
    </div>

    <!-- Lightbox -->
    <div v-if="lightbox" class="fixed inset-0 z-50 grid place-items-center bg-ink/90 p-4" role="dialog" aria-modal="true" @click="lightbox = null" @keydown.esc="lightbox = null">
        <img :src="lightbox" :alt="title" class="max-h-full max-w-full rounded-xl object-contain" />
    </div>
</template>
