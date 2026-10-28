<?php

namespace App\Filament\Resources\HomePageSections\Pages;

use App\Filament\Resources\HomePageSections\HomePageSectionResource;
use App\Filament\Resources\HomePageSections\Pages\Concerns\SortsPickedItems;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditHomePageSection extends EditRecord
{
    use Translatable;
    use SortsPickedItems;

    protected static string $resource = HomePageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $this->capturePickedOrder();
    }

    protected function afterSave(): void
    {
        $this->storePickedOrder();
    }
}
