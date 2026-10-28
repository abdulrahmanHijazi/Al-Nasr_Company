<?php

namespace App\Filament\Resources\PortfolioItems\Pages;

use App\Filament\Actions\ViewOnStorefrontAction;
use App\Filament\Resources\PortfolioItems\PortfolioItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;

class ViewPortfolioItem extends ViewRecord
{
    use Translatable;

    protected static string $resource = PortfolioItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            ViewOnStorefrontAction::make(),
            EditAction::make(),
        ];
    }
}
