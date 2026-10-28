/**
 * Design preview — `npm run preview`
 *
 * Serves the real Inertia + Vue storefront against sample data
 * (resources/js/mocks) so the UI can be reviewed and iterated on without PHP,
 * a database or the Filament panel. It speaks the Inertia protocol, so Links,
 * filters, forms and page transitions behave exactly as in production.
 */
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath } from 'node:url';
import { defineConfig, type Plugin } from 'vite';

const VERSION = 'preview';

function mockInertia(): Plugin {
    return {
        name: 'alnasr:mock-inertia',
        configureServer(server) {
            server.middlewares.use(async (req, res, next) => {
                if (!req.url || !['GET', 'POST'].includes(req.method ?? '')) return next();
                const url = new URL(req.url, 'http://localhost');
                const isInertia = !!req.headers['x-inertia'];
                const wantsHtml = (req.headers.accept ?? '').includes('text/html');
                if (!isInertia && !wantsHtml) return next();

                const { resolveMock, shared } = (await server.ssrLoadModule('/resources/js/mocks/routes.ts')) as typeof import('./resources/js/mocks/routes');

                // Contact form: pretend it saved, then redirect with a flash message.
                if (req.method === 'POST' && url.pathname === '/contact') {
                    res.statusCode = 303;
                    res.setHeader('Location', '/contact?sent=1');
                    return res.end();
                }
                if (req.method !== 'GET') return next();

                const mock = resolveMock(url.pathname, url.searchParams);
                if (!mock) return next();

                const page = {
                    component: mock.component,
                    props: { ...shared(url.searchParams.get('sent') ? 'Message sent successfully.' : null, url.searchParams), ...mock.props },
                    url: url.pathname + url.search,
                    version: VERSION,
                };

                if (isInertia) {
                    res.setHeader('Content-Type', 'application/json');
                    res.setHeader('X-Inertia', 'true');
                    res.setHeader('Vary', 'X-Inertia');
                    return res.end(JSON.stringify(page));
                }

                const settings = (page.props as ReturnType<typeof shared>).settings;
                const html = await server.transformIndexHtml(
                    req.url,
                    `<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>:root{--store-primary:${settings.primary_color};--store-secondary:${settings.secondary_color};--store-header:${settings.header_color};--store-footer:${settings.footer_color}}</style>
    <link rel="stylesheet" href="/resources/css/app.css">
    <script type="module" src="/resources/js/app.ts"></script>
</head>
<body>
    <div id="app" data-page="${JSON.stringify(page).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;')}"></div>
</body>
</html>`,
                );
                res.setHeader('Content-Type', 'text/html; charset=utf-8');
                res.end(html);
            });
        },
    };
}

const toPosix = (path: string) => path.split('\\').join('/');
const root = toPosix(fileURLToPath(new URL('.', import.meta.url))).replace(/\/+$/, '');

export default defineConfig({
    appType: 'custom',
    resolve: { alias: { '@': fileURLToPath(new URL('./resources/js', import.meta.url)) } },
    server: {
        port: 5199,
        strictPort: false,
        watch: {
            // Never react to files outside the project (editors, indexers, other apps).
            ignored: (path: string) => {
                const p = toPosix(path);
                const inside = p.startsWith(root);
                const ancestor = root.startsWith(p);
                return !inside && !ancestor;
            },
        },
    },
    // The HTML is served by the mock middleware, so Vite can't pre-scan imports;
    // listing them avoids a mid-session re-optimise and "Outdated Optimize Dep".
    optimizeDeps: { include: ['vue', '@inertiajs/vue3', 'laravel-vue-i18n', 'lucide-vue-next'] },
    plugins: [tailwindcss(), vue(), mockInertia()],
});
