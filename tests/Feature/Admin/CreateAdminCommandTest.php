<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

it('creates an administrator with the super-admin role', function () {
    $this->artisan('admin:create', [
        '--name' => 'Abdulrahman',
        '--email' => 'admin@alnasr.example',
        '--password' => 'S3cure-pass!',
    ])->assertSuccessful();

    $user = User::where('email', 'admin@alnasr.example')->firstOrFail();

    expect($user->name)->toBe('Abdulrahman')
        ->and(Hash::check('S3cure-pass!', $user->password))->toBeTrue()
        ->and($user->hasRole('super_admin'))->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull();
});

it('lets the created administrator into the panel', function () {
    $this->artisan('admin:create', [
        '--name' => 'Admin',
        '--email' => 'admin@alnasr.example',
        '--password' => 'S3cure-pass!',
    ])->assertSuccessful();

    $user = User::where('email', 'admin@alnasr.example')->firstOrFail();

    $this->actingAs($user)->get('/admin')->assertOk();
});

it('promotes and updates an existing account instead of failing', function () {
    $existing = User::factory()->create(['email' => 'staff@alnasr.example', 'name' => 'Staff']);

    $this->artisan('admin:create', [
        '--name' => 'Staff Admin',
        '--email' => 'staff@alnasr.example',
        '--password' => 'An0ther-pass!',
    ])->assertSuccessful();

    expect(User::count())->toBe(1)
        ->and($existing->fresh()->name)->toBe('Staff Admin')
        ->and($existing->fresh()->hasRole('super_admin'))->toBeTrue();
});

it('assigns a custom role when asked', function () {
    $this->artisan('admin:create', [
        '--email' => 'editor@alnasr.example',
        '--password' => 'S3cure-pass!',
        '--role' => 'editor',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $user = User::where('email', 'editor@alnasr.example')->firstOrFail();

    expect($user->hasRole('editor'))->toBeTrue()
        ->and(Role::where('name', 'editor')->exists())->toBeTrue();
});

it('rejects an invalid email or a short password', function () {
    $this->artisan('admin:create', [
        '--email' => 'not-an-email',
        '--password' => 'short',
        '--no-interaction' => true,
    ])->assertFailed();

    expect(User::count())->toBe(0);
});

it('needs email and password when run non-interactively', function () {
    $this->artisan('admin:create', ['--no-interaction' => true])->assertFailed();

    expect(User::count())->toBe(0);
});
