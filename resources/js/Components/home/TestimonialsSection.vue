<script setup lang="ts">
import { computed, ref } from 'vue';
import { Quote } from 'lucide-vue-next';
import { tr } from '@/lib/locale';
import type { HomeSection } from '@/types';
import SectionShell from '@/Components/home/SectionShell.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed<any[]>(() => props.section.content?.items ?? []);

const current = ref(0);
const active = computed(() => items.value[current.value]);
const initial = (name: string) => (name || '?').trim().charAt(0).toUpperCase();
</script>

<template>
    <SectionShell v-if="items.length" :section="section" tone="surface">
        <!-- Cards -->
        <div v-if="section.design === 'cards'" class="grid gap-5 md:grid-cols-3">
            <figure v-for="(t, i) in items.slice(0, 6)" :key="i" v-reveal="(i % 3) * 70" class="card flex flex-col p-6">
                <Quote class="size-8 text-secondary" />
                <blockquote class="mt-3 flex-1 text-sm leading-relaxed text-ink-soft">{{ tr(t.quote) }}</blockquote>
                <figcaption class="mt-5 flex items-center gap-3">
                    <img v-if="t.image" :src="t.image" :alt="t.name" class="size-11 rounded-full object-cover" />
                    <span v-else class="grid size-11 place-items-center rounded-full bg-primary-50 font-bold text-primary">{{ initial(t.name) }}</span>
                    <span>
                        <span class="block text-sm font-semibold text-ink">{{ t.name }}</span>
                        <span class="block text-xs text-muted">{{ tr(t.role) }}</span>
                    </span>
                </figcaption>
            </figure>
        </div>

        <!-- Large quote -->
        <figure v-else class="mx-auto max-w-3xl text-center">
            <Quote class="mx-auto size-10 text-secondary" />
            <blockquote :key="current" class="mt-4 text-xl font-medium leading-relaxed text-ink sm:text-2xl">{{ tr(active.quote) }}</blockquote>
            <figcaption class="mt-6 flex flex-col items-center gap-3">
                <img v-if="active.image" :src="active.image" :alt="active.name" class="size-14 rounded-full object-cover" />
                <span v-else class="grid size-14 place-items-center rounded-full bg-primary-50 text-lg font-bold text-primary">{{ initial(active.name) }}</span>
                <span>
                    <span class="block font-semibold text-ink">{{ active.name }}</span>
                    <span class="block text-sm text-muted">{{ tr(active.role) }}</span>
                </span>
            </figcaption>
            <div v-if="items.length > 1" class="mt-6 flex justify-center gap-2">
                <button
                    v-for="(_, i) in items"
                    :key="i"
                    type="button"
                    class="h-2 rounded-full transition-all"
                    :class="i === current ? 'w-8 bg-primary' : 'w-2 bg-line hover:bg-muted'"
                    :aria-label="`Testimonial ${i + 1}`"
                    @click="current = i"
                />
            </div>
        </figure>
    </SectionShell>
</template>
