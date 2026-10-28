<?php

namespace App\Filament\Resources\PortfolioItems\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PortfolioItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make(__('Project details'))
                    ->description(__('Shown on the portfolio page as a case study.'))
                    ->icon(Heroicon::OutlinedBriefcase)
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->required()->maxLength(255)->autofocus()->columnSpanFull(),
                        TextInput::make('client_name')->label('Client')->maxLength(255)->prefixIcon(Heroicon::OutlinedUser),
                        DatePicker::make('completed_at')->label('Completed on'),
                        TextInput::make('slug')->maxLength(255)->helperText(__('Leave empty to generate it from the title.')),
                        TextInput::make('project_url')->label('Project URL')->url()->prefixIcon(Heroicon::OutlinedLink)->maxLength(255),
                    ]),
                Section::make(__('Description'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->collapsible()
                    ->schema([
                        RichEditor::make('description')->hiddenLabel()->columnSpanFull(),
                    ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Publishing'))
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        Toggle::make('is_active')->label('Visible on the storefront')->default(true)
                            ->helperText(__('Shown on the storefront once this is on.')),
                        Toggle::make('is_featured')->label('Featured')->default(false),
                        TextInput::make('sort_order')->numeric()->default(0)->helperText(__('Lower numbers appear first.')),
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
