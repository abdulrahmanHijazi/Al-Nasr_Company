<?php

namespace App\Filament\Resources\PortfolioItems\Pages;

use App\Filament\Actions\ViewOnStorefrontAction;
use App\Filament\Resources\PortfolioItems\PortfolioItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditPortfolioItem extends EditRecord
{
    use Translatable;

    protected static string $resource = PortfolioItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            ViewOnStorefrontAction::make(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
