<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $category->load([
            'media',
            'parent',
            'children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'products.brand',
            'products.media',
        ]);

        return Inertia::render('Categories/Show', ['category' => $category]);
    }
}
