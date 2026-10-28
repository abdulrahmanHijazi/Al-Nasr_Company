<?php

namespace App\Support;

use App\Enums\Background3dModel;

/**
 * Small illustrations for the 3D-background picker in Site settings.
 * Generated like DesignPreview:  php artisan design:previews
 */
class ModelPreview
{
    private const NAVY = '#0b1f3a';

    private const BLUE = '#1d4ed8';

    private const SKY = '#60a5fa';

    private const GOLD = '#d99a1f';

    private const STEEL = '#cbd5e1';

    private const SLATE = '#64748b';

    private const GREEN = '#4ade80';

    public static function url(Background3dModel $model): string
    {
        return asset($model->previewPath());
    }

    /** The "no 3D model" card shown first in per-section pickers. */
    public static function noneSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 200" width="320" height="200" role="img">
<rect width="320" height="200" fill="#f1f5f9"/>
<g transform="translate(160 100)" fill="none" stroke="#94a3b8" stroke-width="3" stroke-linecap="round" stroke-dasharray="7 7">
<path d="M0 -50 L44 -25 L44 25 L0 50 L-44 25 L-44 -25 Z"/>
</g>
<path d="M-26 -26 L26 26" transform="translate(160 100)" stroke="#dc2626" stroke-width="5" stroke-linecap="round"/>
</svg>

SVG;
    }

    public static function noneUrl(): string
    {
        return asset('images/models/none.svg');
    }

    public static function svg(Background3dModel $model): string
    {
        $art = match ($model) {
            Background3dModel::SolarPanel => self::solar(0, 0, 1),
            Background3dModel::Battery => self::battery(0, 0, 1),
            Background3dModel::CctvCamera => self::camera(0, 0, 1),
            Background3dModel::Inverter => self::inverter(0, 0, 1),
            Background3dModel::ControlPanel => self::panel(0, 0, 1),
            Background3dModel::WindTurbine => self::turbine(0, 0, 1),
            Background3dModel::Mixed => self::solar(-62, 28, .5).self::battery(70, 40, .45).self::camera(40, -34, .42).self::turbine(-70, -26, .42).self::inverter(2, 12, .42),
            Background3dModel::Custom => self::custom(),
        };

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 200" width="320" height="200" role="img">
<defs>
<radialGradient id="glow" cx="50%" cy="46%" r="60%"><stop offset="0" stop-color="#1e3a8a"/><stop offset="1" stop-color="#0b1f3a"/></radialGradient>
<linearGradient id="metal" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f1f5f9"/><stop offset="1" stop-color="#94a3b8"/></linearGradient>
<linearGradient id="cell" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1e40af"/><stop offset="1" stop-color="#0f172a"/></linearGradient>
</defs>
<rect width="320" height="200" fill="url(#glow)"/>
<ellipse cx="160" cy="176" rx="100" ry="9" fill="#000" opacity=".25"/>
<g transform="translate(160 100)">{$art}</g>
</svg>

SVG;
    }

    private static function t(float $x, float $y, float $s, string $inner): string
    {
        return sprintf('<g transform="translate(%s %s) scale(%s)">%s</g>', $x, $y, $s, $inner);
    }

    private static function solar(float $x, float $y, float $s): string
    {
        $cells = '';
        for ($r = 0; $r < 4; $r++) {
            for ($c = 0; $c < 7; $c++) {
                $px = -66 + $c * 19 + $r * 7;
                $py = -38 + $r * 17;
                $cells .= sprintf('<path d="M%s %s l17 0 l-6.5 14 l-17 0 z" fill="url(#cell)" stroke="#93c5fd" stroke-opacity=".5" stroke-width=".8"/>', $px, $py);
            }
        }

        return self::t($x, $y, $s, '<path d="M-72 -44 L84 -44 L62 34 L-84 34 Z" fill="url(#metal)"/>'
            .'<path d="M-66 -38 L78 -38 L58 28 L-76 28 Z" fill="#0f172a"/>'.$cells
            .'<path d="M-14 34 L-22 70 M40 34 L48 70" stroke="'.self::STEEL.'" stroke-width="5" stroke-linecap="round"/>'
            .'<circle cx="96" cy="-56" r="13" fill="'.self::GOLD.'"/><g stroke="'.self::GOLD.'" stroke-width="3" stroke-linecap="round"><path d="M96 -78v-6M96 -34v6M74 -56h-6M118 -56h6"/></g>');
    }

    private static function battery(float $x, float $y, float $s): string
    {
        return self::t($x, $y, $s, '<rect x="-70" y="-34" width="140" height="68" rx="9" fill="#1f2937"/>'
            .'<rect x="-70" y="-34" width="140" height="14" rx="7" fill="#374151"/>'
            .'<rect x="-58" y="-8" width="64" height="30" rx="4" fill="#0f172a" stroke="'.self::SKY.'" stroke-opacity=".6"/>'
            .'<rect x="-52" y="-2" width="40" height="5" rx="2.5" fill="'.self::SKY.'"/><rect x="-52" y="8" width="52" height="5" rx="2.5" fill="'.self::SLATE.'"/>'
            .'<g fill="'.self::GREEN.'"><rect x="22" y="-4" width="9" height="22" rx="2"/><rect x="35" y="-4" width="9" height="22" rx="2"/><rect x="48" y="-4" width="9" height="22" rx="2"/></g>'
            .'<circle cx="-46" cy="-44" r="7" fill="#dc2626"/><circle cx="46" cy="-44" r="7" fill="#111827" stroke="#475569"/>'
            .'<rect x="-52" y="-52" width="12" height="10" fill="#dc2626"/><rect x="40" y="-52" width="12" height="10" fill="#111827"/>');
    }

    private static function camera(float $x, float $y, float $s): string
    {
        return self::t($x, $y, $s, '<rect x="62" y="-38" width="14" height="76" rx="3" fill="'.self::SLATE.'"/>'
            .'<path d="M62 6 L30 6" stroke="'.self::SLATE.'" stroke-width="9" stroke-linecap="round"/><circle cx="30" cy="6" r="9" fill="#94a3b8"/>'
            .'<rect x="-64" y="-26" width="102" height="48" rx="22" fill="url(#metal)" transform="rotate(-6 -10 0)"/>'
            .'<rect x="-70" y="-30" width="64" height="9" rx="4.5" fill="#e2e8f0" transform="rotate(-6 -40 -26)"/>'
            .'<ellipse cx="-64" cy="-2" rx="15" ry="22" fill="#0f172a" transform="rotate(-6 -64 0)"/>'
            .'<ellipse cx="-66" cy="-2" rx="8" ry="13" fill="'.self::BLUE.'" transform="rotate(-6 -66 0)"/><ellipse cx="-69" cy="-7" rx="3" ry="4" fill="#fff" opacity=".7"/>'
            .'<g fill="#ef4444"><circle cx="-56" cy="-20" r="2.5"/><circle cx="-56" cy="14" r="2.5"/><circle cx="-76" cy="-20" r="2.5"/><circle cx="-76" cy="14" r="2.5"/></g>');
    }

    private static function inverter(float $x, float $y, float $s): string
    {
        $fins = '';
        for ($i = 0; $i < 6; $i++) {
            $fins .= sprintf('<rect x="%s" y="-58" width="3" height="116" fill="#94a3b8"/>', 44 + $i * 5);
        }

        return self::t($x, $y, $s, '<rect x="-46" y="-62" width="96" height="124" rx="10" fill="url(#metal)"/>'.$fins
            .'<rect x="-34" y="-50" width="72" height="50" rx="6" fill="#0f172a"/>'
            .'<path d="M-28 -18 q8 -20 16 0 t16 0 t16 0 t16 0" fill="none" stroke="'.self::GREEN.'" stroke-width="2.5"/>'
            .'<text x="-28" y="-38" font-size="9" fill="'.self::SKY.'" font-family="monospace">5.0 kW</text>'
            .'<g fill="'.self::STEEL.'" stroke="#64748b"><circle cx="-20" cy="22" r="6"/><circle cx="0" cy="22" r="6"/><circle cx="20" cy="22" r="6"/></g>'
            .'<g fill="'.self::GREEN.'"><circle cx="-24" cy="46" r="3"/></g><circle cx="-12" cy="46" r="3" fill="'.self::GOLD.'"/>'
            .'<g fill="#1f2937"><rect x="-30" y="62" width="10" height="14" rx="3"/><rect x="-10" y="62" width="10" height="14" rx="3"/><rect x="10" y="62" width="10" height="14" rx="3"/></g>');
    }

    private static function panel(float $x, float $y, float $s): string
    {
        return self::t($x, $y, $s, '<rect x="-52" y="-66" width="104" height="132" rx="7" fill="#94a3b8"/>'
            .'<rect x="-45" y="-59" width="90" height="118" rx="4" fill="url(#metal)"/>'
            .'<rect x="-34" y="-48" width="68" height="30" rx="3" fill="#0f172a"/><path d="M-28 -30 l10 -9 l10 6 l12 -8 l14 9" stroke="'.self::GREEN.'" stroke-width="2" fill="none"/>'
            .'<g><circle cx="-26" cy="-4" r="5" fill="#ef4444"/><circle cx="-8" cy="-4" r="5" fill="'.self::GREEN.'"/><circle cx="10" cy="-4" r="5" fill="'.self::GOLD.'"/><circle cx="28" cy="-4" r="5" fill="'.self::SKY.'"/></g>'
            .'<g fill="#e2e8f0" stroke="#475569"><circle cx="-18" cy="22" r="9"/><circle cx="18" cy="22" r="9"/></g><g stroke="#0f172a" stroke-width="2"><path d="M-18 22 l5 -6M18 22 l-3 -7"/></g>'
            .'<circle cx="0" cy="48" r="9" fill="#dc2626" stroke="#fde047" stroke-width="3"/>');
    }

    private static function turbine(float $x, float $y, float $s): string
    {
        $blades = '';
        foreach ([0, 120, 240] as $a) {
            $blades .= sprintf('<path d="M-3 0 Q0 -58 4 -62 Q8 -30 3 0 Z" fill="#f1f5f9" transform="rotate(%s)"/>', $a);
        }

        return self::t($x, $y, $s, '<path d="M-3 -34 L3 -34 L7 78 L-7 78 Z" fill="url(#metal)"/>'
            .'<ellipse cx="0" cy="80" rx="22" ry="5" fill="#475569"/>'
            .'<g transform="translate(0 -36)">'.$blades.'<circle r="7" fill="'.self::STEEL.'"/><circle r="3" fill="'.self::BLUE.'"/></g>');
    }

    private static function custom(): string
    {
        return '<path d="M0 -58 L50 -30 L50 28 L0 56 L-50 28 L-50 -30 Z" fill="none" stroke="'.self::SKY.'" stroke-width="3" stroke-dasharray="6 5"/>'
            .'<path d="M-50 -30 L0 -2 L50 -30 M0 -2 L0 56" stroke="'.self::SKY.'" stroke-width="2.5" fill="none" opacity=".7"/>'
            .'<circle cx="38" cy="38" r="17" fill="'.self::GOLD.'"/><path d="M38 47 V29 M30 36 l8 -8 l8 8" stroke="#0b1f3a" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
    }
}
