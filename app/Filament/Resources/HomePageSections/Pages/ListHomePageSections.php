<?php

namespace App\Filament\Resources\HomePageSections\Pages;

use App\Filament\Resources\HomePageSections\HomePageSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListHomePageSections extends ListRecords
{
    use Translatable;

    protected static string $resource = HomePageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
