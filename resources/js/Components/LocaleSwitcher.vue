<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Globe } from 'lucide-vue-next';
import { activeLocale, setLocale, type Locale } from '@/lib/locale';

withDefaults(defineProps<{ tone?: 'dark' | 'light'; compact?: boolean }>(), { tone: 'dark', compact: false });

const target = computed<Locale>(() => (activeLocale() === 'ar' ? 'en' : 'ar'));
const label = computed(() => (target.value === 'ar' ? 'العربية' : 'English'));
const short = computed(() => (target.value === 'ar' ? 'ع' : 'EN'));

async function switchTo() {
    await setLocale(target.value);
    // Product / brand names come from the server in the active language.
    router.reload();
}
</script>

<template>
    <button
        type="button"
        class="inline-flex min-h-9 items-center gap-1.5 rounded-lg px-2.5 text-sm font-medium transition"
        :class="tone === 'dark' ? 'text-white/85 hover:bg-white/10 hover:text-white' : 'text-ink-soft hover:bg-primary-50 hover:text-primary'"
        :aria-label="`Switch language to ${label}`"
        @click="switchTo"
    >
        <Globe class="size-4" />
        <span :lang="target">{{ compact ? short : label }}</span>
    </button>
</template>
