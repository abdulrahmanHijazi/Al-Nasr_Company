import { currentLocale, loadLanguageAsync } from 'laravel-vue-i18n';
import type { Translatable } from '@/types';

export type Locale = 'en' | 'ar';

export const LOCALES: Record<Locale, { label: string; dir: 'ltr' | 'rtl' }> = {
    en: { label: 'English', dir: 'ltr' },
    ar: { label: 'العربية', dir: 'rtl' },
};

const STORAGE_KEY = 'alnasr.locale';

export function isLocale(value: unknown): value is Locale {
    return value === 'en' || value === 'ar';
}

export function storedLocale(): Locale | null {
    try {
        const value = localStorage.getItem(STORAGE_KEY);
        return isLocale(value) ? value : null;
    } catch {
        return null;
    }
}

function applyDocument(locale: Locale) {
    const root = document.documentElement;
    root.lang = locale;
    root.dir = LOCALES[locale].dir;
}

/** Switch language client-side (translations + <html lang/dir>) and remember it. */
export async function setLocale(locale: Locale) {
    await loadLanguageAsync(locale);
    applyDocument(locale);
    try {
        localStorage.setItem(STORAGE_KEY, locale);
        // Lets a server-side SetLocale middleware pick the same language.
        document.cookie = `locale=${locale}; path=/; max-age=31536000; samesite=lax`;
    } catch {
        /* storage may be blocked – language still switches for this visit */
    }
}

export function activeLocale(): Locale {
    return isLocale(currentLocale.value) ? currentLocale.value : 'en';
}

/** Read a translatable value for the active locale, falling back gracefully. */
export function tr(value: Translatable, fallback = ''): string {
    if (value == null) return fallback;
    if (typeof value === 'string') return value;
    const locale = activeLocale();
    const other = locale === 'ar' ? 'en' : 'ar';
    return value[locale] || value[other] || Object.values(value).find(Boolean) || fallback;
}

/** Slug used in URLs — the same value for every locale if the map is shared. */
export function slugOf(value: Translatable, id: number | string): string {
    if (typeof value === 'string' && value) return value;
    if (value && typeof value === 'object') {
        return value.en || value.ar || Object.values(value).find(Boolean) || String(id);
    }
    return String(id);
}

export function money(amount: string | number | null | undefined, currency = 'USD'): string {
    const n = Number(amount ?? 0);
    try {
        return new Intl.NumberFormat(activeLocale() === 'ar' ? 'ar-u-nu-latn' : 'en-US', {
            style: 'currency',
            currency,
            maximumFractionDigits: n % 1 === 0 ? 0 : 2,
        }).format(n);
    } catch {
        return `${n.toFixed(2)} ${currency}`;
    }
}

export function formatDate(value: string | null | undefined, opts: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long' }): string {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';
    return new Intl.DateTimeFormat(activeLocale() === 'ar' ? 'ar-u-nu-latn' : 'en-US', opts).format(date);
}

export function discountPercent(price: string | number, compare?: string | number | null): number {
    const p = Number(price);
    const c = Number(compare ?? 0);
    if (!c || c <= p) return 0;
    return Math.round(((c - p) / c) * 100);
}

/** Plain text from admin-authored rich text (for line-clamped previews). */
export function stripHtml(html: string | null | undefined): string {
    return (html ?? '')
        .replace(/<[^>]*>/g, ' ')
        .replace(/&nbsp;/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}
