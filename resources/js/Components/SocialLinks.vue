<script setup lang="ts">
import { computed, type Component } from 'vue';
import { Facebook, Ghost, Instagram, Linkedin, Music2, Send, Youtube } from 'lucide-vue-next';
import type { SiteSettings } from '@/types';

const props = defineProps<{ settings: SiteSettings }>();

type SocialKey = 'facebook_url' | 'instagram_url' | 'x_url' | 'linkedin_url' | 'youtube_url' | 'tiktok_url' | 'snapchat_url' | 'telegram_url';

// X has no lucide glyph; this is its mark as a single path.
const X_PATH = 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z';

const META: Record<SocialKey, { label: string; icon?: Component }> = {
    facebook_url: { label: 'Facebook', icon: Facebook },
    instagram_url: { label: 'Instagram', icon: Instagram },
    x_url: { label: 'X' },
    linkedin_url: { label: 'LinkedIn', icon: Linkedin },
    youtube_url: { label: 'YouTube', icon: Youtube },
    tiktok_url: { label: 'TikTok', icon: Music2 },
    snapchat_url: { label: 'Snapchat', icon: Ghost },
    telegram_url: { label: 'Telegram', icon: Send },
};

const links = computed(() =>
    (Object.keys(META) as SocialKey[])
        .map((key) => ({ key, ...META[key], href: props.settings[key] }))
        .filter((l) => !!l.href),
);
</script>

<template>
    <ul v-if="links.length" class="flex flex-wrap items-center gap-2">
        <li v-for="l in links" :key="l.key">
            <a
                :href="l.href!"
                target="_blank"
                rel="noopener noreferrer"
                :aria-label="l.label"
                class="grid size-10 place-items-center rounded-xl border border-white/15 bg-white/5 text-white/80 transition hover:-translate-y-0.5 hover:border-secondary hover:bg-secondary hover:text-on-secondary"
            >
                <component :is="l.icon" v-if="l.icon" class="size-[18px]" />
                <svg v-else viewBox="0 0 24 24" class="size-4" fill="currentColor" aria-hidden="true"><path :d="X_PATH" /></svg>
            </a>
        </li>
    </ul>
</template>
