<?php

namespace App\Filament\Resources\HomePageSections\Pages;

use App\Filament\Resources\HomePageSections\HomePageSectionResource;
use App\Filament\Resources\HomePageSections\Pages\Concerns\SortsPickedItems;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateHomePageSection extends CreateRecord
{
    use Translatable;
    use SortsPickedItems;

    protected static string $resource = HomePageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }

    protected function beforeCreate(): void
    {
        $this->capturePickedOrder();
    }

    protected function afterCreate(): void
    {
        $this->storePickedOrder();
    }
}
