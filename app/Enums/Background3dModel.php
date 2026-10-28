<?php

namespace App\Enums;

/**
 * The 3D models the storefront background can show (Site settings → 3D background).
 * Everything except `Custom` is built procedurally in the browser
 * (resources/js/lib/bg3d/models.ts) — no model files to download.
 */
enum Background3dModel: string
{
    case SolarPanel = 'solar-panel';
    case Battery = 'battery';
    case CctvCamera = 'cctv-camera';
    case Inverter = 'inverter';
    case ControlPanel = 'control-panel';
    case WindTurbine = 'wind-turbine';
    case Mixed = 'mixed';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::SolarPanel => __('Solar panel'),
            self::Battery => __('Lithium battery'),
            self::CctvCamera => __('Surveillance camera'),
            self::Inverter => __('Power inverter'),
            self::ControlPanel => __('Industrial control panel'),
            self::WindTurbine => __('Wind turbine'),
            self::Mixed => __('Mixed — all of them together'),
            self::Custom => __('My own model (.glb)'),
        };
    }

    public function previewPath(): string
    {
        return "images/models/{$this->value}.svg";
    }

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $m) => [$m->value => $m->label()])->all();
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $m) => $m->value, self::cases());
    }
}
