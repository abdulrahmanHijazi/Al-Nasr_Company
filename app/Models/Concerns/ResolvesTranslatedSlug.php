<?php

namespace App\Models\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Route-model binding for translatable `slug` columns (spatie/laravel-translatable
 * stores them as JSON, so a plain `where slug = ?` never matches).
 *
 * A slug resolves in *any* supported locale — switching language on the same URL
 * keeps working — and only active records are reachable from the storefront.
 */
trait ResolvesTranslatedSlug
{
    /**
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();

        if ($field !== 'slug') {
            return parent::resolveRouteBinding($value, $field);
        }

        return $this->resolveRouteBindingQuery($this, $value, $field)->first();
    }

    /**
     * @param  Model|Builder|Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): BuilderContract|Relation
    {
        $field ??= $this->getRouteKeyName();

        if ($field !== 'slug') {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        $locales = array_unique(array_filter([
            app()->getLocale(),
            config('app.fallback_locale'),
            'en',
            'ar',
        ]));

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($locales, $value) {
                foreach ($locales as $locale) {
                    $q->orWhere("slug->{$locale}", $value);
                }
            });
    }
}
