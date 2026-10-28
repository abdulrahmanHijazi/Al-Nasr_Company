<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Settings\SiteSettings;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $currency = fn () => app(SiteSettings::class)->currency;

        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make()->schema([
                    Flex::make([
                        SpatieMediaLibraryImageEntry::make('cover')->collection('cover')->hiddenLabel()
                            ->imageSize(150)->square()->grow(false),
                        Group::make([
                            TextEntry::make('name')->hiddenLabel()->size(TextSize::Large)->weight(FontWeight::Bold),
                            TextEntry::make('brand.name')->hiddenLabel()->badge()->color('primary')->icon(Heroicon::OutlinedBuildingStorefront)->placeholder(__('No brand')),
                            TextEntry::make('categories.name')->hiddenLabel()->badge()->color('gray'),
                            TextEntry::make('short_description')->hiddenLabel()->color('gray')->placeholder('—'),
                        ]),
                    ])->from('md'),
                ]),

                Section::make(__('Description'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        TextEntry::make('description')->hiddenLabel()->html()->prose()->placeholder('—'),
                    ]),

                Section::make(__('Gallery'))
                    ->icon(Heroicon::OutlinedPhoto)
                    ->collapsible()
                    ->visible(fn (Product $record) => $record->hasMedia('gallery'))
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('gallery')->collection('gallery')->hiddenLabel()->imageHeight(120),
                    ]),

                Section::make(__('Included in offers'))
                    ->icon(Heroicon::OutlinedGift)
                    ->collapsible()
                    ->visible(fn (Product $record) => $record->bundles()->exists())
                    ->schema([
                        RepeatableEntry::make('bundles')->hiddenLabel()->contained(false)->grid(2)->schema([
                            Grid::make(['default' => 3])->schema([
                                TextEntry::make('name')->hiddenLabel()->weight(FontWeight::SemiBold)->columnSpan(2),
                                TextEntry::make('bundle_price')->hiddenLabel()->money($currency)->alignEnd(),
                            ]),
                        ]),
                    ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Pricing'))
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        TextEntry::make('price')->money($currency)->size(TextSize::Large)->weight(FontWeight::Bold)->color('primary'),
                        TextEntry::make('compare_price')->money($currency)->placeholder('—')->color('gray'),
                        TextEntry::make('cost_price')->money($currency)->placeholder('—'),
                        TextEntry::make('margin')
                            ->label('Margin')
                            ->state(fn (Product $record) => $record->cost_price && (float) $record->price > 0
                                ? round(((float) $record->price - (float) $record->cost_price) / (float) $record->price * 100).'%'
                                : null)
                            ->badge()->color('success')->placeholder('—'),
                    ]),
                Section::make(__('Inventory & status'))
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->schema([
                        TextEntry::make('sku')->label('SKU')->copyable()->icon(Heroicon::OutlinedQrCode),
                        TextEntry::make('stock_qty')->label('Stock')->badge()
                            ->color(fn (int $state) => match (true) {
                                $state <= 0 => 'danger',
                                $state <= 5 => 'warning',
                                default => 'success',
                            }),
                        IconEntry::make('is_active')->label('Visible on the storefront')->boolean(),
                        IconEntry::make('is_featured')->label('Featured')->boolean(),
                        TextEntry::make('updated_at')->label('Updated At')->since(),
                    ]),
            ]),
        ]);
    }
}
