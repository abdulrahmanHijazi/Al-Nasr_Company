/**
 * Props contract between the Laravel controllers and the Vue storefront.
 *
 * spatie/laravel-translatable serialises translatable attributes as the string
 * for the *current* locale, but a raw `{ en, ar }` map is also accepted so the
 * UI can switch language client-side. Use `tr()` from '@/lib/locale' to read.
 */
export type Translatable = string | Record<string, string | null | undefined> | null | undefined;

export interface BrandLite {
    id: number;
    name: Translatable;
    slug: Translatable;
    logo_url?: string | null;
}

export interface CategoryLite {
    id: number;
    name: Translatable;
    slug: Translatable;
}

export interface Product {
    id: number;
    sku: string;
    name: Translatable;
    slug: Translatable;
    short_description?: Translatable;
    description?: Translatable;
    price: string | number;
    compare_price?: string | number | null;
    stock_qty: number;
    is_featured?: boolean;
    brand?: BrandLite | null;
    categories?: CategoryLite[];
    cover_url?: string | null;
    gallery?: string[];
    /** present when loaded through a bundle */
    pivot?: { qty: number; price_override?: string | number | null };
}

export interface Category extends CategoryLite {
    description?: Translatable;
    image_url?: string | null;
    banner_url?: string | null;
    parent_id?: number | null;
    parent?: CategoryLite | null;
    children?: Category[];
    products_count?: number;
    products?: Product[];
}

export interface Brand extends BrandLite {
    description?: Translatable;
    website_url?: string | null;
    cover_url?: string | null;
    products_count?: number;
    products?: Product[];
}

export interface PortfolioItem {
    id: number;
    title: Translatable;
    slug: Translatable;
    description?: Translatable;
    client_name?: Translatable;
    project_url?: string | null;
    completed_at?: string | null;
    is_featured?: boolean;
    cover_url?: string | null;
    gallery?: string[];
}

export interface Bundle {
    id: number;
    name: Translatable;
    slug: Translatable;
    description?: Translatable;
    bundle_price: string | number;
    original_total?: number;
    savings?: number;
    starts_at?: string | null;
    ends_at?: string | null;
    cover_url?: string | null;
    products?: Product[];
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    from?: number | null;
    to?: number | null;
    prev_page_url?: string | null;
    next_page_url?: string | null;
}

export interface SiteSettings {
    site_name_en: string;
    site_name_ar: string;
    logo?: string | null;
    favicon?: string | null;
    og_image?: string | null;
    primary_color: string;
    secondary_color: string;
    header_color: string;
    footer_color: string;
    address_en?: string | null;
    address_ar?: string | null;
    latitude?: number | null;
    longitude?: number | null;
    google_maps_url?: string | null;
    contact_phone?: string | null;
    whatsapp_phone?: string | null;
    contact_email?: string | null;
    working_hours_en?: string | null;
    working_hours_ar?: string | null;
    facebook_url?: string | null;
    instagram_url?: string | null;
    x_url?: string | null;
    linkedin_url?: string | null;
    youtube_url?: string | null;
    tiktok_url?: string | null;
    snapchat_url?: string | null;
    telegram_url?: string | null;
    seo_title_en: string;
    seo_title_ar: string;
    seo_description_en: string;
    seo_description_ar: string;
    /** ISO 4217 code used to format prices (optional – defaults to USD). */
    currency?: string;

    // 3D background
    bg3d_enabled?: boolean;
    bg3d_model?: string;
    bg3d_custom_file?: string | null;
    bg3d_position?: 'left' | 'center' | 'right';
    bg3d_opacity?: number;
    bg3d_speed?: number;
    bg3d_interactive?: boolean;
    bg3d_pages?: 'all' | 'home';
}

export interface SharedProps {
    settings: SiteSettings;
    locale?: 'en' | 'ar';
    flash?: { success?: string | null };
    [key: string]: unknown;
}

export interface ShopFilters {
    q?: string;
    brand?: string[];
    category?: string[];
    min_price?: string | number | null;
    max_price?: string | number | null;
    in_stock?: boolean;
    sort?: 'latest' | 'price_asc' | 'price_desc' | 'name';
}

// -- Home page sections (managed in Filament → Home page) ------------------------

export type SectionType =
    | 'hero'
    | 'slider'
    | 'features'
    | 'categories'
    | 'products'
    | 'services'
    | 'process'
    | 'bundles'
    | 'banners'
    | 'brands'
    | 'portfolio'
    | 'counters'
    | 'testimonials'
    | 'cta';

/** Decorative 3D model chosen per section in Filament (null = none). */
export interface SectionModel3d {
    /** a built-in key from App\Enums\Background3dModel, or 'custom' (then `url` is set) */
    model: string;
    url: string | null;
    side: 'start' | 'end';
}

export interface HomeSection<C = Record<string, any>, I = any> {
    id: number | null;
    model_3d?: SectionModel3d | null;
    type: SectionType;
    /** visual variant, e.g. "grid" | "carousel" — see HomeSectionType::designs() in PHP */
    design: string;
    title: Translatable;
    subtitle: Translatable;
    background_color: string | null;
    content: C;
    items: I[];
    stats?: { products?: number; brands?: number; projects?: number };
}
