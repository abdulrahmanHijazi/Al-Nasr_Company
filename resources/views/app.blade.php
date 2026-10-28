@php
    /** @var \App\Settings\SiteSettings $site */
    $site = app(\App\Settings\SiteSettings::class);
    $locale = app()->getLocale();
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
    $title = $locale === 'ar' ? $site->seo_title_ar : $site->seo_title_en;
    $description = $locale === 'ar' ? $site->seo_description_ar : $site->seo_description_en;
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $site->header_color }}">
    <meta name="description" content="{{ $description }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    @if ($site->og_image)<meta property="og:image" content="{{ $site->og_image }}">@endif
    @if ($site->favicon)<link rel="icon" href="{{ $site->favicon }}">@endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Brand colours chosen in Filament → Site settings. --}}
    <style>
        :root {
            --store-primary: {{ $site->primary_color }};
            --store-secondary: {{ $site->secondary_color }};
            --store-header: {{ $site->header_color }};
            --store-footer: {{ $site->footer_color }};
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
