<?php

namespace App\Filament\Resources\Brands\Schemas;

use App\Models\Brand;
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

class BrandInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make()->schema([
                    Flex::make([
                        SpatieMediaLibraryImageEntry::make('logo')->collection('logo')->hiddenLabel()
                            ->imageSize(96)->square()->grow(false),
                        Group::make([
                            TextEntry::make('name')->hiddenLabel()->size(TextSize::Large)->weight(FontWeight::Bold),
                            TextEntry::make('website_url')->hiddenLabel()->icon(Heroicon::OutlinedGlobeAlt)->url(fn (Brand $record) => $record->website_url, shouldOpenInNewTab: true)->placeholder('—'),
                            TextEntry::make('description')->hiddenLabel()->color('gray')->placeholder('—'),
                        ]),
                    ])->from('md'),
                ]),

                Section::make(__('Products'))
                    ->icon(Heroicon::OutlinedCube)
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('products')->hiddenLabel()->contained(false)->grid(2)->schema([
                            Grid::make(['default' => 3])->schema([
                                TextEntry::make('name')->hiddenLabel()->weight(FontWeight::SemiBold)->columnSpan(2),
                                TextEntry::make('price')->hiddenLabel()->money(fn () => app(\App\Settings\SiteSettings::class)->currency)->alignEnd(),
                                TextEntry::make('sku')->hiddenLabel()->color('gray')->size(TextSize::Small)->columnSpanFull(),
                            ]),
                        ]),
                    ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Status'))
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        IconEntry::make('is_active')->label('Visible on the storefront')->boolean(),
                        TextEntry::make('products_count')->label('Products')->state(fn (Brand $record) => $record->products()->count())->badge()->color('primary'),
                        TextEntry::make('sort_order')->label('Sort order'),
                        TextEntry::make('updated_at')->label('Updated At')->since(),
                    ]),
                Section::make(__('Cover'))
                    ->icon(Heroicon::OutlinedPhoto)
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('cover')->collection('cover')->hiddenLabel()->imageHeight(160)->imageWidth('100%'),
                    ]),
            ]),
        ]);
    }
}
