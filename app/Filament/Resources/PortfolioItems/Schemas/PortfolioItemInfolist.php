<?php

namespace App\Filament\Resources\PortfolioItems\Schemas;

use App\Models\PortfolioItem;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class PortfolioItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make()->schema([
                    SpatieMediaLibraryImageEntry::make('cover')->collection('cover')->hiddenLabel()
                        ->imageHeight(260)->imageWidth('100%'),
                    TextEntry::make('title')->hiddenLabel()->size(TextSize::Large)->weight(FontWeight::Bold),
                    TextEntry::make('description')->hiddenLabel()->html()->prose()->placeholder('—'),
                ]),

                Section::make(__('Gallery'))
                    ->icon(Heroicon::OutlinedPhoto)
                    ->collapsible()
                    ->visible(fn (PortfolioItem $record) => $record->hasMedia('gallery'))
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('gallery')->collection('gallery')->hiddenLabel()->imageHeight(120),
                    ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Project'))
                    ->icon(Heroicon::OutlinedBriefcase)
                    ->schema([
                        TextEntry::make('client_name')->label('Client')->icon(Heroicon::OutlinedUser)->placeholder('—'),
                        TextEntry::make('completed_at')->label('Completed on')->date()->icon(Heroicon::OutlinedCalendarDays)->placeholder('—'),
                        TextEntry::make('project_url')->label('Project URL')->icon(Heroicon::OutlinedLink)
                            ->url(fn (PortfolioItem $record) => $record->project_url, shouldOpenInNewTab: true)->placeholder('—'),
                    ]),
                Section::make(__('Status'))
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        IconEntry::make('is_active')->label('Visible on the storefront')->boolean(),
                        IconEntry::make('is_featured')->label('Featured')->boolean(),
                        TextEntry::make('sort_order')->label('Sort order'),
                        TextEntry::make('updated_at')->label('Updated At')->since(),
                    ]),
            ]),
        ]);
    }
}
