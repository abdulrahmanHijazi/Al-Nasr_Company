<div align="center">

<img src="https://capsule-render.vercel.app/api?type=waving&color=0:16a34a,50:22c55e,100:84cc16&height=180&section=header&text=Al-Nasr+Company&fontSize=52&fontColor=ffffff&fontAlignY=38&desc=Online+Store+%7C+Laravel+13+%2B+Filament+5+%2B+Vue+3&descSize=20&descAlignY=62&animation=fadeIn"/>

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php&logoColor=white)](https://php.net)
[![Vue](https://img.shields.io/badge/Vue-3-4FC08D?style=flat&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=flat&logo=typescript&logoColor=white)](https://www.typescriptlang.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=flat&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Filament](https://img.shields.io/badge/Filament-5-FF3D2E?style=flat&logo=laravel&logoColor=white)](https://filamentphp.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat)](LICENSE)

</div>

---

## 📖 Overview

**Al-Nasr Company Online Store** is a full-featured bilingual (AR/EN) e-commerce platform built with a modern Inertia + Vue storefront and a powerful Filament 5 admin panel — products, brands, nested categories, special-offer bundles, portfolio, and fully customizable site settings (colors, logo, contact, SEO).

## ✨ Features

| Area | Details |
|---|---|
| 🏷️ **Brands** | Translatable `name{en,ar}`, description, website, logo + cover (media-library), active flag, sorting |
| 🗂️ **Categories** | Nested `parent_id` tree, translatable name/description, image + banner, M2M with products |
| 📦 **Products** | SKU, brand FK, M2M categories, price / compare / cost, stock, featured flag, gallery + cover collections, translatable name/slug/short/description |
| 🖼️ **Portfolio** | Company portfolio items: title, description, client, project URL, completed date, featured, cover + gallery |
| 🎁 **Special Offers (Bundles)** | Bundle = product bundle with `bundle_price`, start/end dates, per-bundle qty & price override, computed `original_total` + `savings` |
| ⚙️ **Site Settings** | Color scheme (`primary/secondary/header/footer` injected as CSS vars), logo + favicon, **lat/lng + Google Maps** link, contact page (phone, WhatsApp, email, hours, socials), SEO |
| 📬 **Contact** | `/contact` Inertia form → `contact_messages` table → Filament inbox |
| 🔍 **Translatable Slugs** | JSON-slug route binding works in **any locale**, active records only |

## 🛠️ Tech Stack

- **Backend:** Laravel 13 (PHP 8.3+) · Inertia Laravel · Spatie Translatable / Media Library / Permission / Settings / Phone
- **Frontend:** Vue 3 · TypeScript (`vue-tsc`) · Tailwind CSS v4 · Vite · Inertia pages (Home / Shop / Product / Offers / Contact)
- **Admin:** **Filament 5** (`/admin`) with plugins — Spatie Translatable, Spatie Media Library, Phone Input, **Shield** (roles & permissions), Spatie Settings
- **Testing:** Pest

### Filament navigation groups

`Catalog` (Brand / Category / Product) · `Content` (Portfolio) · `Marketing` (Bundle offers) · `Settings` (site settings) · `Access` (Shield)

## 📁 Structure

```text
app/
  Models/{Brand,Category,Product,PortfolioItem,Bundle,ContactMessage}.php
  Models/Concerns/ResolvesTranslatedSlug.php   # JSON-slug route binding
  Http/Controllers/{Storefront,Brand,Category,Portfolio,Offer,Contact}Controller.php
  Http/Middleware/{HandleInertiaRequests,SetLocale}.php
  Filament/Resources/*Resource.php             # admin CRUD
  Filament/Pages/ManageSiteSettings.php        # colors, logo, lat/lng, phone
  Settings/SiteSettings.php
database/{migrations,settings,factories,seeders}
lang/{en,ar}.json
resources/{css,js,views}
tests/Feature/
```

## 🚀 Installation

```bash
# 1) PHP dependencies
composer install

# 2) Frontend dependencies
npm install

# 3) Environment
cp .env.example .env
php artisan key:generate

# 4) Database
php artisan migrate --seed

# 5) Storage link (media)
php artisan storage:link

# 6) Run
npm run dev        # terminal 1
php artisan serve  # terminal 2
```

The admin panel is available at **`/admin`** after seeding (Filament Shield manages roles).

## 📸 Storefront Routes

| Route | Page |
|---|---|
| `/` | Home |
| `/shop` | Shop (all products) |
| `/products/{slug}` | Product detail |
| `/brands`, `/brands/{slug}` | Brands listing / detail |
| `/categories/{slug}` | Category listing |
| `/portfolio` | Company portfolio |
| `/offers` | Special offers & bundles |
| `/contact` | Contact form |

---

<div align="center">

**Built with ❤️ by [Abdulrahman Hijazi](https://github.com/abdulrahmanHijazi)**

</div>
