<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Fills a missing `slug` translation from the matching name/title translation,
 * so admins never have to type slugs by hand. Works for Arabic as well: letters
 * from any script are kept (browsers handle the percent-encoding).
 *
 * Uniqueness is enforced per locale by appending -2, -3, …
 */
trait GeneratesTranslatedSlug
{
    public static function bootGeneratesTranslatedSlug(): void
    {
        static::saving(function ($model) {
            $source = $model->slugSourceAttribute();

            foreach ($model->getTranslations($source) as $locale => $text) {
                if (blank($text) || filled($model->getTranslation('slug', $locale, false))) {
                    continue;
                }

                $model->setTranslation('slug', $locale, $model->uniqueSlugFor($text, $locale));
            }
        });
    }

    /** Attribute the slug is derived from. Override when it isn't `name`. */
    public function slugSourceAttribute(): string
    {
        return 'name';
    }

    public static function slugify(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text) ?? '';

        return trim($text, '-');
    }

    protected function uniqueSlugFor(string $text, string $locale): string
    {
        $base = self::slugify($text) ?: Str::random(6);
        $slug = $base;
        $i = 2;

        while (
            static::query()
                ->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))
                ->where("slug->{$locale}", $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
