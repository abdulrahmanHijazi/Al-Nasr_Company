<?php

namespace App\Support\Glb;

use InvalidArgumentException;

/**
 * Prepares a product photo (a straight front view on a plain light background, like the ones on manufacturers'
 * websites) so it can be wrapped on the front of a model: finds the product, crops to it and re-encodes it.
 */
class Photo
{
    /**
     * @return array{png: string, width: int, height: int, edge: string} the cropped picture (background made transparent) and the colour of its outer edge (for the body)
     */
    public static function crop(string $bytes, int $maxSide = 1024): array
    {
        $src = @imagecreatefromstring($bytes);
        if (! $src) {
            throw new InvalidArgumentException('That file is not a picture GD can read (use PNG, JPG or WEBP).');
        }

        $w = imagesx($src);
        $h = imagesy($src);

        // flatten any transparency onto white so the background is uniform
        $flat = imagecreatetruecolor($w, $h);
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $src, 0, 0, 0, 0, $w, $h);

        // how far each pixel is from the white background
        $rows = array_fill(0, $h, 0);
        $cols = array_fill(0, $w, 0);
        for ($y = 0; $y < $h; $y += 2) {
            for ($x = 0; $x < $w; $x += 2) {
                $c = imagecolorat($flat, $x, $y);
                $d = 255 - min(($c >> 16) & 255, ($c >> 8) & 255, $c & 255);
                if ($d > 14) {
                    $rows[$y]++;
                    $cols[$x]++;
                }
            }
        }

        // the product spans the rows / columns where a fair share of the pixels are not background
        $minRow = max(4, (int) ($w * 0.05 / 2));
        $minCol = max(4, (int) ($h * 0.05 / 2));
        $ys = array_keys(array_filter($rows, fn ($n) => $n >= $minRow));
        $xs = array_keys(array_filter($cols, fn ($n) => $n >= $minCol));
        if ($ys === [] || $xs === []) {
            throw new InvalidArgumentException('Could not find the product in that picture (is the background plain white?).');
        }

        [$x1, $x2, $y1, $y2] = [min($xs), max($xs), min($ys), max($ys)];
        $cw = $x2 - $x1 + 1;
        $ch = $y2 - $y1 + 1;

        $scale = min(1.0, $maxSide / max($cw, $ch));
        $ow = max(8, (int) round($cw * $scale));
        $oh = max(8, (int) round($ch * $scale));
        $out = imagecreatetruecolor($ow, $oh);
        imagecopyresampled($out, $flat, 0, 0, $x1, $y1, $ow, $oh, $cw, $ch);

        // average colour of a thin band inside the left edge (what the sides of the body should look like)
        $r = $g = $b = $n = 0;
        for ($y = (int) ($oh * 0.2); $y < (int) ($oh * 0.8); $y += 3) {
            $c = imagecolorat($out, (int) max(2, $ow * 0.04), $y);
            $r += ($c >> 16) & 255;
            $g += ($c >> 8) & 255;
            $b += $c & 255;
            $n++;
        }
        $n = max(1, $n);

        // the plain background joined to the border becomes transparent, so rounded corners and feet stay clean
        imagealphablending($out, false);
        imagesavealpha($out, true);
        $clear = imagecolorallocatealpha($out, 255, 255, 255, 127);
        $seen = new \SplFixedArray($ow * $oh);
        $stack = [];
        for ($x = 0; $x < $ow; $x++) {
            array_push($stack, $x, ($oh - 1) * $ow + $x);
        }
        for ($y = 0; $y < $oh; $y++) {
            array_push($stack, $y * $ow, $y * $ow + $ow - 1);
        }
        while ($stack !== []) {
            $i = array_pop($stack);
            if ($seen[$i]) {
                continue;
            }
            $seen[$i] = true;
            [$px, $py] = [$i % $ow, intdiv($i, $ow)];
            $c = imagecolorat($out, $px, $py);
            if (min(($c >> 16) & 255, ($c >> 8) & 255, $c & 255) < 250) {
                continue;
            }
            imagesetpixel($out, $px, $py, $clear);
            foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
                [$nx, $ny] = [$px + $dx, $py + $dy];
                if ($nx >= 0 && $ny >= 0 && $nx < $ow && $ny < $oh && ! $seen[$ny * $ow + $nx]) {
                    $stack[] = $ny * $ow + $nx;
                }
            }
        }

        ob_start();
        imagepng($out, null, 9);

        return [
            'png' => (string) ob_get_clean(),
            'width' => $ow,
            'height' => $oh,
            'edge' => sprintf('#%02x%02x%02x', intdiv($r, $n), intdiv($g, $n), intdiv($b, $n)),
        ];
    }

    /** @return array{width: int, height: int, edge: string} */
    public static function describe(string $path): array
    {
        $img = imagecreatefrompng($path);
        [$w, $h] = [imagesx($img), imagesy($img)];
        $r = $g = $b = $n = 0;

        for ($y = (int) ($h * 0.2); $y < (int) ($h * 0.8); $y += 3) {
            for ($x = (int) ($w * 0.03); $x < (int) ($w * 0.2); $x += 3) {
                $c = imagecolorat($img, $x, $y);
                if (($c >> 24) & 127) {
                    continue;
                }
                $r += ($c >> 16) & 255;
                $g += ($c >> 8) & 255;
                $b += $c & 255;
                $n++;
            }
        }
        $n = max(1, $n);

        return ['width' => $w, 'height' => $h, 'edge' => sprintf('#%02x%02x%02x', intdiv($r, $n), intdiv($g, $n), intdiv($b, $n))];
    }

    public static function dir(): string
    {
        return database_path('models3d/photos');
    }

    /** The saved photo of a library model, if there is one. */
    public static function for(string $slug): ?string
    {
        $path = self::dir()."/{$slug}.png";

        return is_file($path) ? $path : null;
    }
}
