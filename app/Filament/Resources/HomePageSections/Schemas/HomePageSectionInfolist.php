<?php

namespace App\Filament\Resources\HomePageSections\Schemas;

use App\Models\HomePageSection;
use App\Support\DesignPreview;
use App\Support\Model3dChoices;
use App\Support\ModelPreview;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class HomePageSectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make()->schema([
                    TextEntry::make('type')->hiddenLabel()->badge()->color('primary')
                        ->formatStateUsing(fn ($state) => $state->label()),
                    TextEntry::make('title')->hiddenLabel()->size(TextSize::Large)->weight(FontWeight::Bold)
                        ->placeholder(fn (HomePageSection $record) => $record->type->label()),
                    TextEntry::make('subtitle')->hiddenLabel()->color('gray')->placeholder('—'),
                ]),

                Section::make(__('Items shown'))
                    ->icon(Heroicon::OutlinedQueueList)
                    ->visible(fn (HomePageSection $record) => $record->type->usesItems())
                    ->schema([
                        TextEntry::make('items_preview')->hiddenLabel()->badge()->color('gray')
                            ->state(fn (HomePageSection $record) => $record->resolveItems()
                                ->map(fn ($item) => $item->name ?? $item->title ?? '#'.$item->getKey())
                                ->all())
                            ->placeholder('—'),
                    ]),

                Section::make(__('Content'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->visible(fn (HomePageSection $record) => filled($record->content))
                    ->schema([
                        TextEntry::make('content_summary')->hiddenLabel()
                            ->state(fn (HomePageSection $record) => self::summary($record))
                            ->listWithLineBreaks()
                            ->bulleted(),
                    ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Display'))
                    ->icon(Heroicon::OutlinedRectangleGroup)
                    ->schema([
                        ImageEntry::make('design_preview')->hiddenLabel()
                            ->state(fn (HomePageSection $record) => DesignPreview::url($record->type, $record->designKey()))
                            ->imageWidth('100%')->imageHeight('auto'),
                        TextEntry::make('design')->label('Design')
                            ->formatStateUsing(fn (?string $state, HomePageSection $record) => $record->type->designs()[$state ?? $record->type->defaultDesign()] ?? $state)
                            ->badge(),
                        TextEntry::make('show_type')->label('Source')
                            ->formatStateUsing(fn ($state, HomePageSection $record) => $record->type->usesItems() ? $state->label() : '—'),
                        TextEntry::make('limit')->label('How many')->visible(fn (HomePageSection $record) => $record->type->usesItems()),
                        ColorEntry::make('background_color')->label('Background')->placeholder('—'),
                    ]),
                Section::make(__('3D model'))
                    ->icon(Heroicon::OutlinedCube)
                    ->schema([
                        ImageEntry::make('model_3d_preview')->hiddenLabel()
                            ->state(fn (HomePageSection $record) => Model3dChoices::options()[$record->model_3d ?: Model3dChoices::NONE]['image'] ?? ModelPreview::noneUrl())
                            ->imageWidth('100%')->imageHeight('auto'),
                        TextEntry::make('model_3d')->label('3D model')->badge()
                            ->formatStateUsing(fn (?string $state) => Model3dChoices::label($state) ?? __('No 3D model')),
                    ]),
                Section::make(__('Status'))
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        IconEntry::make('is_active')->label('Visible on the storefront')->boolean(),
                        TextEntry::make('sort_order')->label('Sort order'),
                        TextEntry::make('updated_at')->label('Updated At')->since(),
                    ]),
            ]),
        ]);
    }

    /** @return array<int, string> a short, human-readable digest of the content JSON */
    private static function summary(HomePageSection $record): array
    {
        $content = $record->content ?? [];
        $pick = fn ($value) => is_array($value) ? ($value[app()->getLocale()] ?? $value['en'] ?? $value['ar'] ?? null) : $value;
        $lines = [];

        foreach (['headline', 'title', 'text', 'badge'] as $key) {
            if (filled($text = $pick($content[$key] ?? null))) {
                $lines[] = $text;
            }
        }

        foreach (['items', 'slides'] as $list) {
            foreach ($content[$list] ?? [] as $item) {
                $label = $pick($item['title'] ?? null) ?? $pick($item['label'] ?? null) ?? ($item['name'] ?? null) ?? ($item['value'] ?? null);

                if (filled($label)) {
                    $lines[] = (string) $label;
                }
            }
        }

        return $lines;
    }
}
