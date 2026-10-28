<?php

namespace App\Support;

use RuntimeException;

/**
 * Generates tidy gradient + geometric placeholder images with GD so demo data
 * has real files to attach (no network, no stock photos, no fonts needed).
 */
class PlaceholderImage
{
    /** [from, to] colour pairs that sit well with the storefront palette */
    private const PALETTES = [
        ['#1e3a8a', '#3b82f6'],
        ['#0f766e', '#2dd4bf'],
        ['#7c2d12', '#f59e0b'],
        ['#312e81', '#818cf8'],
        ['#14532d', '#84cc16'],
        ['#831843', '#f472b6'],
        ['#0c4a6e', '#38bdf8'],
        ['#3f3f46', '#a1a1aa'],
    ];

    public static function available(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }

    /** Writes a PNG to a temp file and returns its path. */
    public static function make(string $seed, int $width = 900, int $height = 900): string
    {
        if (! self::available()) {
            throw new RuntimeException('The GD extension is required to generate demo images.');
        }

        $hash = crc32($seed);
        [$from, $to] = self::PALETTES[$hash % count(self::PALETTES)];
        [$r1, $g1, $b1] = self::rgb($from);
        [$r2, $g2, $b2] = self::rgb($to);

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);

        // diagonal gradient, one scan line at a time
        for ($y = 0; $y < $height; $y++) {
            $t = $y / max(1, $height - 1);
            $colour = imagecolorallocate(
                $image,
                (int) ($r1 + ($r2 - $r1) * $t),
                (int) ($g1 + ($g2 - $g1) * $t),
                (int) ($b1 + ($b2 - $b1) * $t),
            );
            imageline($image, 0, $y, $width, $y, $colour);
        }

        // soft translucent shapes so each seed looks different
        $white = static fn (int $alpha) => imagecolorallocatealpha($image, 255, 255, 255, $alpha);
        for ($i = 0; $i < 4; $i++) {
            $cx = (int) ((($hash >> ($i * 3)) % 100) / 100 * $width);
            $cy = (int) ((($hash >> ($i * 5 + 1)) % 100) / 100 * $height);
            $size = (int) ($width * (0.18 + (($hash >> ($i + 2)) % 30) / 100));

            if ($i % 2 === 0) {
                imagefilledellipse($image, $cx, $cy, $size, $size, $white(108 + $i * 4));
            } else {
                imagefilledrectangle($image, $cx - (int) ($size / 2), $cy - (int) ($size / 3), $cx + (int) ($size / 2), $cy + (int) ($size / 3), $white(112));
            }
        }

        // subtle grid, echoing the storefront's blueprint pattern
        $grid = $white(120);
        for ($x = 0; $x < $width; $x += (int) ($width / 12)) {
            imageline($image, $x, 0, $x, $height, $grid);
        }
        for ($y = 0; $y < $height; $y += (int) ($height / 12)) {
            imageline($image, 0, $y, $width, $y, $grid);
        }

        $path = tempnam(sys_get_temp_dir(), 'alnasr-demo-').'.png';
        imagepng($image, $path, 6);
        imagedestroy($image);

        return $path;
    }

    /** @return array{0: int, 1: int, 2: int} */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
