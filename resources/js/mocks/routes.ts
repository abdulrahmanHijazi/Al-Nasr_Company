/**
 * Maps storefront URLs to the same Inertia component + props the Laravel
 * controllers return. Only used by `vite.preview.config.ts`.
 */
import { brands, bundles, categories, portfolio, products, settings } from './data';
import { buildHomeSections } from './home';

// Slugs in the sample data are plain strings or an {en, ar} map.
const slugOf = (value: unknown, id: number | string): string =>
    typeof value === 'string' && value
        ? value
        : value && typeof value === 'object'
          ? Object.values(value as Record<string, string>).find(Boolean) ?? String(id)
          : String(id);

export interface MockResponse {
    component: string;
    props: Record<string, unknown>;
}

const PER_PAGE = 8;

const text = (v: unknown): string =>
    typeof v === 'string' ? v : v && typeof v === 'object' ? Object.values(v as Record<string, string>).join(' ') : '';

function shop(params: URLSearchParams): MockResponse {
    const q = (params.get('q') ?? '').toLowerCase().trim();
    const brandSlugs = params.getAll('brand[]').concat(params.getAll('brand'));
    const catSlugs = params.getAll('category[]').concat(params.getAll('category'));
    const min = params.get('min_price') ? Number(params.get('min_price')) : null;
    const max = params.get('max_price') ? Number(params.get('max_price')) : null;
    const inStock = params.get('in_stock') === '1';
    const sort = (params.get('sort') ?? 'latest') as 'latest' | 'price_asc' | 'price_desc' | 'name';
    const pageNo = Math.max(1, Number(params.get('page') ?? 1));

    // a parent category also matches its children
    const expandedCats = new Set(catSlugs);
    categories.forEach((c) => {
        if (c.parent_id && catSlugs.includes(slugOf(categories.find((p) => p.id === c.parent_id)!.slug, 0))) {
            expandedCats.add(slugOf(c.slug, c.id));
        }
    });

    let list = products.filter((p) => {
        if (q && !`${text(p.name)} ${p.sku} ${text(p.brand?.name)}`.toLowerCase().includes(q)) return false;
        if (brandSlugs.length && !brandSlugs.includes(slugOf(p.brand!.slug, 0))) return false;
        if (expandedCats.size && !p.categories!.some((c) => expandedCats.has(slugOf(c.slug, 0)))) return false;
        if (min !== null && Number(p.price) < min) return false;
        if (max !== null && Number(p.price) > max) return false;
        if (inStock && p.stock_qty <= 0) return false;
        return true;
    });

    if (sort === 'price_asc') list = [...list].sort((a, b) => Number(a.price) - Number(b.price));
    if (sort === 'price_desc') list = [...list].sort((a, b) => Number(b.price) - Number(a.price));
    if (sort === 'name') list = [...list].sort((a, b) => text(a.name).localeCompare(text(b.name)));

    const total = list.length;
    const last = Math.max(1, Math.ceil(total / PER_PAGE));
    const current = Math.min(pageNo, last);

    return {
        component: 'Shop/Index',
        props: {
            products: {
                data: list.slice((current - 1) * PER_PAGE, current * PER_PAGE),
                current_page: current,
                last_page: last,
                total,
                from: total ? (current - 1) * PER_PAGE + 1 : null,
                to: Math.min(current * PER_PAGE, total),
            },
            brands,
            categories,
            filters: {
                q: params.get('q') ?? '',
                brand: brandSlugs,
                category: catSlugs,
                min_price: params.get('min_price'),
                max_price: params.get('max_price'),
                in_stock: inStock,
                sort,
            },
        },
    };
}

export function resolveMock(pathname: string, params: URLSearchParams): MockResponse | null {
    const path = pathname.replace(/\/+$/, '') || '/';
    const find = <T extends { id: number; slug: unknown }>(list: T[], slug: string) => list.find((x) => slugOf(x.slug as string, x.id) === slug);

    if (path === '/') {
        return { component: 'Shop/Home', props: { sections: buildHomeSections(params.get('variant'), params.get('m3d')) } };
    }
    if (path === '/shop') return shop(params);
    if (path === '/brands') return { component: 'Brands/Index', props: { brands } };
    if (path === '/offers') return { component: 'Offers/Index', props: { bundles } };
    if (path === '/portfolio') return { component: 'Portfolio/Index', props: { items: portfolio } };
    if (path === '/contact') return { component: 'Contact/Index', props: {} };

    let m: RegExpExecArray | null;
    if ((m = /^\/products\/(.+)$/.exec(path))) {
        const p = find(products, m[1]);
        if (!p) return null;
        const related = products
            .filter((x) => x.id !== p.id && x.categories!.some((c) => p.categories!.some((pc) => pc.id === c.id)))
            .slice(0, 4);
        const inBundles = bundles.filter((b) => b.products!.some((bp) => bp.id === p.id));
        return {
            component: 'Shop/Show',
            props: {
                product: { ...p, bundles: inBundles },
                related: related.length ? related : products.filter((x) => x.id !== p.id).slice(0, 4),
            },
        };
    }
    if ((m = /^\/brands\/(.+)$/.exec(path))) {
        const b = find(brands, m[1]);
        return b ? { component: 'Brands/Show', props: { brand: { ...b, products: products.filter((p) => p.brand!.id === b.id) } } } : null;
    }
    if ((m = /^\/categories\/(.+)$/.exec(path))) {
        const c = find(categories, m[1]);
        if (!c) return null;
        const ids = new Set([c.id, ...(c.children ?? []).map((x) => x.id)]);
        const parent = c.parent_id ? categories.find((x) => x.id === c.parent_id) : null;
        return {
            component: 'Categories/Show',
            props: {
                category: {
                    ...c,
                    parent: parent ? { id: parent.id, name: parent.name, slug: parent.slug } : null,
                    products: products.filter((p) => p.categories!.some((pc) => ids.has(pc.id))),
                },
            },
        };
    }
    if ((m = /^\/portfolio\/(.+)$/.exec(path))) {
        const item = find(portfolio, m[1]);
        return item ? { component: 'Portfolio/Show', props: { item } } : null;
    }
    if ((m = /^\/offers\/(.+)$/.exec(path))) {
        const bundle = find(bundles, m[1]);
        return bundle ? { component: 'Offers/Show', props: { bundle } } : null;
    }
    return null;
}

export const shared = (flash: string | null, query = new URLSearchParams()) => ({
    // ?bg3d=battery|mixed|none … and ?pos=left|center|right let the preview show every model
    settings: {
        ...settings,
        ...(query.get('bg3d') === 'none' ? { bg3d_enabled: false } : query.get('bg3d') ? { bg3d_model: query.get('bg3d')! } : {}),
        ...(query.get('pos') ? { bg3d_position: query.get('pos') as 'left' | 'center' | 'right' } : {}),
        ...(query.get('speed') ? { bg3d_speed: Number(query.get('speed')) } : {}),
    },
    locale: 'en' as const,
    flash: { success: flash },
    errors: {},
});
