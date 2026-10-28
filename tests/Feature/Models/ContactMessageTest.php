<?php

use App\Models\ContactMessage;

it('persists a message and defaults to unread', function () {
    $message = ContactMessage::create([
        'name' => 'Khalid',
        'email' => 'khalid@example.com',
        'phone' => '+966 50 000 0000',
        'subject' => 'Quote: 10 kW solar system',
        'message' => 'We need a quote for a 10 kW on-grid system with lithium storage.',
    ])->fresh();

    expect($message->is_read)->toBeFalse()
        ->and($message->subject)->toBe('Quote: 10 kW solar system')
        ->and($message->created_at)->not->toBeNull();
});

it('allows email, phone and subject to be omitted', function () {
    $message = ContactMessage::create(['name' => 'Sara', 'message' => 'Call me please.'])->fresh();

    expect($message->email)->toBeNull()
        ->and($message->phone)->toBeNull()
        ->and($message->subject)->toBeNull();
});

it('casts is_read to a boolean and can be marked as read', function () {
    $message = ContactMessage::factory()->create();

    expect($message->is_read)->toBeFalse();

    $message->update(['is_read' => 1]);

    expect($message->fresh()->is_read)->toBeTrue();
});

it('can filter unread messages for the admin inbox', function () {
    ContactMessage::factory()->count(2)->create();
    ContactMessage::factory()->create(['is_read' => true]);

    expect(ContactMessage::where('is_read', false)->count())->toBe(2);
});
