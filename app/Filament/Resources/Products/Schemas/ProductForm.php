<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Settings\SiteSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make(__('Product details'))
                    ->description(__('What customers search for and see first.'))
                    ->icon(Heroicon::OutlinedCube)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255)->autofocus()->columnSpanFull(),
                        TextInput::make('sku')->label('SKU')->required()->unique(ignoreRecord: true)->maxLength(100)
                            ->prefixIcon(Heroicon::OutlinedQrCode),
                        TextInput::make('slug')->maxLength(255)->helperText(__('Leave empty to generate it from the name.')),
                        Select::make('brand_id')
                            ->label('Brand')
                            ->relationship('brand', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Brand $record) => $record->name)
                            ->searchable()
                            ->preload(),
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Category $record) => $record->name)
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Textarea::make('short_description')->rows(2)->columnSpanFull(),
                    ]),

                Section::make(__('Description'))
                    ->description(__('Full details, specifications and notes shown on the product page.'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->collapsible()
                    ->schema([
                        RichEditor::make('description')->hiddenLabel()->columnSpanFull(),
                    ]),

                Section::make(__('Pricing'))
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')->numeric()->required()->minValue(0)->prefix(fn () => app(SiteSettings::class)->currency),
                        TextInput::make('compare_price')->numeric()->minValue(0)->prefix(fn () => app(SiteSettings::class)->currency)
                            ->helperText(__('Shown struck through, e.g. the old price.')),
                        TextInput::make('cost_price')->numeric()->minValue(0)->prefix(fn () => app(SiteSettings::class)->currency)
                            ->helperText(__('Internal only — never shown on the storefront.')),
                    ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Publishing'))
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        Toggle::make('is_active')->label('Visible on the storefront')->default(true)
                            ->helperText(__('Shown on the storefront once this is on.')),
                        Toggle::make('is_featured')->label('Featured')->default(false),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ]),
                Section::make(__('Inventory'))
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->schema([
                        TextInput::make('stock_qty')->label('Stock')->numeric()->integer()->minValue(0)->default(0),
                    ]),
                Section::make(__('Images'))
                    ->icon(Heroicon::OutlinedPhoto)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->imageEditor(),
                        SpatieMediaLibraryFileUpload::make('gallery')->collection('gallery')->image()->multiple()->reorderable(),
                    ]),
            ]),
        ]);
    }
}
