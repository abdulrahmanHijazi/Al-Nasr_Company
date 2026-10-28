<?php

namespace App\Filament\Resources\ThreeDModels\Pages;

use App\Filament\Resources\ThreeDModels\ThreeDModelResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateThreeDModel extends CreateRecord
{
    use Translatable;

    protected static string $resource = ThreeDModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }
}
