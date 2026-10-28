<?php

namespace App\Filament\Resources\Bundles\Pages\Concerns;

use Illuminate\Support\Arr;

/**
 * The bundle form edits its products through a plain `items` repeater
 * (product_id + qty). That state is not a column, so it is
 *  1. read from the live form *before* saving (the translatable plugin calls
 *     mutateFormDataBeforeSave() once per locale, so the mutate hooks can't own it),
 *  2. stripped from the data that reaches the model,
 *  3. written to the bundle_product pivot once the record exists.
 */
trait SyncsBundleItems
{
    /** @var array<int|string, array{product_id: int|string|null, qty: int|string|null}> */
    protected array $bundleItems = [];

    protected function captureBundleItems(): void
    {
        $this->bundleItems = $this->form->getRawState()['items'] ?? [];
    }

    protected function withoutBundleItems(array $data): array
    {
        return Arr::except($data, ['items']);
    }

    protected function syncBundleItems(): void
    {
        $sync = collect($this->bundleItems)
            ->filter(fn ($item) => filled($item['product_id'] ?? null))
            ->mapWithKeys(fn ($item) => [
                (int) $item['product_id'] => ['qty' => max(1, (int) ($item['qty'] ?? 1))],
            ])
            ->all();

        $this->getRecord()->products()->sync($sync);
    }
}
