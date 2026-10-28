<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use Inertia\Inertia;

class OfferController extends Controller
{
    public function index()
    {
        return Inertia::render('Offers/Index', [
            'bundles' => Bundle::with(['media', 'products.media'])
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function show(Bundle $bundle)
    {
        return Inertia::render('Offers/Show', [
            'bundle' => $bundle->load(['media', 'products.media']),
        ]);
    }
}
