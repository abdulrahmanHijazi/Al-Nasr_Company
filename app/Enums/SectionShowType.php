<?php

namespace App\Enums;

enum SectionShowType: string
{
    case Latest = 'latest';
    case Featured = 'featured';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Latest => __('Latest items'),
            self::Featured => __('Featured first, then latest'),
            self::Custom => __('Hand-picked items'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $t) => [$t->value => $t->label()])->all();
    }
}
