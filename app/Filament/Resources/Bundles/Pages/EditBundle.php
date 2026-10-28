<?php

namespace App\Filament\Resources\Bundles\Pages;

use App\Filament\Actions\ViewOnStorefrontAction;
use App\Filament\Resources\Bundles\BundleResource;
use App\Filament\Resources\Bundles\Pages\Concerns\SyncsBundleItems;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditBundle extends EditRecord
{
    use Translatable;
    use SyncsBundleItems;

    protected static string $resource = BundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            ViewOnStorefrontAction::make(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->getRecord()->products
            ->map(fn ($product) => ['product_id' => $product->id, 'qty' => $product->pivot->qty])
            ->values()
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->withoutBundleItems($data);
    }

    protected function beforeSave(): void
    {
        $this->captureBundleItems();
    }

    protected function afterSave(): void
    {
        $this->syncBundleItems();
    }
}
