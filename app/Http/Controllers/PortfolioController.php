<?php

namespace App\Http\Controllers;

use App\Models\PortfolioItem;
use Inertia\Inertia;

class PortfolioController extends Controller
{
    public function index()
    {
        return Inertia::render('Portfolio/Index', [
            'items' => PortfolioItem::with('media')
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function show(PortfolioItem $item)
    {
        return Inertia::render('Portfolio/Show', ['item' => $item->load('media')]);
    }
}
