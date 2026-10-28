<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Models\ContactMessage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make(fn (ContactMessage $record) => $record->subject ?: __('Message'))
                    ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                    ->description(fn (ContactMessage $record) => $record->created_at?->diffForHumans())
                    ->schema([
                        TextEntry::make('message')
                            ->hiddenLabel()
                            ->size(TextSize::Medium)
                            ->prose()
                            ->extraAttributes(['class' => 'whitespace-pre-line leading-8']),
                    ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Sender'))
                    ->icon(Heroicon::OutlinedUser)
                    ->schema([
                        TextEntry::make('name')->hiddenLabel()->size(TextSize::Large)->weight(FontWeight::Bold),
                        TextEntry::make('email')->label('Email')->icon(Heroicon::OutlinedEnvelope)->copyable()
                            ->url(fn (ContactMessage $record) => $record->email ? 'mailto:'.$record->email : null)->placeholder('—'),
                        TextEntry::make('phone')->label('Phone')->icon(Heroicon::OutlinedPhone)->copyable()
                            ->url(fn (ContactMessage $record) => $record->phone ? 'tel:'.preg_replace('/[^\d+]/', '', $record->phone) : null)->placeholder('—'),
                    ]),
                Section::make(__('Status'))
                    ->icon(Heroicon::OutlinedInboxArrowDown)
                    ->schema([
                        TextEntry::make('is_read')->label('Status')->badge()
                            ->formatStateUsing(fn (bool $state) => $state ? __('Read') : __('New'))
                            ->color(fn (bool $state) => $state ? 'gray' : 'warning'),
                        TextEntry::make('created_at')->label('Received')->dateTime(),
                    ]),
            ]),
        ]);
    }
}
