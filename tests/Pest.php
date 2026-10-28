<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test case bindings
|--------------------------------------------------------------------------
| Every test boots the app and runs against a fresh in-memory SQLite database
| (see phpunit.xml). `php artisan migrate` also applies database/settings, so
| SiteSettings is available in feature tests.
*/
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/**
 * A user who can open /admin: holds Shield's super-admin role.
 */
function adminUser(array $attributes = []): App\Models\User
{
    $role = Spatie\Permission\Models\Role::findOrCreate(config('filament-shield.super_admin.name', 'super_admin'), 'web');

    $user = App\Models\User::factory()->create($attributes);
    $user->assignRole($role);

    return $user;
}
