<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function index()
    {
        return Inertia::render('Brands/Index', [
            'brands' => Brand::with('media')
                ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function show(Brand $brand)
    {
        $brand->load([
            'media',
            'products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'products.media',
        ]);

        return Inertia::render('Brands/Show', ['brand' => $brand]);
    }
}
