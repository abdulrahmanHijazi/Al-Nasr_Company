<script setup lang="ts">
import { computed, ref } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import type { HomeSection, Product } from '@/types';
import ProductCard from '@/Components/ProductCard.vue';
import SectionShell from '@/Components/home/SectionShell.vue';

const props = defineProps<{ section: HomeSection }>();
const items = computed(() => props.section.items as Product[]);

const track = ref<HTMLElement | null>(null);
function scrollBy(direction: 1 | -1) {
    const el = track.value;
    if (!el) return;
    const rtl = getComputedStyle(el).direction === 'rtl';
    el.scrollBy({ left: direction * (rtl ? -1 : 1) * el.clientWidth * 0.8, behavior: 'smooth' });
}
</script>

<template>
    <SectionShell v-if="items.length" :section="section" tone="surface" :eyebrow="$t('Featured')">
        <!-- Grid -->
        <div v-if="section.design === 'grid'" class="grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-3 lg:grid-cols-4">
            <div v-for="(p, i) in items" :key="p.id" v-reveal="(i % 4) * 60">
                <ProductCard :product="p" />
            </div>
        </div>

        <!-- Carousel -->
        <div v-else class="relative">
            <div ref="track" class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 [scrollbar-width:none] sm:-mx-6 sm:px-6 [&::-webkit-scrollbar]:hidden">
                <div v-for="p in items" :key="p.id" class="w-[62%] shrink-0 snap-start sm:w-[34%] lg:w-[23.5%]">
                    <ProductCard :product="p" />
                </div>
            </div>
            <div class="mt-2 hidden justify-end gap-2 sm:flex">
                <button type="button" class="btn btn-outline !min-h-10 !px-3" aria-label="Previous" @click="scrollBy(-1)"><ChevronLeft class="rtl-flip size-5" /></button>
                <button type="button" class="btn btn-outline !min-h-10 !px-3" aria-label="Next" @click="scrollBy(1)"><ChevronRight class="rtl-flip size-5" /></button>
            </div>
        </div>
    </SectionShell>
</template>
