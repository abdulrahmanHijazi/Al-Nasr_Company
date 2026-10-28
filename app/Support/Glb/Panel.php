<?php

namespace App\Support\Glb;

use GdImage;

/**
 * Paints the printed face of a device (labels, LCD screens, scales, rating plates) into a PNG that
 * {@see GlbBuilder::plane()} wraps on the model. Everything is measured in metres from the centre of
 * the panel, y up, so the same numbers place the painted detail and the real parts in front of it.
 *
 * It draws at twice the resolution and scales down, which gives smooth edges with plain GD.
 * A second "glow" picture holds what lights up (LCD digits, LEDs); it becomes the emissive texture.
 */
class Panel
{
    private const SS = 2;

    private GdImage $base;

    private GdImage $glow;

    private bool $glowUsed = false;

    private float $ppm;

    private int $outW;

    private int $outH;

    /** @var array<string, float> */
    private static array $capRatio = [];

    public function __construct(public readonly float $width, public readonly float $height, int $maxPx = 1024, string $bg = '#ffffff')
    {
        $this->ppm = $maxPx / max($width, $height);
        $this->outW = max(8, (int) round($width * $this->ppm));
        $this->outH = max(8, (int) round($height * $this->ppm));

        $this->base = imagecreatetruecolor($this->outW * self::SS, $this->outH * self::SS);
        $this->glow = imagecreatetruecolor($this->outW * self::SS, $this->outH * self::SS);
        imagefill($this->base, 0, 0, $this->color($this->base, $bg));
        imagefill($this->glow, 0, 0, $this->color($this->glow, '#000000'));
    }

    // -- surfaces ---------------------------------------------------------------------

    /** Vertical gradient over a rectangle (default: the whole panel). */
    public function gradient(string $top, string $bottom, ?float $cx = null, ?float $cy = null, ?float $w = null, ?float $h = null): static
    {
        $x1 = $this->px(($cx ?? 0) - ($w ?? $this->width) / 2);
        $x2 = $this->px(($cx ?? 0) + ($w ?? $this->width) / 2) - 1;
        $y1 = $this->py(($cy ?? 0) + ($h ?? $this->height) / 2);
        $y2 = $this->py(($cy ?? 0) - ($h ?? $this->height) / 2) - 1;
        [$a, $b] = [self::rgb($top), self::rgb($bottom)];
        $rows = max(1, $y2 - $y1);

        for ($y = $y1; $y <= $y2; $y++) {
            $t = ($y - $y1) / $rows;
            $c = imagecolorallocate($this->base, (int) ($a[0] + ($b[0] - $a[0]) * $t), (int) ($a[1] + ($b[1] - $a[1]) * $t), (int) ($a[2] + ($b[2] - $a[2]) * $t));
            imagefilledrectangle($this->base, $x1, $y, $x2, $y, $c);
        }

        return $this;
    }

    public function rect(float $cx, float $cy, float $w, float $h, string $color, float $radius = 0.0, string|bool|null $glow = null): static
    {
        $x1 = $this->px($cx - $w / 2);
        $x2 = $this->px($cx + $w / 2) - 1;
        $y1 = $this->py($cy + $h / 2);
        $y2 = $this->py($cy - $h / 2) - 1;
        $r = (int) round($radius * $this->ppm * self::SS);

        $this->fillRound($this->base, $x1, $y1, $x2, $y2, $r, $this->color($this->base, $color));
        if ($glow !== null && $glow !== false) {
            $this->glowUsed = true;
            $this->fillRound($this->glow, $x1, $y1, $x2, $y2, $r, $this->color($this->glow, $glow === true ? $color : $glow));
        }

        return $this;
    }

    /** A filled rectangle with an outline. */
    public function frame(float $cx, float $cy, float $w, float $h, string $fill, string $border, float $thick, float $radius = 0.0): static
    {
        return $this->rect($cx, $cy, $w, $h, $border, $radius)
            ->rect($cx, $cy, $w - 2 * $thick, $h - 2 * $thick, $fill, max(0, $radius - $thick));
    }

