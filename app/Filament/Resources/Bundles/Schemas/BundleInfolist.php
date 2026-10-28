<?php

namespace App\Filament\Resources\Bundles\Schemas;

use App\Models\Bundle;
use App\Settings\SiteSettings;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class BundleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $currency = fn () => app(SiteSettings::class)->currency;

        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make()->schema([
                    SpatieMediaLibraryImageEntry::make('cover')->collection('cover')->hiddenLabel()
                        ->imageHeight(220)->imageWidth('100%'),
                    TextEntry::make('name')->hiddenLabel()->size(TextSize::Large)->weight(FontWeight::Bold),
                    TextEntry::make('description')->hiddenLabel()->color('gray')->placeholder('—'),
                ]),

                Section::make(__('Included products'))
                    ->icon(Heroicon::OutlinedCube)
                    ->schema([
                        RepeatableEntry::make('products')->hiddenLabel()->contained(false)->schema([
                            Grid::make(['default' => 4])->schema([
                                TextEntry::make('name')->hiddenLabel()->weight(FontWeight::SemiBold)->columnSpan(2),
                                TextEntry::make('pivot.qty')->hiddenLabel()->badge()->color('primary')->prefix('× '),
                                TextEntry::make('price')->hiddenLabel()->money($currency)->alignEnd(),
                                TextEntry::make('sku')->hiddenLabel()->color('gray')->size(TextSize::Small)->columnSpanFull(),
                            ]),
                        ]),
                    ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Pricing'))
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        TextEntry::make('bundle_price')->label('Bundle price')->money($currency)->size(TextSize::Large)->weight(FontWeight::Bold)->color('primary'),
                        TextEntry::make('original_total')->label('Bought separately')->state(fn (Bundle $record) => $record->original_total)->money($currency)->color('gray'),
                        TextEntry::make('savings')->label('Savings')->state(fn (Bundle $record) => $record->savings)->money($currency)->badge()->color('success'),
                    ]),
                Section::make(__('Status'))
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        IconEntry::make('is_active')->label('Visible on the storefront')->boolean(),
                        TextEntry::make('starts_at')->label('Starts at')->dateTime()->placeholder('—'),
                        TextEntry::make('ends_at')->label('Ends at')->dateTime()->placeholder(__('No end date')),
                        TextEntry::make('updated_at')->label('Updated At')->since(),
                    ]),
            ]),
        ]);
    }
}
