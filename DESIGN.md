# Al-Nasr storefront — design notes

## Preview the design (no PHP needed)

```bash
npm install
npm run preview        # http://localhost:5199 — real Inertia pages on sample data
npm run typecheck
npm run build
```

`vite.preview.config.ts` speaks the Inertia protocol against `resources/js/mocks/*`, so links, filters,
the contact form and page transitions behave as in production. Nothing in `mocks/` ships to production.

## Principles

- **Admin-driven brand.** `primary / secondary / header / footer` come from *Site settings*. Every other
  shade is derived in `resources/css/app.css` with `color-mix()`, and text-on-colour is picked from
  luminance (`resources/js/lib/theme.ts`), so any colour the admin picks stays readable.
- **Arabic first-class.** EN ↔ AR switch in the header/footer, `dir` flips, logical properties
  (`ms-`, `ps-`, `start-`, `end-`) everywhere, directional icons flip, numbers/prices stay LTR.
  Strings live in `lang/ar.json` (English text is the key; `lang/en.json` is intentionally empty).
- **Catalogue + enquiry, not checkout.** Orders are out of scope, so every product/bundle leads to
  *Request a quote* (pre-fills `/contact?subject=…`) or *WhatsApp*.
- **Designed empty states.** `SmartImage` renders brand-coloured artwork until real photos are uploaded,
  so grids never look broken.
- **Type:** IBM Plex Sans + IBM Plex Sans Arabic (one family, matched metrics).

## Pages

| Route | Component |
|---|---|
| `/` | `Shop/Home` — hero, value strip, categories, featured, bundles, brands, portfolio, CTA |
| `/shop` | `Shop/Index` — search, category/brand/price/stock filters, sort, chips, pagination |
| `/products/{slug}` | `Shop/Show` — gallery, price/discount, stock, quote + WhatsApp, tabs, bundles, related |
| `/brands`, `/brands/{slug}` | `Brands/Index`, `Brands/Show` |
| `/categories/{slug}` | `Categories/Show` |
| `/portfolio`, `/portfolio/{slug}` | `Portfolio/Index`, `Portfolio/Show` |
| `/offers`, `/offers/{slug}` | `Offers/Index`, `Offers/Show` |
| `/contact` | `Contact/Index` — form, contact cards, OpenStreetMap from lat/lng |

## Backend contract

Prop shapes are typed in `resources/js/types/index.ts`. To wire the real app:

1. Register `App\Http\Middleware\HandleInertiaRequests` (shares `settings`, `locale`, `flash`) and
   `App\Http\Middleware\SetLocale` in the `web` group.
2. Use `resources/views/app.blade.php` as the Inertia root view.
3. Models expose `cover_url` / `gallery` / `logo_url` / `image_url` / `banner_url` via media-library accessors.
4. `StorefrontController` implements the `/shop` filters and the extra home/product props.
5. `App\Models\Concerns\ResolvesTranslatedSlug` makes `/{model:slug}` binding work with JSON slugs.

## Tests (Pest)

```bash
composer install
php artisan key:generate
vendor/bin/pest            # 61 tests: every model, SiteSettings, storefront routes, seeder
php artisan migrate --seed # optional starter catalogue (solar, batteries, inverters, cameras…)
```

`tests/Feature/Models/` covers Brand, Category, Product, PortfolioItem, Bundle and ContactMessage
(translations, casts, relations, pivots, accessors, media collections/URLs). `StorefrontTest` exercises the
controllers, filters and contact form; `SiteSettingsTest` covers the settings group.

## Admin dashboard (Filament 5)