    public function circle(float $cx, float $cy, float $r, string $color, string|bool|null $glow = null): static
    {
        $d = (int) round($r * 2 * $this->ppm * self::SS);
        imagefilledellipse($this->base, $this->px($cx), $this->py($cy), $d, $d, $this->color($this->base, $color));
        if ($glow !== null && $glow !== false) {
            $this->glowUsed = true;
            imagefilledellipse($this->glow, $this->px($cx), $this->py($cy), $d, $d, $this->color($this->glow, $glow === true ? $color : $glow));
        }

        return $this;
    }

    public function line(float $x1, float $y1, float $x2, float $y2, string $color, float $thick = 0.001, string|bool|null $glow = null): static
    {
        foreach ($this->targets($color, $glow) as [$img, $c]) {
            imagesetthickness($img, max(1, (int) round($thick * $this->ppm * self::SS)));
            imageline($img, $this->px($x1), $this->py($y1), $this->px($x2), $this->py($y2), $c);
            imagesetthickness($img, 1);
        }

        return $this;
    }

    /** Arc of a circle, angles in degrees (0 = right, counter-clockwise). */
    public function arc(float $cx, float $cy, float $r, float $from, float $to, string $color, float $thick = 0.001): static
    {
        $d = (int) round($r * 2 * $this->ppm * self::SS);
        imagesetthickness($this->base, max(1, (int) round($thick * $this->ppm * self::SS)));
        // GD measures angles clockwise
        imagearc($this->base, $this->px($cx), $this->py($cy), $d, $d, (int) round(360 - $to), (int) round(360 - $from), $this->color($this->base, $color));
        imagesetthickness($this->base, 1);

        return $this;
    }

    /** @param  array<int, array{0: float, 1: float}>  $points */
    public function polygon(array $points, string $color, string|bool|null $glow = null): static
    {
        $flat = [];
        foreach ($points as [$x, $y]) {
            array_push($flat, $this->px($x), $this->py($y));
        }
        foreach ($this->targets($color, $glow) as [$img, $c]) {
            imagefilledpolygon($img, $flat, $c);
        }

        return $this;
    }

    // -- text ---------------------------------------------------------------------------

    /**
     * @param  float  $cap  height of the capital letters in metres
     * @param  float  $track  extra space between letters in metres
     */
    public function text(string $s, float $cx, float $cy, float $cap, string $color, string $align = 'center', bool $bold = false, string|bool|null $glow = null, float $track = 0.0): static
    {
        if ($s === '') {
            return $this;
        }

        $font = self::font($bold);
        $capPx = $cap * $this->ppm * self::SS;
        $size = $capPx / self::capRatio($font);
        $trackPx = $track * $this->ppm * self::SS;

        $chars = $trackPx != 0.0 ? mb_str_split($s) : [$s];
        $advances = array_map(function (string $ch) use ($size, $font): float {
            $box = imagettfbbox($size, 0, $font, $ch);

            return (float) ($box[2] - $box[0]) + ($ch === ' ' ? $size * 0.35 : 0.0);
        }, $chars);
        $total = array_sum($advances) + $trackPx * (count($chars) - 1);

        $x = $this->px($cx) - match ($align) {
            'left' => 0.0,
            'right' => $total,
            default => $total / 2,
        };
        $y = $this->py($cy) + $capPx / 2;

        foreach ($this->targets($color, $glow) as [$img, $c]) {
            $cursor = $x;
            foreach ($chars as $i => $ch) {
                imagettftext($img, $size, 0, (int) round($cursor), (int) round($y), $c, $font, $ch);
                $cursor += $advances[$i] + $trackPx;
            }
        }

        return $this;
    }

    // -- ready-made pieces ----------------------------------------------------------------

