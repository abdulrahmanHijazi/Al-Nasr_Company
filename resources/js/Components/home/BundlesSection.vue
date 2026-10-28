<script setup lang="ts">
import { computed } from 'vue';
import type { Bundle, HomeSection } from '@/types';
import BundleCard from '@/Components/BundleCard.vue';
import SectionShell from '@/Components/home/SectionShell.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed(() => props.section.items as Bundle[]);
</script>

<template>
    <SectionShell v-if="items.length" :section="section" :tone="section.design === 'band' ? 'dark' : 'canvas'" :eyebrow="$t('Special offers')">
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <div v-for="(b, i) in items" :key="b.id" v-reveal="i * 80">
                <BundleCard :bundle="b" />
            </div>
        </div>
    </SectionShell>
</template>
