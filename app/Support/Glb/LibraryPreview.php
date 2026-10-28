<?php

namespace App\Support\Glb;

use App\Enums\ThreeDModelType;

/** The picture shown for a library model in the pickers (generated on import, no image tools needed). */
class LibraryPreview
{
    public static function svg(ThreeDModelType $type, string $color, ?string $brand = null): string
    {
        $art = match ($type) {
            ThreeDModelType::Inverter => self::inverter($color),
            ThreeDModelType::Battery => self::battery($color),
            ThreeDModelType::ChargeController => self::controller($color),
            ThreeDModelType::Breaker => self::breaker($color),
            ThreeDModelType::Other => self::other($color),
        };

        $label = $brand ? '<text x="160" y="188" text-anchor="middle" font-family="Cairo, Arial, sans-serif" font-size="13" font-weight="700" fill="#cbd5e1" letter-spacing="1.5">'.htmlspecialchars(strtoupper($brand), ENT_XML1).'</text>' : '';

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 200" width="320" height="200" role="img">
<defs>
<radialGradient id="g" cx="50%" cy="44%" r="62%"><stop offset="0" stop-color="#1e3a8a"/><stop offset="1" stop-color="#0b1f3a"/></radialGradient>
<linearGradient id="m" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f1f5f9"/><stop offset="1" stop-color="#94a3b8"/></linearGradient>
</defs>
<rect width="320" height="200" fill="url(#g)"/>
<ellipse cx="160" cy="166" rx="86" ry="8" fill="#000" opacity=".28"/>
<g transform="translate(160 88)">{$art}</g>
{$label}
</svg>

SVG;
    }

    private static function inverter(string $c): string
    {
        return '<rect x="-46" y="-62" width="92" height="124" rx="10" fill="url(#m)"/>'
            .'<rect x="-46" y="-62" width="92" height="10" rx="5" fill="'.$c.'"/>'
            .'<rect x="-36" y="-44" width="72" height="62" rx="5" fill="#0f172a"/>'
            .'<rect x="-28" y="-38" width="56" height="20" rx="3" fill="'.$c.'" opacity=".85"/>'
            .'<g fill="#22c55e"><circle cx="-14" cy="-6" r="3"/></g><circle cx="0" cy="-6" r="3" fill="#f59e0b"/><circle cx="14" cy="-6" r="3" fill="#ef4444"/>'
            .'<g fill="#cbd5e1"><circle cx="-22" cy="8" r="4.5"/><circle cx="-7" cy="8" r="4.5"/><circle cx="8" cy="8" r="4.5"/><circle cx="23" cy="8" r="4.5"/></g>'
            .'<g fill="#1f2937"><rect x="-32" y="62" width="10" height="13" rx="3"/><rect x="-14" y="62" width="10" height="13" rx="3"/><rect x="4" y="62" width="10" height="13" rx="3"/><rect x="22" y="62" width="10" height="13" rx="3"/></g>';
    }

    private static function battery(string $c): string
    {
        return '<rect x="-88" y="-34" width="176" height="68" rx="8" fill="#2b2f36"/>'
            .'<rect x="-80" y="-26" width="160" height="52" rx="5" fill="#1b1e23"/>'
            .'<rect x="-100" y="-34" width="12" height="68" rx="3" fill="#94a3b8"/><rect x="88" y="-34" width="12" height="68" rx="3" fill="#94a3b8"/>'
            .'<rect x="-70" y="-14" width="16" height="28" rx="4" fill="'.$c.'"/><rect x="54" y="-14" width="16" height="28" rx="4" fill="'.$c.'"/>'
            .'<rect x="-40" y="-14" width="52" height="26" rx="3" fill="#0f172a"/><rect x="-34" y="-8" width="40" height="8" rx="2" fill="#38bdf8"/>'
            .'<g fill="#22c55e"><circle cx="22" cy="-6" r="3.5"/><circle cx="32" cy="-6" r="3.5"/><circle cx="42" cy="-6" r="3.5"/></g>'
            .'<circle cx="24" cy="12" r="5" fill="#dc2626"/><circle cx="40" cy="12" r="5" fill="#111827"/>';
    }

    private static function controller(string $c): string
    {
        $fins = '';
        for ($i = 0; $i < 7; $i++) {
            $fins .= '<rect x="46" y="'.(-52 + $i * 15).'" width="22" height="7" rx="2" fill="#94a3b8"/>';
        }

        return '<rect x="-40" y="-60" width="86" height="120" rx="9" fill="'.$c.'"/>'.$fins
            .'<rect x="-30" y="-50" width="66" height="66" rx="5" fill="#0f172a"/>'
            .'<rect x="-24" y="-44" width="54" height="24" rx="3" fill="#38bdf8" opacity=".85"/>'
            .'<g fill="#cbd5e1"><circle cx="-16" cy="-4" r="4"/><circle cx="-4" cy="-4" r="4"/><circle cx="8" cy="-4" r="4"/><circle cx="20" cy="-4" r="4"/></g>'
            .'<rect x="-34" y="60" width="74" height="12" rx="3" fill="#1f2937"/>'
            .'<g fill="#cbd5e1"><circle cx="-20" cy="66" r="3"/><circle cx="-6" cy="66" r="3"/><circle cx="8" cy="66" r="3"/><circle cx="22" cy="66" r="3"/></g>';
    }

    private static function breaker(string $c): string
    {
        $poles = '';
        foreach ([0, 1, 2] as $i) {
            $x = -57 + $i * 38;
            $poles .= '<rect x="'.$x.'" y="-52" width="36" height="104" rx="4" fill="#eef0f3"/>'
                .'<rect x="'.($x + 9).'" y="-40" width="18" height="40" rx="3" fill="#14171c"/>'
                .'<rect x="'.($x + 6).'" y="18" width="24" height="14" rx="2" fill="#fff" stroke="#cbd5e1"/>'
                .'<rect x="'.($x + 8).'" y="-60" width="20" height="8" rx="2" fill="#94a3b8"/><rect x="'.($x + 8).'" y="52" width="20" height="8" rx="2" fill="#94a3b8"/>';
        }

        return '<rect x="-72" y="-8" width="144" height="14" rx="2" fill="#b6bec8" opacity=".55"/>'.$poles
            .'<rect x="-57" y="-56" width="112" height="6" rx="3" fill="'.$c.'"/>';
    }

    private static function other(string $c): string
    {
        return '<path d="M0 -58 L50 -30 L50 28 L0 56 L-50 28 L-50 -30 Z" fill="none" stroke="'.$c.'" stroke-width="3" stroke-dasharray="6 5"/>'
            .'<path d="M-50 -30 L0 -2 L50 -30 M0 -2 L0 56" stroke="'.$c.'" stroke-width="2.5" fill="none" opacity=".7"/>';
    }
}
