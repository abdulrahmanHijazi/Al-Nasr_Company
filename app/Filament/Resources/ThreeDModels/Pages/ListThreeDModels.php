<?php

namespace App\Filament\Resources\ThreeDModels\Pages;

use App\Filament\Resources\ThreeDModels\ThreeDModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListThreeDModels extends ListRecords
{
    use Translatable;

    protected static string $resource = ThreeDModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
