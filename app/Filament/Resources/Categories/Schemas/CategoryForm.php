<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make(__('Category details'))
                    ->description(__('Categories can be nested one level — e.g. Inverters → Hybrid inverters.'))
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255)->autofocus(),
                        TextInput::make('slug')->maxLength(255)->helperText(__('Leave empty to generate it from the name.')),
                        Select::make('parent_id')
                            ->label('Parent category')
                            ->relationship('parent', 'name', fn ($query) => $query->whereNull('parent_id'))
                            ->getOptionLabelFromRecordUsing(fn (Category $record) => $record->name)
                            ->searchable()
                            ->preload()
                            ->placeholder(__('— top level —'))
                            ->columnSpanFull(),
                        Textarea::make('description')->rows(4)->columnSpanFull(),
                    ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Publishing'))
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        Toggle::make('is_active')->label('Visible on the storefront')->default(true)
                            ->helperText(__('Shown on the storefront once this is on.')),
                        TextInput::make('sort_order')->numeric()->default(0)->helperText(__('Lower numbers appear first.')),
                    ]),
                Section::make(__('Images'))
                    ->icon(Heroicon::OutlinedPhoto)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('image')->collection('image')->image()->imageEditor(),
                        SpatieMediaLibraryFileUpload::make('banner')->collection('banner')->image(),
                    ]),
            ]),
        ]);
    }
}