    /** Black-and-white bars that look like a barcode label. */
    public function barcode(float $cx, float $cy, float $w, float $h, string $seed): static
    {
        $this->rect($cx, $cy, $w, $h, '#ffffff');
        $x = -$w / 2 + $w * 0.04;
        $end = $w / 2 - $w * 0.04;
        $bits = crc32($seed);
        $unit = $w / 130;
        $i = 0;

        while ($x < $end) {
            $bar = (($bits >> ($i % 28)) & 3) + 1;
            $width = $bar * $unit;
            if ($i % 2 === 0 && $x + $width < $end) {
                $this->rect($cx + $x + $width / 2, $cy, $width, $h * 0.82, '#111111');
            }
            $x += $width;
            $i++;
            $bits = ($bits * 1103515245 + 12345) & 0x7fffffff;
        }

        return $this;
    }

    /** A sun symbol (PV). */
    public function sun(float $cx, float $cy, float $r, string $color, string|bool|null $glow = null): static
    {
        $this->circle($cx, $cy, $r * 0.45, $color, $glow);
        for ($a = 0; $a < 360; $a += 45) {
            $rad = deg2rad($a);
            $this->line($cx + cos($rad) * $r * 0.65, $cy + sin($rad) * $r * 0.65, $cx + cos($rad) * $r, $cy + sin($rad) * $r, $color, $r * 0.14, $glow);
        }

        return $this;
    }

    /** A battery symbol with $bars of 4 filled. */
    public function battery(float $cx, float $cy, float $w, float $h, int $bars, string $color, string|bool|null $glow = null): static
    {
        $t = $h * 0.12;
        $this->rect($cx, $cy, $w, $h, $color, 0.0, $glow)
            ->rect($cx, $cy, $w - 2 * $t, $h - 2 * $t, '#000000', 0.0, '#000000')
            ->rect($cx + $w / 2 + $t, $cy, $t * 1.6, $h * 0.4, $color, 0.0, $glow);
        $cell = ($w - 4 * $t) / 4;
        for ($i = 0; $i < $bars; $i++) {
            $this->rect($cx - $w / 2 + 2 * $t + $cell * ($i + 0.5), $cy, $cell * 0.8, $h - 4 * $t, $color, 0.0, $glow);
        }

        return $this;
    }

    /** A lightning bolt. */
    public function bolt(float $cx, float $cy, float $h, string $color, string|bool|null $glow = null): static
    {
        $s = $h / 2;

        return $this->polygon([
            [$cx + $s * 0.15, $cy + $s], [$cx - $s * 0.55, $cy - $s * 0.1], [$cx - $s * 0.05, $cy - $s * 0.1],
            [$cx - $s * 0.2, $cy - $s], [$cx + $s * 0.55, $cy + $s * 0.15], [$cx + $s * 0.05, $cy + $s * 0.15],
        ], $color, $glow);
    }

    /** Small round dial with a scale and a pointer, as on a thermal-magnetic breaker. */
    public function dial(float $cx, float $cy, float $r, float $pointerDeg, string $face, string $ink): static
    {
        $this->circle($cx, $cy, $r, '#0b0d10')->circle($cx, $cy, $r * 0.9, $face);
        for ($i = 0; $i <= 8; $i++) {
            $a = deg2rad(225 - $i * 33.75);
            $this->line($cx + cos($a) * $r * 0.62, $cy + sin($a) * $r * 0.62, $cx + cos($a) * $r * 0.84, $cy + sin($a) * $r * 0.84, $ink, $r * 0.045);
        }
        $a = deg2rad($pointerDeg);
        $this->line($cx - cos($a) * $r * 0.2, $cy - sin($a) * $r * 0.2, $cx + cos($a) * $r * 0.55, $cy + sin($a) * $r * 0.55, $ink, $r * 0.09)
            ->circle($cx, $cy, $r * 0.12, $ink);

        return $this;
    }

