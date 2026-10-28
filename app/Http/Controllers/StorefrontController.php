<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomePageSection;
use App\Models\Product;

/**
 * Props mirror resources/js/types/index.ts. Settings, locale and flash are
 * shared globally by App\Http\Middleware\HandleInertiaRequests.
 */
class StorefrontController extends Controller
{
    public function home()
    {
        // Sections are managed in Filament → Home page. Only while none have ever been
        // created is the built-in layout (HomePageSection::defaults()) used — once an admin
        // has sections, "Visible on the storefront" alone decides what appears.
        $sections = HomePageSection::active()->ordered()->get();

        if ($sections->isEmpty() && ! HomePageSection::query()->exists()) {
            $sections = HomePageSection::builtIn();
        }

        return Inertia::render('Shop/Home', [
            'sections' => $sections->map->toStorefront()->values(),
        ]);
    }

    public function shop(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'brand' => 'nullable|array',
            'brand.*' => 'string',
            'category' => 'nullable|array',
            'category.*' => 'string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'in_stock' => 'nullable|boolean',
            'sort' => 'nullable|in:latest,price_asc,price_desc,name',
        ]);

        $locale = app()->getLocale();
        $query = Product::with(['brand.media', 'categories', 'media'])->where('is_active', true);

        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(fn ($q) => $q
                ->where('sku', 'like', $term)
                ->orWhere("name->{$locale}", 'like', $term)
                ->orWhereHas('brand', fn ($b) => $b->where("name->{$locale}", 'like', $term)));
        }
        if (! empty($filters['brand'])) {
            $query->whereHas('brand', fn ($b) => $b->whereIn("slug->{$locale}", $filters['brand']));
        }
        if (! empty($filters['category'])) {
            // A parent category also matches products filed under its children.
            $query->whereHas('categories', fn ($c) => $c
                ->whereIn("slug->{$locale}", $filters['category'])
                ->orWhereHas('parent', fn ($p) => $p->whereIn("slug->{$locale}", $filters['category'])));
        }
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (! empty($filters['in_stock'])) {
            $query->where('stock_qty', '>', 0);
        }

        match ($filters['sort'] ?? 'latest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy("name->{$locale}"),
            default => $query->orderByDesc('is_featured')->latest(),
        };

        return Inertia::render('Shop/Index', [
            'products' => $query->paginate(12)->withQueryString(),
            'brands' => Brand::where('is_active', true)->withCount('products')->orderBy('sort_order')->get(),
            'categories' => Category::where('is_active', true)->withCount('products')
                ->with('children')->orderBy('sort_order')->get(),
            'filters' => $filters,
        ]);
    }

    public function showProduct(Product $product)
    {
        $product->load(['brand.media', 'categories', 'media', 'bundles.media', 'bundles.products.media']);

        $related = Product::with(['brand', 'media'])
            ->where('is_active', true)
            ->whereKeyNot($product->getKey())
            ->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $product->categories->pluck('id')))
            ->take(4)->get();

        return Inertia::render('Shop/Show', [
            'product' => $product,
            'related' => $related,
        ]);
    }
}
