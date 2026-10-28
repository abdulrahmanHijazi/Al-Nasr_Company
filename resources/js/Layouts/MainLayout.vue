<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { CheckCircle2, Clock, Mail, MapPin, Menu, MessageCircle, Phone, Search, X } from 'lucide-vue-next';
import { activeLocale } from '@/lib/locale';
import { telLink, whatsappLink } from '@/lib/contact';
import type { SharedProps } from '@/types';
import Background3D from '@/Components/Background3D.vue';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import SocialLinks from '@/Components/SocialLinks.vue';

const page = usePage<SharedProps>();
const settings = computed(() => page.props.settings);
const isAr = computed(() => activeLocale() === 'ar');

const siteName = computed(() => (isAr.value ? settings.value.site_name_ar : settings.value.site_name_en) || 'Al-Nasr');
const tagline = computed(() => (isAr.value ? settings.value.seo_description_ar : settings.value.seo_description_en));
const address = computed(() => (isAr.value ? settings.value.address_ar : settings.value.address_en));
const hours = computed(() => (isAr.value ? settings.value.working_hours_ar : settings.value.working_hours_en));
const wa = computed(() => whatsappLink(settings.value.whatsapp_phone, `${siteName.value} – `));

const NAV = [
    { href: '/', label: 'Home', exact: true },
    { href: '/shop', label: 'Shop' },
    { href: '/offers', label: 'Offers' },
    { href: '/brands', label: 'Brands' },
    { href: '/portfolio', label: 'Portfolio' },
    { href: '/contact', label: 'Contact' },
];

function isActive(item: (typeof NAV)[number]): boolean {
    const path = page.url.split('?')[0];
    if (item.exact) return path === item.href;
    if (item.href === '/shop') return path.startsWith('/shop') || path.startsWith('/products') || path.startsWith('/categories');
    return path.startsWith(item.href);
}

// Mobile menu + search -------------------------------------------------------
const menuOpen = ref(false);
const searchOpen = ref(false);
const query = ref('');
watch(
    () => page.url,
    () => {
        menuOpen.value = false;
        searchOpen.value = false;
    },
);

function submitSearch() {
    const q = query.value.trim();
    router.get('/shop', q ? { q } : {});
}

// Flash toast ---------------------------------------------------------------
const flash = computed(() => page.props.flash?.success ?? null);
const toastDismissed = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | undefined;
watch(flash, (value) => {
    toastDismissed.value = false;
    clearTimeout(toastTimer);
    if (value) toastTimer = setTimeout(() => (toastDismissed.value = true), 6000);
});

