<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestMessages extends TableWidget
{
    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return __('Latest enquiries');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => ContactMessage::query()->latest())
            ->columns([
                TextColumn::make('name')->weight('bold'),
                TextColumn::make('subject')->limit(50)->placeholder('—'),
                TextColumn::make('phone')->placeholder('—'),
                TextColumn::make('is_read')->label(__('Status'))->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? __('Read') : __('New'))
                    ->color(fn (bool $state) => $state ? 'gray' : 'warning'),
                TextColumn::make('created_at')->label('Received')->since(),
            ])
            ->recordUrl(fn (ContactMessage $record) => ContactMessageResource::getUrl('view', ['record' => $record]))
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}
