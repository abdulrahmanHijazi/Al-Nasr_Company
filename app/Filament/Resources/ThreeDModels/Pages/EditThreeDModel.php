<?php

namespace App\Filament\Resources\ThreeDModels\Pages;

use App\Filament\Resources\ThreeDModels\ThreeDModelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditThreeDModel extends EditRecord
{
    use Translatable;

    protected static string $resource = ThreeDModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }
}
