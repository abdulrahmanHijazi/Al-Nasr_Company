<?php

namespace App\Support;

use App\Enums\HomeSectionType;

/**
 * Wireframe thumbnails for every home-section design, shown in the admin's
 * design picker. Generated (not hand-drawn) so adding a design is one `match` arm:
 *
 *   php artisan design:previews
 */
class DesignPreview
{
    private const W = 320;

    private const H = 200;

    private const BG = '#f1f5f9';

    private const WHITE = '#ffffff';

    private const LINE = '#e2e8f0';

    private const INK = '#334155';

    private const MUTED = '#cbd5e1';

    private const PRIMARY = '#1d4ed8';

    private const ACCENT = '#d99a1f';

    private const DARK = '#0b1f3a';

    /** Public path (relative to /public) of the preview image for a type + design. */
    public static function path(HomeSectionType|string $type, string $design): string
    {
        $type = $type instanceof HomeSectionType ? $type->value : $type;

        return "images/designs/{$type}-{$design}.svg";
    }

    public static function url(HomeSectionType|string $type, string $design): string
    {
        return asset(self::path($type, $design));
    }

    public static function svg(HomeSectionType $type, string $design): string
    {
        $body = match ($type) {
            HomeSectionType::Hero => self::hero($design),
            HomeSectionType::Slider => self::slider($design),
            HomeSectionType::Features => self::features($design),
            HomeSectionType::Categories => self::categories($design),
            HomeSectionType::Products => self::products($design),
            HomeSectionType::Services => self::services($design),
            HomeSectionType::Process => self::process($design),
            HomeSectionType::Bundles => self::bundles($design),
            HomeSectionType::Banners => self::banners($design),
            HomeSectionType::Brands => self::brands($design),
            HomeSectionType::Portfolio => self::portfolio($design),
            HomeSectionType::Counters => self::counters($design),
            HomeSectionType::Testimonials => self::testimonials($design),
            HomeSectionType::Cta => self::cta($design),
        };

        $w = self::W;
        $h = self::H;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$w} {$h}" width="{$w}" height="{$h}" role="img">
<rect width="{$w}" height="{$h}" fill="#f8fafc"/>
{$body}
</svg>

SVG;
    }

    // -- drawing primitives -------------------------------------------------------

    private static function rect(float $x, float $y, float $w, float $h, string $fill, float $rx = 6, string $extra = ''): string
    {
        return sprintf('<rect x="%s" y="%s" width="%s" height="%s" rx="%s" fill="%s" %s/>', $x, $y, $w, $h, $rx, $fill, $extra);
    }

    private static function bar(float $x, float $y, float $w, string $fill = self::MUTED, float $h = 5): string
    {
        return self::rect($x, $y, $w, $h, $fill, $h / 2);
    }

    private static function circle(float $cx, float $cy, float $r, string $fill): string
    {
        return sprintf('<circle cx="%s" cy="%s" r="%s" fill="%s"/>', $cx, $cy, $r, $fill);
    }

    /** A product / image placeholder: tinted block with a little mountain glyph. */
    private static function image(float $x, float $y, float $w, float $h, string $tint = '#dbeafe', float $rx = 6): string
    {
        $mx = $x + $w * 0.5;
        $my = $y + $h * 0.62;
        $s = min($w, $h) * 0.22;

        return self::rect($x, $y, $w, $h, $tint, $rx)
            .sprintf('<path d="M%s %s L%s %s L%s %s Z" fill="#ffffff" opacity=".7"/>', $mx - $s * 1.4, $my + $s, $mx - $s * 0.2, $my - $s, $mx + $s * 1.2, $my + $s)
            .self::circle($x + $w * 0.28, $y + $h * 0.3, min($w, $h) * 0.07, '#ffffff');
    }

    private static function card(float $x, float $y, float $w, float $h): string
    {
        return self::rect($x, $y, $w, $h, self::WHITE, 8, 'stroke="'.self::LINE.'"');
    }

    private static function heading(float $x, float $y, float $w = 90, string $fill = self::INK): string
    {
        return self::bar($x, $y, $w, $fill, 7);
    }

    private static function button(float $x, float $y, float $w, string $fill = self::ACCENT, bool $outline = false): string
    {
        return $outline
            ? self::rect($x, $y, $w, 14, 'none', 7, 'stroke="#ffffff" stroke-opacity=".5"')
            : self::rect($x, $y, $w, 14, $fill, 7);
    }

    private static function dark(float $y, float $h, float $x = 0, float $w = self::W, float $rx = 0): string
    {
        return self::rect($x, $y, $w, $h, self::DARK, $rx);
    }

    // -- designs --------------------------------------------------------------------

    private static function slider(string $design): string
    {
        $arrows = self::circle(20, 100, 9, '#ffffff44').self::circle(300, 100, 9, '#ffffff44');
        $dots = self::rect(140, 176, 18, 5, self::ACCENT, 3).self::circle(170, 178.5, 2.5, '#ffffff88').self::circle(182, 178.5, 2.5, '#ffffff88').self::circle(194, 178.5, 2.5, '#ffffff88');

        return match ($design) {
            'product' => self::dark(0, 200)
                .self::bar(40, 62, 56, self::ACCENT, 8).self::bar(40, 80, 130, '#ffffff', 11).self::bar(40, 98, 100, '#ffffff', 11)
                .self::bar(40, 120, 110, '#94a3b8').self::button(40, 140, 62)
                .self::rect(200, 50, 78, 104, '#e2e8f0', 12).self::rect(212, 62, 54, 22, '#0b1f3a', 6).self::circle(239, 110, 14, '#0b1f3a')
                .$arrows.$dots,
            'cards' => self::rect(0, 0, 320, 200, self::BG)
                .self::image(20, 24, 280, 152, '#93c5fd', 18).self::rect(20, 24, 280, 152, self::DARK, 18, 'opacity=".55"')
                .self::bar(44, 70, 54, self::ACCENT, 8).self::bar(44, 88, 140, '#ffffff', 11).self::bar(44, 106, 110, '#ffffff', 11).self::button(44, 130, 62)
                .self::circle(36, 100, 8, '#ffffff44').self::circle(284, 100, 8, '#ffffff44')
                .self::rect(142, 158, 16, 5, self::ACCENT, 3).self::circle(170, 160.5, 2.5, '#ffffff88').self::circle(182, 160.5, 2.5, '#ffffff88'),
            default => self::image(0, 0, 320, 200, '#93c5fd', 0).self::rect(0, 0, 320, 200, self::DARK, 0, 'opacity=".6"')
                .self::bar(40, 64, 56, self::ACCENT, 8).self::bar(40, 82, 160, '#ffffff', 12).self::bar(40, 101, 120, '#ffffff', 12)
                .self::bar(40, 124, 130, '#cbd5e1').self::button(40, 144, 66)
                .$arrows.$dots,
        };
    }

    private static function hero(string $design): string
    {
        $o = self::dark(0, 200);

        return $o.match ($design) {
            'slider' => self::rect(0, 0, 320, 200, '#1e3a8a', 0, 'opacity=".55"')
                .self::bar(40, 64, 60, self::ACCENT, 8)
                .self::bar(40, 82, 150, '#ffffff', 11)
                .self::bar(40, 100, 120, '#ffffff', 11)
                .self::bar(40, 122, 110, '#94a3b8')
                .self::button(40, 142, 62)
                .self::circle(16, 100, 9, '#ffffff33').self::circle(304, 100, 9, '#ffffff33')
                .self::rect(140, 182, 16, 5, self::ACCENT, 3).self::circle(168, 184.5, 2.5, '#ffffff88').self::circle(180, 184.5, 2.5, '#ffffff88'),
            'centered' => self::bar(125, 26, 70, '#ffffff22', 12)
                .self::bar(60, 52, 200, '#ffffff', 12)
                .self::bar(90, 72, 140, '#ffffff', 12)
                .self::bar(80, 98, 160, '#94a3b8')
                .self::button(100, 118, 56).self::button(164, 118, 56, outline: true)
                .self::bar(60, 160, 40, self::ACCENT, 9).self::bar(140, 160, 40, self::ACCENT, 9).self::bar(220, 160, 40, self::ACCENT, 9)
                .self::bar(62, 176, 36, '#64748b', 4).self::bar(142, 176, 36, '#64748b', 4).self::bar(222, 176, 36, '#64748b', 4),
            default => self::bar(24, 32, 56, '#ffffff22', 12)
                .self::bar(24, 60, 140, '#ffffff', 11)
                .self::bar(24, 78, 110, '#ffffff', 11)
                .self::bar(24, 102, 130, '#94a3b8')
                .self::bar(24, 114, 100, '#94a3b8')
                .self::button(24, 138, 58).self::button(90, 138, 58, outline: true)
                .self::rect(206, 22, 92, 70, self::WHITE, 8, 'transform="rotate(4 252 57)"')
                .self::image(212, 28, 80, 40, '#bfdbfe')
                .self::rect(32 + 170, 100, 92, 70, self::WHITE, 8, 'transform="rotate(-4 250 135)"')
                .self::image(208, 106, 80, 40, '#fde68a')
                .self::bar(214, 152, 50, self::MUTED).self::bar(214, 162, 30, self::ACCENT),
        };
    }

    private static function features(string $design): string
    {
        $out = '';

        if ($design === 'cards') {
            for ($i = 0; $i < 4; $i++) {
                $x = 14 + $i * 76;
                $out .= self::card($x, 50, 68, 100)
                    .self::rect($x + 22, 62, 24, 24, self::PRIMARY, 8)
                    .self::bar($x + 14, 98, 40, self::INK, 6)
                    .self::bar($x + 10, 114, 48).self::bar($x + 16, 124, 36);
            }

            return $out;
        }

        $out .= self::rect(0, 62, 320, 76, self::WHITE, 0, 'stroke="'.self::LINE.'"');
        for ($i = 0; $i < 4; $i++) {
            $x = 16 + $i * 76;
            $out .= self::rect($x, 78, 22, 22, '#dbeafe', 7)
                .self::bar($x + 28, 80, 38, self::INK, 6)
                .self::bar($x + 28, 94, 34).self::bar($x, 112, 60).self::bar($x, 122, 44);
        }

        return $out;
    }

    private static function categories(string $design): string
    {
        $out = self::heading(16, 14, 80).self::bar(260, 15, 44, self::PRIMARY);

        if ($design === 'pills') {
            for ($i = 0; $i < 8; $i++) {
                $x = 16 + ($i % 2) * 150;
                $y = 40 + intdiv($i, 2) * 38;
                $out .= self::card($x, $y, 142, 32)
                    .self::image($x + 5, $y + 5, 22, 22, ['#bfdbfe', '#fde68a', '#bbf7d0', '#fbcfe8'][$i % 4], 6)
                    .self::bar($x + 34, $y + 9, 60, self::INK, 5).self::bar($x + 34, $y + 19, 36);
            }

            return $out;
        }

        for ($i = 0; $i < 8; $i++) {
            $x = 16 + ($i % 4) * 75;
            $y = 40 + intdiv($i, 4) * 76;
            $out .= self::image($x, $y, 69, 68, ['#93c5fd', '#fcd34d', '#86efac', '#f9a8d4'][$i % 4], 8)
                .self::rect($x, $y + 42, 69, 26, self::DARK, 8, 'opacity=".72"')
                .self::bar($x + 6, $y + 52, 38, '#ffffff', 5);
        }

        return $out;
    }

    private static function productCard(float $x, float $y, float $w = 68): string
    {
        return self::card($x, $y, $w, 112)
            .self::image($x + 5, $y + 5, $w - 10, 56, '#dbeafe', 6)
            .self::bar($x + 7, $y + 70, $w - 24, self::PRIMARY, 4)
            .self::bar($x + 7, $y + 80, $w - 14, self::INK, 5)
            .self::bar($x + 7, $y + 96, 30, self::INK, 6);
    }

    private static function products(string $design): string
    {
        $out = self::heading(16, 14, 96).self::bar(260, 15, 44, self::PRIMARY);

        if ($design === 'carousel') {
            for ($i = 0; $i < 4; $i++) {
                $out .= self::productCard(16 + $i * 78, 44, 72);
            }
            $out .= self::rect(0, 44, 320, 112, '#f8fafc', 0, 'opacity="0"')
                .self::circle(292, 178, 10, self::WHITE).self::circle(266, 178, 10, self::WHITE)
                .'<path d="M262 178 l4 -4 v8z M296 178 l-4 -4 v8z" fill="#475569"/>';

            return $out;
        }

        for ($i = 0; $i < 4; $i++) {
            $out .= self::productCard(16 + $i * 76, 42, 68);
        }
        for ($i = 0; $i < 4; $i++) {
            $out .= self::card(16 + $i * 76, 160, 68, 28).self::bar(24 + $i * 76, 170, 40, self::MUTED);
        }

        return $out;
    }

    private static function services(string $design): string
    {
        $out = self::bar(16, 14, 56, self::ACCENT, 4).self::heading(16, 26, 120);

        if ($design === 'list') {
            for ($i = 0; $i < 4; $i++) {
                $y = 50 + $i * 36;
                $out .= self::card(16, $y, 288, 30)
                    .self::bar(26, $y + 11, 18, self::ACCENT, 8)
                    .self::rect(54, $y + 5, 20, 20, '#dbeafe', 6)
                    .self::bar(84, $y + 8, 80, self::INK, 6).self::bar(84, $y + 19, 150)
                    .'<path d="M286 '.($y + 15).' h8 m-3 -3 l3 3 l-3 3" stroke="#94a3b8" fill="none" stroke-width="1.5"/>';
            }

            return $out;
        }

        for ($i = 0; $i < 4; $i++) {
            $x = 14 + $i * 76;
            $out .= self::card($x, 52, 68, 120)
                .self::rect($x + 8, 62, 24, 24, '#dbeafe', 8)
                .self::bar($x + 8, 96, 46, self::INK, 6)
                .self::bar($x + 8, 110, 52).self::bar($x + 8, 120, 44).self::bar($x + 8, 130, 50)
                .self::bar($x + 8, 152, 34, self::PRIMARY, 5);
        }

        return $out;
    }

    private static function process(string $design): string
    {
        $out = '';

        if ($design === 'timeline') {
            $out .= '<line x1="110" y1="30" x2="110" y2="170" stroke="'.self::LINE.'" stroke-width="3"/>';
            for ($i = 0; $i < 4; $i++) {
                $y = 40 + $i * 40;
                $out .= self::circle(110, $y, 9, self::PRIMARY)
                    .self::bar(106, $y - 2, 8, '#ffffff', 4)
                    .self::bar(132, $y - 9, 70, self::INK, 6).self::bar(132, $y + 3, 110);
            }

            return $out;
        }

        for ($i = 0; $i < 4; $i++) {
            $x = 14 + $i * 76;
            $out .= self::rect($x, 56, 68, 88, self::WHITE, 8, 'stroke="#94a3b8" stroke-dasharray="4 3"')
                .self::circle($x + 16, 76, 9, self::ACCENT)
                .self::bar($x + 8, 96, 46, self::INK, 6)
                .self::bar($x + 8, 110, 52).self::bar($x + 8, 120, 40);
        }

        return $out;
    }

    private static function bundles(string $design): string
    {
        $dark = $design === 'band';
        $out = $dark ? self::dark(0, 200) : '';
        $out .= self::bar(16, 16, 50, self::ACCENT, 4).self::heading(16, 28, 120, $dark ? '#ffffff' : self::INK);

        for ($i = 0; $i < 3; $i++) {
            $x = 14 + $i * 100;
            $out .= self::card($x, 54, 92, 124)
                .self::image($x + 5, 59, 82, 50, '#fde68a', 6)
                .self::rect($x + 9, 63, 36, 12, self::ACCENT, 6)
                .self::bar($x + 8, 118, 60, self::INK, 6)
                .self::bar($x + 8, 130, 74).self::bar($x + 8, 140, 56)
                .self::bar($x + 8, 156, 36, self::PRIMARY, 8).self::rect($x + 50, 156, 34, 8, '#dcfce7', 4);
        }

        return $out;
    }

    private static function banners(string $design): string
    {
        $tints = ['#93c5fd', '#fcd34d', '#86efac'];
        $cols = match ($design) {
            'wide' => 1,
            'triple' => 3,
            default => 2,
        };
        $gap = 10;
        $w = (288 - $gap * ($cols - 1)) / $cols;
        $h = $cols === 1 ? 110 : ($cols === 2 ? 120 : 130);
        $out = '';

        for ($i = 0; $i < $cols; $i++) {
            $x = 16 + $i * ($w + $gap);
            $out .= self::image($x, 40, $w, $h, $tints[$i % 3], 10)
                .self::rect($x, 40 + $h - 38, $w, 38, self::DARK, 10, 'opacity=".7"')
                .self::bar($x + 10, 40 + $h - 28, min(70, $w - 60), '#ffffff', 6)
                .self::bar($x + 10, 40 + $h - 16, min(100, $w - 50), '#94a3b8', 4)
                .self::rect($x + $w - 40, 40 + $h - 28, 30, 14, self::ACCENT, 7);
        }

        return $out;
    }

    private static function brands(string $design): string
    {
        $out = self::bar(16, 14, 50, self::ACCENT, 4).self::heading(16, 26, 110);

        if ($design === 'marquee') {
            $out .= self::rect(0, 70, 320, 60, '#eef2f7', 0);
            for ($i = 0; $i < 5; $i++) {
                $x = -14 + $i * 70;
                $out .= self::card($x, 82, 62, 36).self::bar($x + 12, 97, 38, self::MUTED, 7);
            }
            $out .= '<defs><linearGradient id="fadeL"><stop offset="0" stop-color="#f8fafc"/><stop offset="1" stop-color="#f8fafc" stop-opacity="0"/></linearGradient>'
                .'<linearGradient id="fadeR"><stop offset="0" stop-color="#f8fafc" stop-opacity="0"/><stop offset="1" stop-color="#f8fafc"/></linearGradient></defs>'
                .'<rect x="0" y="70" width="46" height="60" fill="url(#fadeL)"/><rect x="274" y="70" width="46" height="60" fill="url(#fadeR)"/>'
                .'<path d="M150 150 h20 m-6 -5 l6 5 l-6 5" stroke="#94a3b8" fill="none" stroke-width="2"/>';

            return $out;
        }

        for ($i = 0; $i < 6; $i++) {
            $x = 16 + ($i % 3) * 98;
            $y = 56 + intdiv($i, 3) * 60;
            $out .= self::card($x, $y, 92, 50).self::bar($x + 20, $y + 20, 52, self::MUTED, 9);
        }

        return $out;
    }

    private static function portfolio(string $design): string
    {
        $out = self::bar(16, 14, 50, self::ACCENT, 4).self::heading(16, 26, 110);

        if ($design === 'featured') {
            $out .= self::card(16, 48, 176, 136)
                .self::image(21, 53, 166, 86, '#bfdbfe', 6)
                .self::bar(28, 150, 90, self::INK, 6).self::bar(28, 163, 130);
            for ($i = 0; $i < 2; $i++) {
                $y = 48 + $i * 71;
                $out .= self::card(200, $y, 104, 65)
                    .self::image(205, $y + 5, 94, 32, ['#fde68a', '#bbf7d0'][$i], 6)
                    .self::bar(210, $y + 44, 60, self::INK, 5).self::bar(210, $y + 54, 80);
            }

            return $out;
        }

        for ($i = 0; $i < 3; $i++) {
            $x = 16 + $i * 98;
            $out .= self::card($x, 48, 92, 136)
                .self::image($x + 5, 53, 82, 70, ['#bfdbfe', '#fde68a', '#bbf7d0'][$i], 6)
                .self::bar($x + 10, 134, 50, self::PRIMARY, 4)
                .self::bar($x + 10, 146, 70, self::INK, 6).self::bar($x + 10, 160, 62);
        }

        return $out;
    }

    private static function counters(string $design): string
    {
        if ($design === 'cards') {
            $out = '';
            for ($i = 0; $i < 4; $i++) {
                $x = 14 + $i * 76;
                $out .= self::card($x, 64, 68, 72)
                    .self::bar($x + 14, 82, 40, self::PRIMARY, 12)
                    .self::bar($x + 12, 108, 44).self::bar($x + 18, 118, 32);
            }

            return $out;
        }

        $out = self::rect(0, 54, 320, 92, self::PRIMARY, 0);
        for ($i = 0; $i < 4; $i++) {
            $x = 20 + $i * 76;
            $out .= self::bar($x, 78, 44, '#ffffff', 14).self::bar($x + 2, 104, 40, '#bfdbfe', 5).self::bar($x + 8, 114, 28, '#93c5fd', 4);
        }

        return $out;
    }

    private static function testimonials(string $design): string
    {
        $out = self::heading(16, 14, 110);

        if ($design === 'quote') {
            return $out
                .'<text x="160" y="84" font-size="52" text-anchor="middle" fill="'.self::ACCENT.'" font-family="Georgia,serif">“</text>'
                .self::bar(50, 96, 220, self::INK, 7).self::bar(70, 112, 180, self::INK, 7)
                .self::circle(160, 140, 12, '#dbeafe')
                .self::bar(134, 160, 52, self::INK, 5).self::bar(142, 170, 36)
                .self::rect(138, 186, 16, 4, self::PRIMARY, 2).self::circle(164, 188, 2, self::MUTED).self::circle(174, 188, 2, self::MUTED);
        }

        for ($i = 0; $i < 3; $i++) {
            $x = 16 + $i * 98;
            $out .= self::card($x, 44, 92, 124)
                .'<text x="'.($x + 12).'" y="78" font-size="30" fill="'.self::ACCENT.'" font-family="Georgia,serif">“</text>'
                .self::bar($x + 10, 88, 72).self::bar($x + 10, 99, 66).self::bar($x + 10, 110, 70).self::bar($x + 10, 121, 40)
                .self::circle($x + 20, 146, 9, '#dbeafe')
                .self::bar($x + 34, 140, 40, self::INK, 5).self::bar($x + 34, 151, 30);
        }

        return $out;
    }

    private static function cta(string $design): string
    {
        return match ($design) {
            'split' => self::dark(46, 108, 16, 288, 14)
                .self::rect(204, 46, 100, 108, '#ffffff14', 14)
                .self::bar(34, 74, 130, '#ffffff', 9).self::bar(34, 92, 100, '#ffffff', 9)
                .self::bar(34, 114, 140, '#94a3b8')
                .self::rect(214, 68, 80, 16, self::ACCENT, 8).self::rect(214, 92, 80, 16, 'none', 8, 'stroke="#ffffff" stroke-opacity=".5"').self::rect(214, 116, 80, 16, 'none', 8, 'stroke="#ffffff" stroke-opacity=".5"'),
            'minimal' => '<line x1="60" y1="52" x2="260" y2="52" stroke="'.self::MUTED.'" stroke-width="1.5"/>'
                .'<line x1="60" y1="150" x2="260" y2="150" stroke="'.self::MUTED.'" stroke-width="1.5"/>'
                .self::bar(90, 72, 140, self::INK, 9)
                .self::bar(110, 92, 100)
                .self::rect(100, 110, 56, 16, self::PRIMARY, 8).self::rect(164, 110, 56, 16, 'none', 8, 'stroke="#94a3b8"'),
            default => self::rect(16, 46, 288, 108, self::PRIMARY, 16)
                .self::bar(36, 76, 130, '#ffffff', 9).self::bar(36, 94, 100, '#ffffff', 9)
                .self::bar(36, 116, 140, '#93c5fd')
                .self::rect(204, 78, 84, 16, self::ACCENT, 8).self::rect(204, 102, 84, 16, 'none', 8, 'stroke="#ffffff" stroke-opacity=".6"'),
        };
    }
}
