import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { i18nVue, loadLanguageAsync } from 'laravel-vue-i18n';
import MainLayout from '@/Layouts/MainLayout.vue';
import { reveal } from '@/directives/reveal';
import { isLocale, setLocale, storedLocale, type Locale } from '@/lib/locale';
import { applyTheme } from '@/lib/theme';
import type { SharedProps } from '@/types';

const langFiles = import.meta.glob('../../lang/*.json');

createInertiaApp({
    title: (title) => (title ? `${title} · Al-Nasr` : 'Al-Nasr Company'),
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${name}.vue`];
        if (!page) throw new Error(`Page not found: ${name}`);
        page.default.layout ??= MainLayout;
        return page;
    },
    async setup({ el, App, props, plugin }) {
        const shared = props.initialPage.props as unknown as SharedProps;

        // Visitor's choice (localStorage) wins over the server default.
        const wanted = storedLocale() ?? (isLocale(shared.locale) ? shared.locale : 'en');
        const locale: Locale = wanted;

        applyTheme(shared.settings ?? {});

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18nVue, {
                lang: 'en',
                fallbackLang: 'en',
                resolve: async (lang: string) => {
                    const loader = langFiles[`../../lang/${lang}.json`];
                    return (loader ? await loader() : { default: {} }) as { default: Record<string, string> };
                },
            })
            .directive('reveal', reveal);

        // Load the dictionary *before* first paint so Arabic never flashes English.
        await loadLanguageAsync('en');
        await setLocale(locale);

        app.mount(el);
    },
    progress: {
        color: 'var(--store-secondary)',
        showSpinner: false,
    },
});
