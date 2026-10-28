# Al-Nasr Company — Online Store (Laravel 13 + Inertia + Vue + TS + Tailwind + Filament 5)

> Standard Laravel layout (`app/Models`, `app/Http/Controllers`, `app/Filament`) — **no nwidart modules**.

## 1. Features

| Area | Details |
|---|---|
| Brands | `name{en,ar}`, description, website, logo + cover (media-library), active, sort |
| Product Categories | nested `parent_id`, translatable name/description, image + banner, M2M products |
| Products | SKU, brand FK, M2M categories, price/compare/cost, stock, featured, `gallery` + `cover` collections, translatable name/slug/short/description |
| Portfolio | Company portfolio items: title/description/client/project_url/completed_at/featured, cover + gallery |
| Special Offers (Bundles) | Bundle = product bundle: `bundle_price`, starts/ends_at, `bundle_product(qty, price_override)`, computed `original_total`, `savings` |
| Site Settings | Color scheme (`primary/secondary/header/footer`), company logo + favicon, location **lat + lng** + address + Google Maps URL, contact page (phone, whatsapp, email, hours, socials), SEO |
| Contact Pages | `/contact` Inertia form → `contact_messages` table + Filament inbox |

## 2. Stack (as requested)

- **Laravel 13** (`laravel/framework ^13.0`, PHP `^8.3`)
- **Inertia Laravel** (`inertiajs/inertia-laravel ^2.0`) + **Vue 3** + **TS** (`vue-tsc`, `tsconfig.json` with `@/*` alias)
- **Tailwind CSS v4** (`@tailwindcss/vite`, CSS vars `--store-primary/secondary/header/footer` injected from settings)
- **Filament 5** (`filament/filament ^5.0`) + `AdminPanelProvider` (`/admin`) — Filament 3.x does not support Laravel 13; see DESIGN.md for the admin + home-section docs
- **Translatable**: `spatie/laravel-translatable` + Filament plugin **`lara-zeus/spatie-translatable`** (`LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable` + `SpatieLaravelTranslatablePlugin`)
- **Media**: `spatie/laravel-medialibrary` + **`filament/spatie-laravel-media-library-plugin`** (`SpatieMediaLibraryFileUpload`, `SpatieMediaLibraryImageColumn`)
- **Phone**: `propaganistas/laravel-phone` + **`ysfkaya/filament-phone-input`** (`PhoneInput::make('contact_phone')`)
- **AuthZ**: `spatie/laravel-permission` + **`bezhansalleh/filament-shield`** (`FilamentShieldPlugin`, navigation group `Access`)
- **Settings**: `spatie/laravel-settings` + **`filament/spatie-laravel-settings-plugin`** (`SettingsPage`, `ManageSiteSettings`)

Requested plugin URLs mapped to composer packages:

- https://filamentphp.com/plugins/filament-spatie-media-library → `filament/spatie-laravel-media-library-plugin`
- https://filamentphp.com/plugins/lara-zeus-spatie-translatable → `lara-zeus/spatie-translatable`
- https://filamentphp.com/plugins/ysfkaya-phone-input → `ysfkaya/filament-phone-input`
- https://filamentphp.com/plugins/bezhansalleh-shield#custom-navigation-group → `bezhansalleh/filament-shield`
- https://filamentphp.com/plugins/filament-spatie-settings → `filament/spatie-laravel-settings-plugin`

## 3. Structure (elearning-dashboard mode)

```text
app/
  Models/{Brand,Category,Product,PortfolioItem,Bundle,ContactMessage}.php
  Models/Concerns/ResolvesTranslatedSlug.php      # JSON-slug route binding (any locale, active only)
  Http/Controllers/{Storefront,Brand,Category,Portfolio,Offer,Contact}Controller.php
  Http/Middleware/{HandleInertiaRequests,SetLocale}.php
  Filament/Resources/*Resource.php  (+ Pages)     # admin — needs the Filament 4 port, see DESIGN.md
  Filament/Pages/ManageSiteSettings.php
  Settings/SiteSettings.php
database/{migrations,settings,factories,seeders}
lang/{en,ar}.json
resources/{css,js,views}
tests/Feature/Models/*  tests/Feature/*Test.php   # Pest
```

