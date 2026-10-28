<?php

namespace App\Filament\Resources\ThreeDModels;

use App\Enums\ThreeDModelType;
use App\Filament\Resources\ThreeDModels\Pages\CreateThreeDModel;
use App\Filament\Resources\ThreeDModels\Pages\EditThreeDModel;
use App\Filament\Resources\ThreeDModels\Pages\ListThreeDModels;
use App\Models\ThreeDModel;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use UnitEnum;

/**
 * The equipment 3D library. Loaded from database/models3d by `php artisan models3d:import`;
 * add your own .glb here or drop real manufacturer models into that folder and import again.
 */
class ThreeDModelResource extends Resource
{
    use Translatable;

    protected static ?string $model = ThreeDModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'slug';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Home page');
    }

    public static function getModelLabel(): string
    {
        return __('3D model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('3D models');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make(__('Model'))
                    ->icon(Heroicon::OutlinedCube)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255)->columnSpanFull(),
                        TextInput::make('brand')->maxLength(100)->helperText(__('For example Voltronic, Felicity, Solis, Tobac.')),
                        Select::make('type')->options(ThreeDModelType::options())->required()->default(ThreeDModelType::Other->value),
                        TextInput::make('slug')->required()->maxLength(120)->unique(ignoreRecord: true)->alphaDash()
                            ->helperText(__('Used internally to refer to this model — letters, numbers and dashes.')),
                    ]),
                Section::make(__('Files'))
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->columns(2)
                    ->schema([
                        FileUpload::make('file')->label('3D model file')->required()
                            ->disk('public')->directory('models3d')->visibility('public')
                            ->acceptedFileTypes(['model/gltf-binary', 'application/octet-stream', 'model/gltf+json'])
                            ->rules(['extensions:glb,gltf'])
                            ->maxSize(15 * 1024)
                            ->helperText(__('A .glb file (up to 15 MB).')),
                        FileUpload::make('preview')->label('Picture')->image()
                            ->disk('public')->directory('models3d/previews')->visibility('public')
                            ->helperText(__('Shown in the pickers. Optional.')),
                    ]),
            ]),
            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Publishing'))
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        Toggle::make('is_active')->label('Available in the pickers')->default(true),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('preview')->label('Picture')->disk('public')->width(96)->height(60)->extraImgAttributes(['class' => 'rounded-md']),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('brand')->badge()->color('gray')->searchable()->placeholder('—'),
                TextColumn::make('type')->badge()->formatStateUsing(fn (ThreeDModelType $state) => $state->label()),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('type')
            ->filters([
                SelectFilter::make('type')->options(ThreeDModelType::options()),
                SelectFilter::make('brand')->options(fn () => ThreeDModel::query()->whereNotNull('brand')->distinct()->orderBy('brand')->pluck('brand', 'brand')->all()),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListThreeDModels::route('/'),
            'create' => CreateThreeDModel::route('/create'),
            'edit' => EditThreeDModel::route('/{record}/edit'),
        ];
    }
}
