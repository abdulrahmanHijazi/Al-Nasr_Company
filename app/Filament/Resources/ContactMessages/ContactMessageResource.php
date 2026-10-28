<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\ContactMessages\Schemas\ContactMessageInfolist;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * Inbox for the storefront contact form. Messages arrive from the public site,
 * so they can be read, marked and deleted — never created or edited here.
 */
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Inbox');
    }

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return __('message');
    }

    protected static ?string $recordTitleAttribute = 'subject';

    public static function getPluralModelLabel(): string
    {
        return __('Messages');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $unread = ContactMessage::where('is_read', false)->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactMessageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_read')->label('Read')->boolean()
                    ->trueIcon(Heroicon::OutlinedEnvelopeOpen)->falseIcon(Heroicon::Envelope)
                    ->trueColor('gray')->falseColor('warning'),
                TextColumn::make('name')->searchable()->weight(fn (ContactMessage $record) => $record->is_read ? null : 'bold'),
                TextColumn::make('subject')->searchable()->limit(40)->placeholder('—'),
                TextColumn::make('phone')->searchable()->copyable()->toggleable(),
                TextColumn::make('email')->searchable()->copyable()->toggleable(),
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_read')->label('Read'),
            ])
            ->recordActions([
                ViewAction::make()->label(__('Open')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markAsRead')
                        ->label(__('Mark as read'))
                        ->icon(Heroicon::OutlinedEnvelopeOpen)
                        ->action(fn (Collection $records) => $records->each->update(['is_read' => true]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
        ];
    }
}