    /** Rows of dark slots, e.g. a ventilation grille. */
    public function slots(float $cx, float $cy, float $w, float $h, int $count, string $color, bool $horizontal = true): static
    {
        for ($i = 0; $i < $count; $i++) {
            $t = $count > 1 ? $i / ($count - 1) : 0.5;
            if ($horizontal) {
                $this->rect($cx, $cy + $h / 2 - $t * $h, $w, $h / ($count * 2.2), $color, $h / ($count * 4.4));
            } else {
                $this->rect($cx - $w / 2 + $t * $w, $cy, $w / ($count * 2.2), $h, $color, $w / ($count * 4.4));
            }
        }

        return $this;
    }

    /** Blend two colours: 0 = $a, 1 = $b. */
    public static function mix(string $a, string $b, float $t): string
    {
        [$x, $y] = [self::rgb($a), self::rgb($b)];

        return sprintf('#%02x%02x%02x', (int) ($x[0] + ($y[0] - $x[0]) * $t), (int) ($x[1] + ($y[1] - $x[1]) * $t), (int) ($x[2] + ($y[2] - $x[2]) * $t));
    }

    // -- output -------------------------------------------------------------------------------

    /** @return array{map: string, glow: string|null} PNG bytes */
    public function png(): array
    {
        return [
            'map' => $this->encode($this->base),
            'glow' => $this->glowUsed ? $this->encode($this->glow) : null,
        ];
    }

    // -- internals ------------------------------------------------------------------------------

    private function encode(GdImage $source): string
    {
        $out = imagecreatetruecolor($this->outW, $this->outH);
        imagecopyresampled($out, $source, 0, 0, 0, 0, $this->outW, $this->outH, $this->outW * self::SS, $this->outH * self::SS);

        ob_start();
        imagepng($out, null, 9);
        $png = (string) ob_get_clean();

        return $png;
    }

    private function px(float $x): int
    {
        return (int) round(($x + $this->width / 2) * $this->ppm * self::SS);
    }

    private function py(float $y): int
    {
        return (int) round(($this->height / 2 - $y) * $this->ppm * self::SS);
    }

    /** @return array<int, array{0: GdImage, 1: int}> */
    private function targets(string $color, string|bool|null $glow): array
    {
        $list = [[$this->base, $this->color($this->base, $color)]];
        if ($glow !== null && $glow !== false) {
            $this->glowUsed = true;
            $list[] = [$this->glow, $this->color($this->glow, $glow === true ? $color : $glow)];
        }

        return $list;
    }

    private function fillRound(GdImage $img, int $x1, int $y1, int $x2, int $y2, int $r, int $c): void
    {
        $r = min($r, intdiv(max(0, min($x2 - $x1, $y2 - $y1)), 2));

        if ($r <= 0) {
            imagefilledrectangle($img, $x1, $y1, $x2, $y2, $c);

            return;
        }

        imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $c);
        imagefilledrectangle($img, $x1, $y1 + $r, $x2, $y2 - $r, $c);
        foreach ([[$x1 + $r, $y1 + $r], [$x2 - $r, $y1 + $r], [$x1 + $r, $y2 - $r], [$x2 - $r, $y2 - $r]] as [$x, $y]) {
            imagefilledellipse($img, $x, $y, $r * 2, $r * 2, $c);
        }
    }

    private function color(GdImage $img, string $hex): int
    {
        [$r, $g, $b] = self::rgb($hex);

        return imagecolorallocate($img, $r, $g, $b);
    }

    /** @return array{0: int, 1: int, 2: int} */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private static function font(bool $bold): string
    {
        return resource_path('fonts/Cairo-'.($bold ? 'Bold' : 'Regular').'.ttf');
    }

    /** Cap height of the font per point size, measured once (GD sizes text in points, not pixels). */
    private static function capRatio(string $font): float
    {
        return self::$capRatio[$font] ??= (function () use ($font): float {
            $box = imagettfbbox(100, 0, $font, 'H');

            return abs($box[7] - $box[1]) / 100;
        })();
    }
}
