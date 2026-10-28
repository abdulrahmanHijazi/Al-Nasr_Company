<script setup lang="ts">
import type { Component } from 'vue';
import { Head } from '@inertiajs/vue3';
import type { HomeSection, SectionType } from '@/types';
import BannersSection from '@/Components/home/BannersSection.vue';
import SliderSection from '@/Components/home/SliderSection.vue';
import BrandsSection from '@/Components/home/BrandsSection.vue';
import BundlesSection from '@/Components/home/BundlesSection.vue';
import CategoriesSection from '@/Components/home/CategoriesSection.vue';
import CountersSection from '@/Components/home/CountersSection.vue';
import CtaSection from '@/Components/home/CtaSection.vue';
import FeaturesSection from '@/Components/home/FeaturesSection.vue';
import HeroSection from '@/Components/home/HeroSection.vue';
import PortfolioSection from '@/Components/home/PortfolioSection.vue';
import ProcessSection from '@/Components/home/ProcessSection.vue';
import ProductsSection from '@/Components/home/ProductsSection.vue';
import ServicesSection from '@/Components/home/ServicesSection.vue';
import TestimonialsSection from '@/Components/home/TestimonialsSection.vue';

/**
 * The home page is a list of sections configured in Filament → Home page.
 * Each has a `type` (what it shows) and a `design` (how it looks); this page
 * just renders them in order.
 */
defineProps<{ sections: HomeSection[] }>();

const registry: Record<SectionType, Component> = {
    hero: HeroSection,
    slider: SliderSection,
    features: FeaturesSection,
    categories: CategoriesSection,
    products: ProductsSection,
    services: ServicesSection,
    process: ProcessSection,
    bundles: BundlesSection,
    banners: BannersSection,
    brands: BrandsSection,
    portfolio: PortfolioSection,
    counters: CountersSection,
    testimonials: TestimonialsSection,
    cta: CtaSection,
};
</script>

<template>
    <Head :title="$t('Home')" />

    <template v-for="(section, i) in sections" :key="section.id ?? `built-in-${i}`">
        <component :is="registry[section.type]" v-if="registry[section.type]" :section="section" />
    </template>
</template>