Built to the [Filament 5.x docs](https://filamentphp.com/docs/5.x/getting-started): `composer require filament/filament:"^5.0"`,
`php artisan filament:install --panels`, panel at **`/admin`** (`app/Providers/Filament/AdminPanelProvider.php`).
Filament 3 does not support Laravel 13, so the whole stack is on the v5 line (Livewire 4).

```bash
php artisan migrate --seed          # tables + starter catalogue + default home layout
php artisan admin:create            # prompts for name / email / password
# or non-interactive:
php artisan admin:create --name="Admin" --email=admin@alnasr.example --password='S3cure-pass!'
```

`admin:create` creates (or promotes) the user and gives them Shield's `super_admin` role. Anyone with *a* role
can open the panel (`User::canAccessPanel()`); the stock `php artisan make:filament-user` makes a user with no
role, who would get a 403 here. Roles and permissions are managed under **Access → Roles** (Filament Shield).

| Menu group | Pages |
|---|---|
| Home page | **Home page sections** — add / re-order / design the storefront home page |
| Catalog | Brands, Categories, Products |
| Marketing | Special offers (bundles) |
| Content | Portfolio |
| Inbox | Contact messages (unread badge, open = mark read) |
| Settings | Site settings (branding & colours, contact, location, social, SEO, currency) |
| Dashboard | Store stats, products-by-category chart, latest enquiries |

Names/descriptions are bilingual through `lara-zeus/spatie-translatable` (locale switcher on every resource page).

## Admin language (Arabic / English)

* **Language switch:** [`bezhansalleh/filament-language-switch`](https://github.com/bezhanSalleh/filament-language-switch)
  (v5) adds an `AR / EN` switcher to the top bar and login page. Configured in `AppServiceProvider::boot()`;
  the admin opens in **Arabic (RTL)** by default and remembers the admin's choice in the session.
* **Theme:** the plugin needs a Filament theme that scans its views — `resources/css/filament/admin/theme.css`
  (registered with `->viteTheme()`). After changing it run `npm run build`.
* **Translations:** Filament, Shield and the translatable plugin ship Arabic. Everything custom is in
  `lang/admin/ar.json` (English text is the key); Laravel's own validation / auth / password messages are in
  `lang/ar/`. Storefront strings stay in `lang/ar.json`.
* **Add a screen or label?** Use plain English strings (`->label('Sort order')`, `__('…')`). Field, column,
  filter and tab labels are translated automatically; `ArabicTranslationTest` renders every admin page in Arabic and
  **fails with the list of any string that still has no translation** — add it to `lang/admin/ar.json`.

## 3D background

A slowly turning 3D model sits behind the storefront (fixed, click-through, WebGL via Three.js). Choose it in
**Admin → Settings → Site settings → 3D background**:

| Setting | Options |
|---|---|
| Model | Solar panel · Lithium battery · Surveillance camera · Power inverter · Industrial control panel · Wind turbine · **Mixed** (all together) · **My own model** (.glb upload, ≤ 15 MB) |
| Position | Right · Center · Left |
| Strength | Subtle · Balanced · Strong · Full |
| Rotation speed | Still · Slow · Normal · Fast |
| Show on | Every page · Home page only |
| React to mouse and scroll | on / off |

* Built-in models are generated in code (`resources/js/lib/bg3d/models.ts`) — nothing to download or license, and they use
  your brand colours. Add one: write a builder, add its key to `MODEL_KEYS` and to `App\Enums\Background3dModel`, then add a
  picture in `App\Support\ModelPreview` and run `php artisan design:previews`.
* **Performance:** Three.js (~165 KB gzipped) is a separate chunk loaded only when the background is shown. It pauses when the
  tab is hidden, uses a capped pixel ratio, stays still for visitors with *reduce motion*, and silently does nothing without
  WebGL. Switch it off in settings for the lightest storefront.
* **Looks:** while it runs, the dark hero becomes see-through over a navy backdrop and white bands turn frosted glass
  (`html.has-bg3d` in `resources/css/app.css`). A model on the right replaces the hero's floating product cards.
* **After pulling:** run `php artisan migrate` (the settings are created by `database/settings/…add_3d_background_settings.php`).
* Preview without a backend: `npm run preview` → `/?bg3d=battery|cctv-camera|inverter|control-panel|wind-turbine|mixed|none`,
  plus `&pos=left|center|right` and `&speed=0..3`.

## 3D model per home section

Besides the page-wide background, **each home-page section can carry its own rotating 3D model**. Pick it in
**Admin → Home page → Home page sections → (create / edit) → 3D model** — a picture card for *No 3D model* and every model
above (including *Mixed* and *My own model*), plus the side it sits on (start / end). It is stored on the row
(`home_page_sections.model_3d`, `model_3d_file`, `model_3d_side`), shown in the list (badge + filter) and on the detail page.

* **Where it appears:** desktop (`lg` and up). In content sections the model fills the empty strip beside the heading
  (`Components/home/Section3D.vue`, inside `SectionShell`); in the **hero** (Split and Centered designs) it takes the place of the
  floating product cards; in the **call to action** (Primary and Split) it sits between the text and the buttons.
  Designs that are a bare strip — Features *Row*, Counters *Band*, hero *Slider*, CTA *Minimal* — have no room for a model and ignore it.
  Sections without a title (e.g. promo banners) have no heading strip, so give them none.
* **Performance:** a viewer is created only while its section is on screen (IntersectionObserver) and disposed when it
  leaves; at most 3 WebGL contexts live at once, the page background keeps its own. Phones never load Three.js for it.
  Visitors with *reduce motion* get a still frame.
* **Sample data:** `HomePageSection::defaults()` now has all 13 section types with bilingual content and a fitting model.
  `php artisan db:seed` and `php artisan demo:seed` call `HomePageSection::seedDefaults()` — it adds any section type that is
  missing and gives untouched sections their sample model, but never overwrites anything an admin saved.
* **After pulling:** `php artisan migrate`, then `php artisan db:seed` (or `demo:seed`), then `npm run build`.
* Preview without a backend: `npm run preview` → `/` shows the sample models; `/?m3d=off` hides them, `/?m3d=battery` forces one.

## Equipment 3D library (inverters, batteries, charge controllers, breakers)

23 ready-made `.glb` models live in **`database/models3d/`** (+ `manifest.json` with bilingual names, brand and type), and **one command**
loads them into the database and storage:

```bash
php artisan migrate
php artisan models3d:import          # copies the files to storage/app/public/models3d, makes the pictures, fills the table
php artisan storage:link             # once
```

`php artisan demo:seed` runs the import too (skip it with `--no-models`). It is safe to repeat: models are matched by slug, nothing is
duplicated, and what an admin changed (e.g. switched a model off) is kept.

| Type | Models |
|---|---|
| Inverters | Voltronic Axpert · Felicity hybrid · Solis hybrid · Growatt off-grid · Deye hybrid |
| Lithium batteries | Tobac rack 48 V · Tobac 4-module cabinet · Felicity wall-mount · Pylontech rack · Dyness wall-mount |
| Solar charge controllers | Voltronic MPPT · Felicity MPPT · EPever Tracer · Victron SmartSolar · PWM 30 A |
| Breakers | MCB 1P / 2P / 3P / 4P · DC MCB 2P · MCB with shunt-trip · MCCB 3P · MCCB with shunt-trip |

* **Use them:** they appear (grouped by type, with pictures) in the 3D picker of **Home page sections** and in **Site settings → 3D
  background**. A section or setting stores `library:{slug}`; the storefront receives it as a normal `.glb`. Manage them under
  **Home page → 3D models** (rename, switch off, upload your own).
* **Honest note:** these are stylised models built in code (`App\Support\Glb\Library`, `GlbBuilder`) from each product line's typical shape and
  colours — not CAD copies of a manufacturer's design, and the brand names are only labels. For a manufacturer's real model, put its
  `.glb` in `database/models3d/` (same file name to replace one, or a new name to add one — an unlisted file is imported as
  "Other equipment", edit its name in the admin) and run `php artisan models3d:import` again.
* **Regenerate the built-in files:** `php artisan models3d:build` (adds missing ones; `--force` rewrites all, including ones you replaced).
  Add a model by writing one more entry in `Library::entries()`.
* Preview a file without a backend: `npm run preview` → `/?m3d=glb:inverter-solis-hybrid`.

## Demo catalogue

`php artisan demo:seed` now stocks the families above: **11 brands** (Voltronic, Felicity, Solis, Growatt, Deye, Tobac, Pylontech, Dyness,
EPever, Victron, and Al-Nasr Electric for breakers), **15 categories** (inverters → hybrid / off-grid / grid-tie, lithium batteries → rack / wall,
charge controllers → MPPT / PWM, breakers → MCB / DC / MCCB / shunt-trip), **35 products**, 4 offers (kits), 5 projects. `db:seed` loads a
smaller starter set of the same.

## Admin forms, detail pages and font

* **Font:** [Cairo](https://fonts.google.com/specimen/Cairo) (Arabic + Latin, variable weights), self-hosted from
  `@fontsource-variable/cairo` through the Filament theme — no CDN. Set with
  `->font('Cairo Variable', provider: LocalFontProvider::class)`; the `@import` lives in
  `resources/css/filament/admin/theme.css` (run `npm run build` after changing it).
* **Forms:** every resource uses a two-column layout — main content on the right (RTL) and a side column for
  *Publishing*, *Inventory / Schedule* and *Images* — with section icons and descriptions
  (`app/Filament/Resources/*/Schemas/*Form.php`).
* **Detail pages (infolists):** each resource has a read-only *View* page (`Schemas/*Infolist.php`): image + title
  header, price / stock / margin cards, included products and offers, gallery. **Messages** open as a conversation
  card with *Reply by email*, *WhatsApp*, *Call*, *Mark as read / unread* and *Delete* actions.
* **Content language:** product, brand and page text is edited in Arabic first; switch to English with the language
  picker at the top of each form.

## Demo data command

```bash
php artisan demo:seed                    # brands, categories, 24 products, 4 offers, 5 projects, 12 messages, home layout
php artisan demo:seed --extra=60         # + 60 random filler products (test pagination / filters)
php artisan demo:seed --messages=25      # bigger inbox
php artisan demo:seed --no-images        # skip generated placeholder images
php artisan demo:seed --fresh --force    # wipe catalogue / content first, then insert
php artisan storage:link                 # once, so the generated images are served
```

Realistic bilingual (AR/EN) solar / lithium / inverter / industrial / CCTV / alarm content with generated images
(GD, no network). Safe to re-run — records are matched by SKU or slug and updated, never duplicated. Users, roles and
site-settings you already filled in are left alone (contact details are only set while still empty). It refuses to
run in production without `--force`, and `--fresh` asks for confirmation (or needs `--force`).
`php artisan db:seed` still loads the smaller starter catalogue.

## Home page sections (multi-design)

Same idea as the elearning dashboard's *Home Page Sections*: each row is a block with a **type** (what it shows), a
**design** (how it looks), a **source** (latest / featured / hand-picked items) and an order. Drag rows in the list to
re-order; toggle *Active* to hide.

| Type | Designs |
|---|---|
| Hero | Split with featured products · Full-width slider · Centered |
| Features strip | Row · Cards |
| Categories | Image tiles · Pills |
| Products | Grid · Carousel |
| Installation services | Cards · Numbered list |
| Process / steps | Grid · Timeline |
| Special offers | Dark band · Light cards |
| Promo banners | Wide · Split · Triple |
| Brands | Logo grid · Scrolling strip |
| Portfolio | Grid · Featured first |
| Counters | Colour band · Cards |
| Testimonials | Cards · Large quote |
| Call to action | Primary colour · Dark split · Minimal |

* **Design picker:** when choosing a section's design, every option shows a wireframe preview
  (`public/images/designs/{type}-{design}.svg`); the list and detail pages show the chosen one. The images are
  generated by `App\Support\DesignPreview` — after adding a design to `HomeSectionType::designs()` add its drawing there
  and run `php artisan design:previews` (a test fails if any design has no preview).
* **When is a section shown?** Only while *Visible on the storefront* is on. Until the first section is created the
  built-in layout is used; after that, turning every section off gives an empty home page. Blocks set to
  *Featured first, then latest* always fill their slots with the newest visible items.
* Until an admin saves a section the storefront uses `HomePageSection::defaults()`; `db:seed` persists it.
* Content blocks (slides, services, counters…) are stored as bilingual JSON `{en, ar}` on `home_page_sections.content`.
* Add a design: list it in `App\Enums\HomeSectionType::designs()` and handle the key in
  `resources/js/Components/home/<Type>Section.vue`.
* Review every design without a backend: `npm run preview` → `/` (built-in) and `/?variant=alt` (second designs).

## Equipment 3D models from real product photos

The library models are no longer plain boxes: each hand-built model has a printed front (LCD with readings, buttons,
LED labels, rating plate, ports, terminals) painted by `App\Support\Glb\Panel`, and the models listed with a photo are
the manufacturer's own front picture wrapped on a body with the real proportions (`Library::photoOr`).

- Save a photo (straight front view on a plain white background, e.g. from the manufacturer's site):
  `php artisan models3d:photo <slug> <url-or-file>` → `database/models3d/photos/<slug>.png` (background removed).
- Rebuild the `.glb` files: `php artisan models3d:build --force`, then `php artisan models3d:import`.
- Photos saved so far (database/models3d/photos): Voltronic Axpert / InfiniSolar off-grid inverters (voltronicpower.com, 13 models), Felicity inverters and batteries (felicitysolar.nl, 7 + 6 models) and Chint MCCBs NXMG / NM8N / NXMD (chint-ajlanbros.com, 7 models). Models of other brands (Solis, Growatt, Deye, Tobac, Pylontech, Dyness, EPever, Victron, MCB) are still hand-built until their photos are added.
  Product photos belong to their manufacturers — keep them only where you have their permission (distributor/reseller).

## Home slider

`HomeSectionType::Slider` is a stand-alone section (add it anywhere): slides with a background picture, a product
picture, badge, title, subtitle and button; arrows, dots, swipe, auto-play (pauses on hover/focus, off for
reduced-motion). Designs: `full` (full-bleed picture), `product` (dark slide with the product beside the text),
`cards` (rounded card inside the page container). Component: `resources/js/Components/home/SliderSection.vue`.
