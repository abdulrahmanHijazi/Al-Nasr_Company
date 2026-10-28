<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Bundle;
use App\Models\ContactMessage;
use App\Models\Product;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected function getHeading(): ?string
    {
        return __('Store at a glance');
    }

    protected function getStats(): array
    {
        $products = Product::count();
        $active = Product::where('is_active', true)->count();
        $outOfStock = Product::where('is_active', true)->where('stock_qty', '<=', 0)->count();
        $offers = Bundle::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->count();
        $unread = ContactMessage::where('is_read', false)->count();

        return [
            Stat::make(__('Products'), $products)
                ->description(__(':count visible on the storefront', ['count' => $active]))
                ->descriptionIcon(Heroicon::OutlinedEye)
                ->color('primary'),
            Stat::make(__('Out of stock'), $outOfStock)
                ->description($outOfStock > 0 ? __('Restock or hide these products') : __('Everything is available'))
                ->color($outOfStock > 0 ? 'danger' : 'success'),
            Stat::make(__('Active offers'), $offers)
                ->description(__(':count brands carried', ['count' => Brand::where('is_active', true)->count()]))
                ->color('warning'),
            Stat::make(__('Unread messages'), $unread)
                ->description($unread > 0 ? __('Customers are waiting for a reply') : __('Inbox is clear'))
                ->color($unread > 0 ? 'warning' : 'success'),
        ];
    }
}