Filament navigation groups: `Catalog` (Brand/Category/Product), `Content` (Portfolio), `Marketing` (Bundle offers), `Settings` (site settings), `Access` (Shield).

## 4. Key files already scaffolded

- `app/Settings/SiteSettings.php` — group `site`, colors, logo, lat/lng, contact.
- `app/Filament/Pages/ManageSiteSettings.php` — ColorPicker ×4, logo upload, lat/lng numeric, PhoneInput ×2.
- `app/Models/*.php` — HasTranslations + InteractsWithMedia + collections.
- `app/Filament/Resources/*.php` — media upload + translatable + toggles.
- `app/Models/Bundle.php` — `original_total` / `savings` accessors.
- `routes/web.php` — `/`, `/shop`, `/products/{slug}`, `/brands*`, `/categories/{slug}`, `/portfolio*`, `/offers*`, `/contact` (GET+POST).
- `resources/js/*` — TS Inertia pages (Home/Shop/Show/Contact/Offers).

## 5. Install (fresh machine)

```powershell
cd "C:\Users\Lenovo\PhpstormProjects\datacenter\Al-Nasr_Company"

# 1) PHP deps (Laravel 13 needs full skeleton files: artisan, bootstrap/, config/, public/, storage/)
# If you started from this scaffold only, first create skeleton then merge:
# composer create-project laravel/laravel tmp-skel "^13.0"
# Copy-Item tmp-skel\* . -Recurse -Force  # then re-apply scaffold files

composer install

composer require filament/filament:"^5.0" -W
composer require filament/spatie-laravel-media-library-plugin:"^3.0" `
  filament/spatie-laravel-settings-plugin:"^3.2" `
  lara-zeus/spatie-translatable:"^1.3" `
  ysfkaya/filament-phone-input:"^3.0" `
  bezhansalleh/filament-shield:"^3.1" -W
composer require nwidart/laravel-modules:"^10.0" inertiajs/inertia-laravel:"^2.0" -W
composer require spatie/laravel-medialibrary spatie/laravel-settings spatie/laravel-translatable spatie/laravel-permission -W

php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="settings-migrations"
php artisan filament:install --panels
php artisan shield:install --all
php artisan migrate
php artisan settings:sync # after defining settings migration for `site` group

npm install; npm run dev

php artisan shield:generate --all
php artisan db:seed
php artisan serve
# Admin: http://127.0.0.1:8000/admin  | Storefront: http://127.0.0.1:8000/
```

Vite entry: `resources/css/app.css` + `resources/js/app.ts`. Blade root must set CSS vars from settings:

```blade
<style>
:root {
  --store-primary: {{ app(\App\Settings\SiteSettings::class)->primary_color }};
  --store-secondary: {{ ...->secondary_color }};
  --store-header: {{ ...->header_color }};
  --store-footer: {{ ...->footer_color }};
}
</style>
```

Leaflet/Google embed on contact page uses `latitude` + `longitude`.

## 6. DB tables

`brands, categories, products, category_product, portfolio_items, bundles, bundle_product, contact_messages` (+ `media`, `settings`, `permissions/roles` from packages).

## 7. Next steps / TODO in code

- [ ] Generate Filament Pages classes (`List/Create/Edit`) per resource + Shield permissions + navigation group config.
- [ ] Settings migration: `php artisan make:settings SiteSettings` content from `app/Settings/SiteSettings.php`.
- [ ] Slugs auto-fill (ar/en), SKU generator, stock decrement on order (orders module not yet in scope).
- [ ] Storefront filters (brand/category/price), search, pagination, i18n (`laravel-vue-i18n`), RTL for Arabic.
- [ ] Seeders + factories (brands/categories/products/bundles/portfolio).
- [ ] Pest tests for bundle savings, contact validation, settings group.
- [ ] `php artisan pint`, `npm run typecheck`, `npm run build`.

## 8. Notes vs elearning-dashboard

- elearning uses **Nova 4** + `astrotomic/laravel-translatable` + custom `nova-components/`; this store uses **Filament 3** + `spatie/laravel-translatable` + official Filament plugins (simpler, no Nova license).
- Module layout kept 1:1 (`App/Models`, `App/Filament/Resources`, `App/Http/Controllers`, `Database/migrations`, `routes`) so teams coming from elearning feel at home; Filament replaces `App/Nova`.
