<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    /** Opening a message counts as reading it. */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->getRecord()->is_read) {
            $this->getRecord()->update(['is_read' => true]);
        }
    }

    protected function getHeaderActions(): array
    {
        /** @var ContactMessage $message */
        $message = $this->getRecord();
        $digits = preg_replace('/\D/', '', (string) $message->phone);

        return [
            Action::make('reply')
                ->label(__('Reply by email'))
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('primary')
                ->url('mailto:'.$message->email.'?subject='.rawurlencode('Re: '.($message->subject ?: __('Your enquiry'))), shouldOpenInNewTab: true)
                ->visible(filled($message->email)),
            Action::make('whatsapp')
                ->label(__('WhatsApp'))
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('success')
                ->url('https://wa.me/'.$digits, shouldOpenInNewTab: true)
                ->visible(filled($digits)),
            Action::make('call')
                ->label(__('Call'))
                ->icon(Heroicon::OutlinedPhone)
                ->color('gray')
                ->url('tel:'.($message->phone ? preg_replace('/[^\d+]/', '', $message->phone) : ''))
                ->visible(filled($message->phone)),
            Action::make('toggleRead')
                ->label(fn () => $this->getRecord()->is_read ? __('Mark as unread') : __('Mark as read'))
                ->icon(Heroicon::OutlinedEnvelopeOpen)
                ->color('gray')
                ->action(function () {
                    $record = $this->getRecord();
                    $record->update(['is_read' => ! $record->is_read]);

                    Notification::make()->title($record->is_read ? __('Marked as read') : __('Marked as unread'))->success()->send();
                }),
            DeleteAction::make(),
        ];
    }
}
