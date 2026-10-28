<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Clock, Mail, MapPin, MessageCircle, Navigation, Phone, Send } from 'lucide-vue-next';
import { activeLocale } from '@/lib/locale';
import { directionsUrl, osmEmbedUrl, telLink, whatsappLink } from '@/lib/contact';
import type { SharedProps } from '@/types';
import PageHeader from '@/Components/PageHeader.vue';

const page = usePage<SharedProps>();
const settings = computed(() => page.props.settings);
const isAr = computed(() => activeLocale() === 'ar');

const address = computed(() => (isAr.value ? settings.value.address_ar : settings.value.address_en));
const hours = computed(() => (isAr.value ? settings.value.working_hours_ar : settings.value.working_hours_en));
const wa = computed(() => whatsappLink(settings.value.whatsapp_phone));
const directions = computed(() => directionsUrl(settings.value.latitude, settings.value.longitude, settings.value.google_maps_url));
const map = computed(() =>
    settings.value.latitude != null && settings.value.longitude != null
        ? osmEmbedUrl(Number(settings.value.latitude), Number(settings.value.longitude))
        : null,
);

// ?subject=… is set by the "Request a quote" buttons on product / offer pages.
const prefill = typeof window !== 'undefined' ? new URLSearchParams(window.location.search).get('subject') : null;

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: prefill ?? '',
    message: prefill ? `${activeLocale() === 'ar' ? 'أرغب في الحصول على عرض سعر لـ' : "I'd like a quote for"}: ${prefill}\n` : '',
});

function submit() {
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => {
            // reset() would restore the ?subject= prefill; start the next message clean.
            form.defaults({ name: '', email: '', phone: '', subject: '', message: '' });
            form.reset();
        },
    });
}

// One-tap enquiry types (pre-fill the subject line).
const enquiryTypes = [
    'Solar power system',
    'Lithium batteries',
    'Inverters & power systems',
    'Industrial control panel',
    'Surveillance / alarm system',
    'Other',
];

const cards = computed(() =>
    [
        settings.value.contact_phone && {
            icon: Phone,
            label: 'Phone',
            value: settings.value.contact_phone,
            href: telLink(settings.value.contact_phone),
            num: true,
        },
        wa.value && { icon: MessageCircle, label: 'WhatsApp', value: settings.value.whatsapp_phone, href: wa.value, num: true },
        settings.value.contact_email && {
            icon: Mail,
            label: 'Email',
            value: settings.value.contact_email,
            href: `mailto:${settings.value.contact_email}`,
        },
        hours.value && { icon: Clock, label: 'Working hours', value: hours.value, href: null },
        address.value && { icon: MapPin, label: 'Address', value: address.value, href: directions.value },
    ].filter(Boolean) as { icon: typeof Phone; label: string; value: string; href: string | null; num?: boolean }[],
);
</script>

<template>
    <Head :title="$t('Contact')" />

    <PageHeader
        :eyebrow="$t('Contact')"
        :title="$t('Let us know how we can help')"
        :description="$t('Send us a message or reach out directly — we usually reply within one working day.')"
        :crumbs="[{ label: $t('Contact') }]"
    />

    <div class="container-page py-10 sm:py-14">
        <div class="grid gap-8 lg:grid-cols-5 lg:gap-10">
            <!-- Form -->
            <div class="lg:col-span-3">
                <div class="card p-6 sm:p-8">
                    <form class="grid gap-5 sm:grid-cols-2" novalidate @submit.prevent="submit">
                        <div>
                            <label for="name" class="field-label">{{ $t('Full name') }} <span class="text-danger">*</span></label>
                            <input id="name" v-model="form.name" type="text" autocomplete="name" required class="field" :class="form.errors.name && 'border-danger'" />
                            <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label for="phone" class="field-label">{{ $t('Phone') }}</label>
                            <input id="phone" v-model="form.phone" type="tel" autocomplete="tel" class="field num" :class="form.errors.phone && 'border-danger'" />
                            <p v-if="form.errors.phone" class="field-error">{{ form.errors.phone }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="email" class="field-label">{{ $t('Email') }}</label>
                            <input id="email" v-model="form.email" type="email" autocomplete="email" class="field" :class="form.errors.email && 'border-danger'" />
                            <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="field-label">{{ $t('What do you need?') }}</p>
                            <ul class="flex flex-wrap gap-2">
                                <li v-for="type in enquiryTypes" :key="type">
                                    <button
                                        type="button"
                                        class="chip"
                                        :class="form.subject === $t(type) && 'chip-active'"
                                        :aria-pressed="form.subject === $t(type)"
                                        @click="form.subject = $t(type)"
                                    >
                                        {{ $t(type) }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="subject" class="field-label">{{ $t('Subject') }}</label>
                            <input id="subject" v-model="form.subject" type="text" class="field" :class="form.errors.subject && 'border-danger'" />
                            <p v-if="form.errors.subject" class="field-error">{{ form.errors.subject }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="message" class="field-label">{{ $t('Message') }} <span class="text-danger">*</span></label>
                            <textarea id="message" v-model="form.message" rows="6" required class="field" :class="form.errors.message && 'border-danger'" />
                            <p v-if="form.errors.message" class="field-error">{{ form.errors.message }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="btn btn-primary btn-lg w-full sm:w-auto" :disabled="form.processing">
                                <Send class="rtl-flip size-5" />{{ form.processing ? $t('Sending…') : $t('Send message') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info -->
            <div class="space-y-5 lg:col-span-2">
                <ul class="card divide-y divide-line">
                    <li v-for="c in cards" :key="c.label" class="flex items-start gap-4 p-5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-primary-50 text-primary">
                            <component :is="c.icon" class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted rtl:tracking-normal">{{ $t(c.label) }}</p>
                            <a v-if="c.href" :href="c.href" class="mt-0.5 block break-words font-semibold text-ink transition hover:text-primary" :class="c.num && 'num'">{{ c.value }}</a>
                            <p v-else class="mt-0.5 font-semibold text-ink">{{ c.value }}</p>
                        </div>
                    </li>
                </ul>

                <div v-if="map" class="card overflow-hidden">
                    <iframe :src="map" :title="$t('Our location')" class="block aspect-[4/3] w-full border-0" loading="lazy" referrerpolicy="no-referrer" />
                    <a v-if="directions" :href="directions" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 border-t border-line p-4 text-sm font-semibold text-primary transition hover:bg-primary-50">
                        <Navigation class="size-4" />{{ $t('Get directions') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
