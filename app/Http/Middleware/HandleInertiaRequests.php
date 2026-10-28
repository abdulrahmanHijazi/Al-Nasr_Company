<?php

namespace App\Http\Middleware;

use App\Enums\Background3dModel;
use App\Models\ThreeDModel;
use App\Settings\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props available to every storefront page (see resources/js/types SharedProps).
     * Settings are shared once here so the layout can render header, footer,
     * contact details and colours on every route.
     */
    public function share(Request $request): array
    {
        $settings = app(SiteSettings::class)->toArray();

        // Uploaded branding files are stored as disk paths; the browser needs URLs.
        foreach (['logo', 'favicon', 'og_image', 'bg3d_custom_file'] as $key) {
            $value = $settings[$key] ?? null;

            if (is_string($value) && $value !== '' && ! preg_match('#^(https?:)?//|^/#', $value)) {
                $settings[$key] = Storage::disk('public')->url($value);
            }
        }

        // a library model ("library:{slug}") reaches the browser as a custom .glb
        if ($library = ThreeDModel::fromKey($settings['bg3d_model'] ?? null)) {
            $settings['bg3d_model'] = Background3dModel::Custom->value;
            $settings['bg3d_custom_file'] = $library->fileUrl();
        }

        return [
            ...parent::share($request),
            'locale' => app()->getLocale(),
            'settings' => $settings,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }
}