const year = new Date().getFullYear();
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <Background3D />

        <a href="#main" class="sr-only z-[60] rounded-lg bg-white px-4 py-2 font-semibold text-primary shadow focus:not-sr-only focus:fixed focus:start-4 focus:top-4">
            {{ $t('Skip to content') }}
        </a>

        <!-- Utility bar -->
        <div class="hidden bg-footer text-xs text-on-footer/80 md:block">
            <div class="container-page flex h-10 items-center justify-between gap-6">
                <ul class="flex items-center gap-5">
                    <li v-if="settings.contact_phone">
                        <a :href="telLink(settings.contact_phone) ?? undefined" class="flex items-center gap-1.5 transition hover:text-white">
                            <Phone class="size-3.5 text-secondary" /><span class="num">{{ settings.contact_phone }}</span>
                        </a>
                    </li>
                    <li v-if="settings.contact_email">
                        <a :href="`mailto:${settings.contact_email}`" class="flex items-center gap-1.5 transition hover:text-white">
                            <Mail class="size-3.5 text-secondary" />{{ settings.contact_email }}
                        </a>
                    </li>
                    <li v-if="hours" class="hidden items-center gap-1.5 lg:flex"><Clock class="size-3.5 text-secondary" />{{ hours }}</li>
                </ul>
                <LocaleSwitcher />
            </div>
        </div>

        <!-- Main header -->
        <header class="sticky top-0 z-40 border-b border-white/10 bg-header/95 text-on-header backdrop-blur-lg">
            <div class="container-page flex h-16 items-center gap-4 sm:h-[4.5rem]">
                <Link href="/" class="flex shrink-0 items-center gap-2.5" :aria-label="siteName">
                    <img v-if="settings.logo" :src="settings.logo" :alt="siteName" class="h-9 w-auto sm:h-10" />
                    <template v-else>
                        <span class="grid size-10 place-items-center rounded-xl bg-secondary text-lg font-extrabold text-on-secondary shadow-sm">
                            {{ siteName.charAt(0) }}
                        </span>
                        <span class="text-lg font-bold leading-none tracking-tight">{{ siteName }}</span>
                    </template>
                </Link>

                <nav class="ms-6 hidden items-center gap-1 lg:flex" :aria-label="$t('Main navigation')">
                    <Link
                        v-for="item in NAV"
                        :key="item.href"
                        :href="item.href"
                        class="relative rounded-lg px-3.5 py-2 text-sm font-medium transition"
                        :class="isActive(item) ? 'text-white' : 'text-white/70 hover:text-white'"
                        :aria-current="isActive(item) ? 'page' : undefined"
                    >
                        {{ $t(item.label) }}
                        <span v-if="isActive(item)" class="absolute inset-x-3.5 -bottom-[1.05rem] h-0.5 rounded-full bg-secondary sm:-bottom-[1.3rem]" />
                    </Link>
                </nav>

                <div class="ms-auto flex items-center gap-1.5">
                    <button
                        type="button"
                        class="grid size-10 place-items-center rounded-xl text-white/80 transition hover:bg-white/10 hover:text-white"
                        :aria-label="$t('Search products')"
                        :aria-expanded="searchOpen"
                        @click="searchOpen = !searchOpen"
                    >
                        <Search class="size-5" />
                    </button>
                    <a v-if="wa" :href="wa" target="_blank" rel="noopener" class="btn btn-secondary hidden !min-h-10 !px-4 sm:inline-flex">
                        <MessageCircle class="size-4" />{{ $t('WhatsApp') }}
                    </a>
                    <div class="md:hidden"><LocaleSwitcher compact /></div>
                    <button
                        type="button"
                        class="grid size-10 place-items-center rounded-xl text-white transition hover:bg-white/10 lg:hidden"
                        :aria-label="menuOpen ? $t('Close menu') : $t('Open menu')"
                        :aria-expanded="menuOpen"
                        @click="menuOpen = !menuOpen"
                    >
                        <X v-if="menuOpen" class="size-6" />
                        <Menu v-else class="size-6" />
                    </button>
                </div>
            </div>

            <!-- Search drawer -->
            <div v-if="searchOpen" class="border-t border-white/10 bg-header">
                <form class="container-page flex items-center gap-3 py-3" role="search" @submit.prevent="submitSearch">
                    <Search class="size-5 shrink-0 text-white/50" />
                    <input
                        v-model="query"
                        type="search"
                        autofocus
                        :placeholder="$t('Search products, SKUs, brands…')"
                        class="h-11 min-w-0 flex-1 bg-transparent text-base text-white placeholder:text-white/40 focus:outline-none"
                    />
                    <button class="btn btn-secondary !min-h-10">{{ $t('Search') }}</button>
                </form>
            </div>

            <!-- Mobile menu -->
            <div v-if="menuOpen" class="border-t border-white/10 bg-header lg:hidden">
                <nav class="container-page flex flex-col py-3" :aria-label="$t('Main navigation')">
                    <Link
                        v-for="item in NAV"
                        :key="item.href"
                        :href="item.href"
                        class="rounded-xl px-3 py-3 text-base font-medium"
                        :class="isActive(item) ? 'bg-white/10 text-white' : 'text-white/75 hover:bg-white/5'"
                    >
                        {{ $t(item.label) }}
                    </Link>
                    <a v-if="wa" :href="wa" target="_blank" rel="noopener" class="btn btn-secondary mt-3">
                        <MessageCircle class="size-4" />{{ $t('Chat on WhatsApp') }}
                    </a>
                </nav>
            </div>
        </header>

        <!-- Flash toast -->
        <Transition
            enter-active-class="transition duration-300"
            enter-from-class="-translate-y-3 opacity-0"
            leave-active-class="transition duration-200"
            leave-to-class="-translate-y-3 opacity-0"
        >
            <div v-if="flash && !toastDismissed" class="fixed inset-x-4 top-20 z-50 mx-auto max-w-md" role="status">
                <div class="flex items-start gap-3 rounded-2xl border border-success/20 bg-white p-4 shadow-[var(--shadow-lift)]">
                    <CheckCircle2 class="mt-0.5 size-5 shrink-0 text-success" />
                    <p class="flex-1 text-sm font-medium text-ink">{{ $t(flash) }}</p>
                    <button type="button" class="text-muted hover:text-ink" :aria-label="$t('Dismiss')" @click="toastDismissed = true">
                        <X class="size-4" />
                    </button>
                </div>
            </div>
        </Transition>

        <main id="main" class="flex-1"><slot /></main>

        <!-- Footer -->
        <footer class="mt-8 bg-footer text-on-footer">
            <div class="container-page grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <Link href="/" class="inline-flex items-center gap-2.5">
                        <img v-if="settings.logo" :src="settings.logo" :alt="siteName" class="h-10 w-auto" />
                        <template v-else>
                            <span class="grid size-10 place-items-center rounded-xl bg-secondary text-lg font-extrabold text-on-secondary">{{ siteName.charAt(0) }}</span>
                            <span class="text-lg font-bold tracking-tight">{{ siteName }}</span>
                        </template>
                    </Link>
                    <p v-if="tagline" class="mt-4 max-w-sm text-sm leading-relaxed text-white/60">{{ tagline }}</p>
                    <div class="mt-6"><SocialLinks :settings="settings" /></div>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-secondary rtl:tracking-normal">{{ $t('Explore') }}</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li v-for="item in NAV.slice(1)" :key="item.href">
                            <Link :href="item.href" class="text-white/65 transition hover:text-white">{{ $t(item.label) }}</Link>
                        </li>
                    </ul>
                </div>

                <div class="lg:col-span-3">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-secondary rtl:tracking-normal">{{ $t('Get in touch') }}</h3>
                    <ul class="mt-4 space-y-3 text-sm text-white/65">
                        <li v-if="address" class="flex gap-2.5"><MapPin class="mt-0.5 size-4 shrink-0 text-secondary" />{{ address }}</li>
                        <li v-if="settings.contact_phone" class="flex gap-2.5">
                            <Phone class="mt-0.5 size-4 shrink-0 text-secondary" />
                            <a :href="telLink(settings.contact_phone) ?? undefined" class="num hover:text-white">{{ settings.contact_phone }}</a>
                        </li>
                        <li v-if="settings.contact_email" class="flex gap-2.5">
                            <Mail class="mt-0.5 size-4 shrink-0 text-secondary" />
                            <a :href="`mailto:${settings.contact_email}`" class="hover:text-white">{{ settings.contact_email }}</a>
                        </li>
                        <li v-if="hours" class="flex gap-2.5"><Clock class="mt-0.5 size-4 shrink-0 text-secondary" />{{ hours }}</li>
                    </ul>
                </div>

                <div class="lg:col-span-3">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-secondary rtl:tracking-normal">{{ $t('Need a quote?') }}</h3>
                    <p class="mt-4 text-sm leading-relaxed text-white/65">{{ $t('Tell us what you need and our team will get back to you shortly.') }}</p>
                    <Link href="/contact" class="btn btn-secondary mt-5">{{ $t('Request a quote') }}</Link>
                </div>
            </div>

            <div class="border-t border-white/10">
                <div class="container-page flex flex-wrap items-center justify-between gap-3 py-5 text-xs text-white/50">
                    <p>© <span class="num">{{ year }}</span> {{ siteName }}. {{ $t('All rights reserved.') }}</p>
                    <LocaleSwitcher />
                </div>
            </div>
        </footer>

        <!-- Floating WhatsApp -->
        <a
            v-if="wa"
            :href="wa"
            target="_blank"
            rel="noopener"
            :aria-label="$t('Chat on WhatsApp')"
            class="fixed bottom-5 end-5 z-40 grid size-14 place-items-center rounded-full bg-[#25d366] text-white shadow-[var(--shadow-lift)] transition hover:scale-105 sm:hidden"
        >
            <MessageCircle class="size-7" />
        </a>
    </div>
</template>
