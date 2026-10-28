<script setup lang="ts">
import { computed } from 'vue';
import type { Brand, HomeSection } from '@/types';
import BrandCard from '@/Components/BrandCard.vue';
import SectionShell from '@/Components/home/SectionShell.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed(() => props.section.items as Brand[]);
// the strip needs the list twice to loop seamlessly
const looped = computed(() => [...items.value, ...items.value]);
</script>

<template>
    <SectionShell v-if="items.length" :section="section" :eyebrow="$t('Partners')">
        <!-- Logo grid -->
        <div v-if="section.design === 'logos'" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div v-for="(b, i) in items" :key="b.id" v-reveal="(i % 6) * 40">
                <BrandCard :brand="b" compact />
            </div>
        </div>

        <!-- Marquee -->
        <div v-else class="marquee-mask -mx-4 overflow-hidden sm:-mx-6" dir="ltr">
            <ul class="marquee-track flex w-max gap-4 px-4">
                <li v-for="(b, i) in looped" :key="`${b.id}-${i}`" class="w-44 shrink-0" :aria-hidden="i >= items.length">
                    <BrandCard :brand="b" compact />
                </li>
            </ul>
        </div>
    </SectionShell>
</template>

<style scoped>
.marquee-mask {
    mask-image: linear-gradient(to right, transparent, #000 8%, #000 92%, transparent);
}
.marquee-track {
    animation: marquee 32s linear infinite;
}
.marquee-track:hover {
    animation-play-state: paused;
}
@keyframes marquee {
    to {
        transform: translateX(-50%);
    }
}
@media (prefers-reduced-motion: reduce) {
    .marquee-track {
        animation: none;
    }
}
</style>
