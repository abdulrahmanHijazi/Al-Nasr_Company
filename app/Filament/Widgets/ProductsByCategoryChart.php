<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;

class ProductsByCategoryChart extends ChartWidget
{
    protected static ?int $sort = 15;

    public function getHeading(): string
    {
        return __('Products by category');
    }

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit(8)
            ->get();

        return [
            'datasets' => [[
                'label' => __('Products'),
                'data' => $categories->pluck('products_count')->all(),
                'backgroundColor' => ['#1d4ed8', '#d99a1f', '#0f766e', '#7c3aed', '#be123c', '#0369a1', '#4d7c0f', '#c2410c'],
                'borderWidth' => 0,
            ]],
            'labels' => $categories->map(fn (Category $c) => $c->name)->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
