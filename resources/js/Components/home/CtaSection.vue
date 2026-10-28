<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { MessageCircle } from 'lucide-vue-next';
import { tr } from '@/lib/locale';
import { whatsappLink } from '@/lib/contact';
import type { HomeSection, SharedProps } from '@/types';
import Section3D from '@/Components/home/Section3D.vue';

/** Designs: primary (rounded colour card), split (dark with a side panel), minimal (text only). */
const props = defineProps<{ section: HomeSection }>();

const page = usePage<SharedProps>();
const c = computed(() => props.section.content ?? {});
const wa = computed(() => (c.value.show_whatsapp === false ? null : whatsappLink(page.props.settings.whatsapp_phone)));
const primaryUrl = computed(() => c.value.primary_url || '/contact');
</script>

<template>
    <section class="section" :style="section.background_color ? { backgroundColor: section.background_color } : undefined">
        <div class="container-page">
            <!-- Primary -->
            <div v-if="section.design === 'primary'" class="relative isolate overflow-hidden rounded-3xl bg-primary px-6 py-12 text-on-primary sm:px-12 sm:py-16">
                <div class="bg-grid absolute inset-0 -z-10" aria-hidden="true" />
                <div class="absolute -end-16 -top-16 -z-10 size-64 rounded-full bg-secondary/40 blur-3xl" aria-hidden="true" />
                <div class="flex flex-col items-start justify-between gap-8 md:flex-row md:items-center">
                    <div class="max-w-xl">
                        <h2 class="text-2xl font-bold leading-tight sm:text-3xl">{{ tr(c.title) }}</h2>
                        <p v-if="tr(c.text)" class="mt-3 text-base opacity-85">{{ tr(c.text) }}</p>
                    </div>
                    <Section3D v-if="section.model_3d" :model="section.model_3d" size="inline" class="-my-6 ms-auto" />
                    <div class="flex flex-wrap gap-3">
                        <Link v-if="tr(c.primary_label)" :href="primaryUrl" class="btn btn-secondary btn-lg">{{ tr(c.primary_label) }}</Link>
                        <Link v-if="tr(c.secondary_label) && c.secondary_url" :href="c.secondary_url" class="btn btn-on-dark btn-lg">{{ tr(c.secondary_label) }}</Link>
                        <a v-if="wa" :href="wa" target="_blank" rel="noopener" class="btn btn-on-dark btn-lg"><MessageCircle class="size-5" />{{ $t('WhatsApp') }}</a>
                    </div>
                </div>
            </div>

            <!-- Split -->
            <div v-else-if="section.design === 'split'" class="grid overflow-hidden rounded-3xl bg-header text-on-header md:grid-cols-5">
                <div class="relative isolate flex items-center justify-between gap-6 p-8 sm:p-12 md:col-span-3">
                    <div class="bg-grid absolute inset-0 -z-10" aria-hidden="true" />
                    <div>
                        <h2 class="text-2xl font-bold leading-tight sm:text-3xl">{{ tr(c.title) }}</h2>
                        <p v-if="tr(c.text)" class="mt-3 max-w-lg text-white/70">{{ tr(c.text) }}</p>
                    </div>
                    <Section3D v-if="section.model_3d" :model="section.model_3d" size="inline" class="-my-6" />
                </div>
                <div class="flex flex-col justify-center gap-3 bg-white/5 p-8 sm:p-12 md:col-span-2">
                    <Link v-if="tr(c.primary_label)" :href="primaryUrl" class="btn btn-secondary btn-lg w-full">{{ tr(c.primary_label) }}</Link>
                    <Link v-if="tr(c.secondary_label) && c.secondary_url" :href="c.secondary_url" class="btn btn-on-dark btn-lg w-full">{{ tr(c.secondary_label) }}</Link>
                    <a v-if="wa" :href="wa" target="_blank" rel="noopener" class="btn btn-on-dark btn-lg w-full"><MessageCircle class="size-5" />{{ $t('WhatsApp') }}</a>
                </div>
            </div>

            <!-- Minimal -->
            <div v-else class="mx-auto max-w-2xl border-y border-line py-12 text-center">
                <h2 class="text-2xl font-bold leading-tight text-ink sm:text-3xl">{{ tr(c.title) }}</h2>
                <p v-if="tr(c.text)" class="mt-3 text-muted">{{ tr(c.text) }}</p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <Link v-if="tr(c.primary_label)" :href="primaryUrl" class="btn btn-primary btn-lg">{{ tr(c.primary_label) }}</Link>
                    <a v-if="wa" :href="wa" target="_blank" rel="noopener" class="btn btn-outline btn-lg"><MessageCircle class="size-5 text-[#25d366]" />{{ $t('WhatsApp') }}</a>
                </div>
            </div>
        </div>
    </section>
</template>
