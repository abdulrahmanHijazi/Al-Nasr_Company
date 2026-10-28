<?php

namespace App\Filament\Resources\Bundles\Pages;

use App\Filament\Actions\ViewOnStorefrontAction;
use App\Filament\Resources\Bundles\BundleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;

class ViewBundle extends ViewRecord
{
    use Translatable;

    protected static string $resource = BundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            ViewOnStorefrontAction::make(),
            EditAction::make(),
        ];
    }
}
