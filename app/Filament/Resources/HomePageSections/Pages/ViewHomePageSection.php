<?php

namespace App\Filament\Resources\HomePageSections\Pages;

use App\Filament\Resources\HomePageSections\HomePageSectionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;

class ViewHomePageSection extends ViewRecord
{
    use Translatable;

    protected static string $resource = HomePageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            EditAction::make(),
        ];
    }
}
