<?php

namespace App\Filament\Resources\Bundles\Pages;

use App\Filament\Resources\Bundles\BundleResource;
use App\Filament\Resources\Bundles\Pages\Concerns\SyncsBundleItems;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateBundle extends CreateRecord
{
    use Translatable;
    use SyncsBundleItems;

    protected static string $resource = BundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->withoutBundleItems($data);
    }

    protected function beforeCreate(): void
    {
        $this->captureBundleItems();
    }

    protected function afterCreate(): void
    {
        $this->syncBundleItems();
    }
}
