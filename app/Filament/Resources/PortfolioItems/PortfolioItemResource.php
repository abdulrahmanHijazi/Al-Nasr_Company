<?php

namespace App\Filament\Resources\PortfolioItems;

use App\Filament\Resources\PortfolioItems\Pages\CreatePortfolioItem;
use App\Filament\Resources\PortfolioItems\Pages\EditPortfolioItem;
use App\Filament\Resources\PortfolioItems\Pages\ListPortfolioItems;
use App\Models\PortfolioItem;
use App\Filament\Resources\PortfolioItems\Pages\ViewPortfolioItem;
use App\Filament\Resources\PortfolioItems\Schemas\PortfolioItemInfolist;
use App\Filament\Resources\PortfolioItems\Schemas\PortfolioItemForm;
use Filament\Actions\ViewAction;
use App\Filament\Actions\ViewOnStorefrontAction;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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

class PortfolioItemResource extends Resource
{
    use Translatable;

    protected static ?string $model = PortfolioItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Content');
    }

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return __('project');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portfolio');
    }

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PortfolioItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PortfolioItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->square(),
                TextColumn::make('title')->searchable()->sortable()->limit(50),
                TextColumn::make('client_name')->label('Client')->searchable(),
                TextColumn::make('completed_at')->date()->sortable(),
                ToggleColumn::make('is_featured')->label('Featured'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('is_featured')->label('Featured'),
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
            'index' => ListPortfolioItems::route('/'),
            'create' => CreatePortfolioItem::route('/create'),
            'view' => ViewPortfolioItem::route('/{record}'),
            'edit' => EditPortfolioItem::route('/{record}/edit'),
        ];
    }
}
