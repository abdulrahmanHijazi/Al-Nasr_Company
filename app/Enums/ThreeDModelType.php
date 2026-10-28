<?php

namespace App\Enums;

/** What kind of equipment a 3D library model shows (App\Models\ThreeDModel). */
enum ThreeDModelType: string
{
    case Inverter = 'inverter';
    case Battery = 'battery';
    case ChargeController = 'charge-controller';
    case Breaker = 'breaker';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Inverter => __('Inverters'),
            self::Battery => __('Lithium batteries'),
            self::ChargeController => __('Solar charge controllers'),
            self::Breaker => __('Breakers'),
            self::Other => __('Other equipment'),
        };
    }

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $t) => [$t->value => $t->label()])->all();
    }
}
