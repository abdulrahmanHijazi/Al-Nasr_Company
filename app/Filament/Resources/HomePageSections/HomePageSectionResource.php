<?php

namespace App\Filament\Resources\HomePageSections;

use App\Enums\HomeSectionType;
use App\Filament\Resources\HomePageSections\Pages\CreateHomePageSection;
use App\Filament\Resources\HomePageSections\Pages\EditHomePageSection;
use App\Filament\Resources\HomePageSections\Pages\ListHomePageSections;
use App\Filament\Resources\HomePageSections\Schemas\HomePageSectionForm;
use App\Models\HomePageSection;
use App\Support\DesignPreview;
use App\Support\Model3dChoices;
use App\Filament\Resources\HomePageSections\Pages\ViewHomePageSection;
use App\Filament\Resources\HomePageSections\Schemas\HomePageSectionInfolist;
use Filament\Actions\ViewAction;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use UnitEnum;

/**
 * Builds the storefront home page: each row is one block (type) rendered with
 * one of several designs, in the order set here. Same idea as the elearning
 * dashboard's "Home Page Sections".
 */
class HomePageSectionResource extends Resource
{
    use Translatable;

    protected static ?string $model = HomePageSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Home page');
    }

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return __('home page section');
    }

    public static function getPluralModelLabel(): string
    {
        return __('home page sections');
    }

    public static function form(Schema $schema): Schema
    {
        return HomePageSectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HomePageSectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('preview')->label('Design')->width(96)->height(60)
                    ->state(fn (HomePageSection $record) => DesignPreview::url($record->type, $record->designKey()))->extraImgAttributes(['class' => 'rounded-md']),
                TextColumn::make('type')->badge()
                    ->formatStateUsing(fn (HomeSectionType $state) => $state->label()),
                TextColumn::make('design')
                    ->formatStateUsing(fn (?string $state, HomePageSection $record) => $record->type->designs()[$state ?? $record->type->defaultDesign()] ?? $state),
                TextColumn::make('title')->limit(40)->placeholder('—')->searchable(),
                TextColumn::make('show_type')->label('Items')
                    ->formatStateUsing(fn ($state, HomePageSection $record) => $record->type->usesItems() ? $state->label() : '—')
                    ->toggleable(),
                TextColumn::make('model_3d')->label('3D model')->badge()->color('info')->icon(Heroicon::OutlinedCube)
                    ->formatStateUsing(fn (?string $state) => Model3dChoices::label($state) ?? '—')
                    ->toggleable(),
                ColorColumn::make('background_color')->label('Background')->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('type')->options(HomeSectionType::options()),
                SelectFilter::make('model_3d')->label('3D model')->options(fn () => Model3dChoices::labels()),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
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
            'index' => ListHomePageSections::route('/'),
            'create' => CreateHomePageSection::route('/create'),
            'view' => ViewHomePageSection::route('/{record}'),
            'edit' => EditHomePageSection::route('/{record}/edit'),
        ];
    }
}
