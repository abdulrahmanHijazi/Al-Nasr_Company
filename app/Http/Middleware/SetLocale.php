<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The storefront language switch (resources/js/lib/locale.ts) stores the visitor's
 * choice in a plain `locale` cookie; honour it so server-rendered <html lang/dir>,
 * translatable model attributes and validation messages match the UI.
 */
class SetLocale
{
    private const SUPPORTED = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('locale');

        if (is_string($locale) && in_array($locale, self::SUPPORTED, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
