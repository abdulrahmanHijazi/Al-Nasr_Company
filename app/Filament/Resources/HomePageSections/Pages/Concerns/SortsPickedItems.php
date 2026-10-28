<?php

namespace App\Filament\Resources\HomePageSections\Pages\Concerns;

/**
 * Select::multiple() syncs ids without an order, and the form is re-filled from
 * the database (sorted by id) as soon as it is saved. The admin picks items in
 * the order they should appear, so that order is captured *before* saving and
 * written to the pivot's sort_order afterwards.
 */
trait SortsPickedItems
{
    private const PICKED_RELATIONS = ['products', 'categories', 'brands', 'bundles', 'portfolioItems'];

    /** @var array<string, array<int, int|string>> */
    protected array $pickedOrder = [];

    protected function capturePickedOrder(): void
    {
        $state = $this->form->getRawState();

        foreach (self::PICKED_RELATIONS as $relation) {
            $this->pickedOrder[$relation] = array_values($state[$relation] ?? []);
        }
    }

    protected function storePickedOrder(): void
    {
        $record = $this->getRecord();

        foreach ($this->pickedOrder as $relation => $ids) {
            foreach ($ids as $position => $id) {
                $record->{$relation}()->updateExistingPivot($id, ['sort_order' => $position]);
            }
        }
    }
}
