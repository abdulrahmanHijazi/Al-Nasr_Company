<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Opens the record's public page on the storefront in a new tab. Only shown for
 * records that are switched on ("Visible on the storefront") and have a public page.
 */
class ViewOnStorefrontAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'viewOnStorefront';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('View on storefront'))
            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
            ->color('gray')
            ->url(fn ($record) => $record && method_exists($record, 'storefrontUrl') ? $record->storefrontUrl() : null, shouldOpenInNewTab: true)
            ->visible(fn ($record) => $record
                && method_exists($record, 'storefrontUrl')
                && (bool) ($record->is_active ?? false)
                && $record->storefrontUrl() !== null);
    }
}
