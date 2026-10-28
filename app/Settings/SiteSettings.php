<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Al-Nasr Company site settings.
 * Managed via Filament spatie-settings plugin.
 * Covers: color scheme, logo, location (lat/lng), contact page.
 */
class SiteSettings extends Settings
{
    public string $site_name_en;
    public string $site_name_ar;

    public ?string $logo;
    public ?string $favicon;
    public ?string $og_image;

    // Color scheme
    public string $primary_color;
    public string $secondary_color;
    public string $header_color;
    public string $footer_color;

    /** ISO 4217 code used to format prices on the storefront. */
    public string $currency;

    // Location using lng + lat
    public ?string $address_en;
    public ?string $address_ar;
    public ?float $latitude;
    public ?float $longitude;
    public ?string $google_maps_url;

    // Contact page
    public ?string $contact_phone;
    public ?string $whatsapp_phone;
    public ?string $contact_email;
    public ?string $working_hours_en;
    public ?string $working_hours_ar;

    public ?string $facebook_url;
    public ?string $instagram_url;
    public ?string $x_url;
    public ?string $linkedin_url;
    public ?string $youtube_url;
    public ?string $tiktok_url;
    public ?string $snapchat_url;
    public ?string $telegram_url;

    // 3D background (Site settings → 3D background)
    public bool $bg3d_enabled;
    public string $bg3d_model;          // App\Enums\Background3dModel value
    public ?string $bg3d_custom_file;   // uploaded .glb / .gltf on the public disk
    public string $bg3d_position;       // left | center | right
    public int $bg3d_opacity;           // 20–100 (%)
    public int $bg3d_speed;             // 0 (still) – 3 (fast)
    public bool $bg3d_interactive;      // follows the mouse / scroll
    public string $bg3d_pages;          // all | home

    public string $seo_title_en;
    public string $seo_title_ar;
    public string $seo_description_en;
    public string $seo_description_ar;

    public static function group(): string
    {
        return 'site';
    }
}
