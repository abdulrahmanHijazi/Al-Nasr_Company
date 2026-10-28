<?php

use App\Settings\SiteSettings;

it('is seeded with sensible defaults by the settings migration', function () {
    $settings = app(SiteSettings::class);

    expect($settings->site_name_en)->toBe('Al-Nasr Company')
        ->and($settings->site_name_ar)->toBe('شركة النصر')
        ->and($settings->currency)->toBe('USD')
        ->and($settings->primary_color)->toStartWith('#')
        ->and($settings->logo)->toBeNull()
        ->and($settings->latitude)->toBeNull();
});

it('uses the "site" group', function () {
    expect(SiteSettings::group())->toBe('site');
});

it('persists the colour scheme, location and contact details', function () {
    $settings = app(SiteSettings::class);
    $settings->primary_color = '#0f766e';
    $settings->secondary_color = '#f59e0b';
    $settings->header_color = '#042f2e';
    $settings->footer_color = '#022c22';
    $settings->latitude = 24.7136;
    $settings->longitude = 46.6753;
    $settings->contact_phone = '+966 11 000 0000';
    $settings->whatsapp_phone = '+966 50 000 0000';
    $settings->contact_email = 'sales@alnasr.example';
    $settings->save();

    $fresh = app(SiteSettings::class);

    expect($fresh->primary_color)->toBe('#0f766e')
        ->and($fresh->header_color)->toBe('#042f2e')
        ->and($fresh->latitude)->toBe(24.7136)
        ->and($fresh->longitude)->toBe(46.6753)
        ->and($fresh->whatsapp_phone)->toBe('+966 50 000 0000');
});

it('serialises everything the storefront layout needs', function () {
    expect(app(SiteSettings::class)->toArray())->toHaveKeys([
        'site_name_en', 'site_name_ar', 'logo',
        'primary_color', 'secondary_color', 'header_color', 'footer_color', 'currency',
        'latitude', 'longitude', 'contact_phone', 'whatsapp_phone', 'contact_email',
        'seo_title_en', 'seo_title_ar', 'seo_description_en', 'seo_description_ar',
    ]);
});
