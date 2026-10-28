<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make(__('Brand details'))
                    ->description(__('The name and story customers see on the brand page.'))
                    ->icon(Heroicon::OutlinedBuildingStorefront)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255)->autofocus(),
                        TextInput::make('slug')->maxLength(255)->helperText(__('Leave empty to generate it from the name.')),
                        Textarea::make('description')->rows(5)->columnSpanFull(),
                        TextInput::make('website_url')->label('Website')->url()->prefixIcon(Heroicon::OutlinedGlobeAlt)->maxLength(255)->columnSpanFull(),
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
                        SpatieMediaLibraryFileUpload::make('logo')->collection('logo')->image()->imageEditor(),
                        SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image(),
                    ]),
            ]),
        ]);
    }
}
