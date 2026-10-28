<?php

namespace App\Providers;

use App\Policies\RolePolicy;
use BezhanSalleh\LanguageSwitch\Enums\TriggerStyle;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\Entry;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Admin-only strings live in lang/admin/{locale}.json so they stay out of the storefront
        // bundle. Registered as soon as the translator exists, before any locale is loaded.
        $this->callAfterResolving('translator', function ($translator) {
            $translator->addJsonPath(lang_path('admin'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Shield's role manager lives outside Laravel's policy auto-discovery.
        Gate::policy(Role::class, RolePolicy::class);

        // /admin language switcher (Arabic ⇄ English; Filament flips to RTL by itself).
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en'])
                ->labels(['ar' => 'العربية', 'en' => 'English'])
                ->trigger(style: TriggerStyle::AvatarLabel)
                ->circular()
                // Arabic unless the admin picks English (their choice is kept in the session).
                ->userPreferredLocale(fn () => 'ar');
        });

        // Run every field / column / filter / tab label through the translator.
        Field::configureUsing(fn (Field $field) => $field->translateLabel());
        Entry::configureUsing(fn (Entry $entry) => $entry->translateLabel());
        Column::configureUsing(fn (Column $column) => $column->translateLabel());
        BaseFilter::configureUsing(fn (BaseFilter $filter) => $filter->translateLabel());
        Tab::configureUsing(fn (Tab $tab) => $tab->translateLabel());
    }
}
