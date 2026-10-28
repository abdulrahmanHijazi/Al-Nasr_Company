<?php

namespace App\Filament\Resources\Bundles;

use App\Filament\Resources\Bundles\Pages\CreateBundle;
use App\Filament\Resources\Bundles\Pages\EditBundle;
use App\Filament\Resources\Bundles\Pages\ListBundles;
use App\Models\Bundle;
use App\Models\Product;
use App\Filament\Resources\Bundles\Pages\ViewBundle;
use App\Filament\Resources\Bundles\Schemas\BundleInfolist;
use App\Filament\Resources\Bundles\Schemas\BundleForm;
use Filament\Actions\ViewAction;
use App\Filament\Actions\ViewOnStorefrontAction;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use UnitEnum;

class BundleResource extends Resource
{
    use Translatable;

    protected static ?string $model = Bundle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Marketing');
    }

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return __('special offer');
    }

    public static function getPluralModelLabel(): string
    {
        return __('special offers');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BundleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BundleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->square(),
                TextColumn::make('name')->searchable()->sortable()->limit(40),
                TextColumn::make('bundle_price')->money('USD')->sortable(),
                TextColumn::make('savings')->money('USD')->color('success'),
                TextColumn::make('products_count')->counts('products')->label('Items'),
                TextColumn::make('ends_at')->dateTime()->placeholder(__('No end date'))->sortable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                ViewOnStorefrontAction::make()->iconButton()->hiddenLabel()->tooltip(__('View on storefront')),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBundles::route('/'),
            'create' => CreateBundle::route('/create'),
            'view' => ViewBundle::route('/{record}'),
            'edit' => EditBundle::route('/{record}/edit'),
        ];
    }
}
