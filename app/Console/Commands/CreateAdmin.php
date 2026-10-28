<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateAdmin extends Command
{
    protected $description = 'Create (or promote) an administrator who can sign in at /admin';

    protected $signature = 'admin:create
        {--name= : Full name}
        {--email= : Email address used to sign in}
        {--password= : Password (min 8 characters). Prompted when omitted}
        {--role= : Role to assign (defaults to the Shield super-admin role)}';

    public function handle(): int
    {
        $interactive = $this->input->isInteractive();

        $name = $this->option('name') ?: ($interactive ? text('Name', default: 'Administrator', required: true) : 'Administrator');
        $email = $this->option('email') ?: ($interactive ? text('Email address', required: true) : null);
        $plain = $this->option('password') ?: ($interactive ? password('Password (min 8 characters)', required: true) : null);

        if (! $email || ! $plain) {
            $this->components->error('In non-interactive mode pass --email and --password.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $plain],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email:rfc', 'max:255'],
                'password' => ['required', Password::min(8)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->components->error($message);
            }

            return self::FAILURE;
        }

        $roleName = $this->option('role') ?: config('filament-shield.super_admin.name', 'super_admin');
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

        $existing = User::where('email', $email)->first();

        if ($existing) {
            $this->components->warn("A user with {$email} already exists — updating name, password and role.");
        }

        $user = User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => $plain]);
        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

        $user->syncRoles([$role]);

        $this->components->info($existing ? 'Administrator updated.' : 'Administrator created.');
        $this->table(['Name', 'Email', 'Role'], [[$user->name, $user->email, $role->name]]);
        $this->line('Sign in at <href='.url('/admin').'>'.url('/admin').'</>');

        return self::SUCCESS;
    }
}
