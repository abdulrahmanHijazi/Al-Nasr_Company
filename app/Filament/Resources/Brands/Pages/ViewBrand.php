<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Actions\ViewOnStorefrontAction;
use App\Filament\Resources\Brands\BrandResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;

class ViewBrand extends ViewRecord
{
    use Translatable;

    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            ViewOnStorefrontAction::make(),
            EditAction::make(),
        ];
    }
}
