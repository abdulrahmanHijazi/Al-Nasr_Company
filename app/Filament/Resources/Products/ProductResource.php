<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Products\Schemas\ProductInfolist;
use App\Filament\Resources\Products\Schemas\ProductForm;
use Filament\Actions\ViewAction;
use App\Filament\Actions\ViewOnStorefrontAction;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use UnitEnum;

class ProductResource extends Resource
{
    use Translatable;

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Catalog');
    }

    public static function getModelLabel(): string
    {
        return __('Product');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Products');
    }

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['sku'];
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->square(),
                TextColumn::make('name')->searchable(query: fn (Builder $query, string $search) => $query
                    ->where('name', 'like', "%{$search}%"))->sortable()->limit(40),
                TextColumn::make('sku')->label('SKU')->searchable()->sortable()->copyable(),
                TextColumn::make('brand.name')->label('Brand')->toggleable(),
                TextColumn::make('price')->money('USD')->sortable(),
                TextColumn::make('stock_qty')->label('Stock')->sortable()
                    ->badge()
                    ->color(fn (int $state) => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    }),
                ToggleColumn::make('is_featured')->label('Featured'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('brand_id')->label('Brand')->relationship('brand', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Brand $record) => $record->name),
                SelectFilter::make('categories')->relationship('categories', 'name')->multiple()
                    ->getOptionLabelFromRecordUsing(fn (Category $record) => $record->name),
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('is_featured')->label('Featured'),
                Filter::make('out_of_stock')->label('Out of stock')
                    ->query(fn (Builder $query) => $query->where('stock_qty', '<=', 0)),
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
