<?php

namespace App\Support\Glb;

use InvalidArgumentException;

/**
 * Tiny glTF 2.0 binary (.glb) writer: rounded boxes and cylinders with PBR materials.
 * Enough to model equipment such as inverters, batteries, charge controllers and breakers
 * without any 3D tool or download. Y is up, the "front" faces +Z, units are metres.
 */
class GlbBuilder
{
    /** @var array<string, array{color: array<int, float>, metal: float, rough: float, emissive: array<int, float>|null}> */
    private array $materials = [];

    /** @var array<string, array{p: array<int, float>, n: array<int, float>, i: array<int, int>}> material name => geometry */
    private array $geometry = [];

    public function __construct(private readonly string $name = 'model')
    {
    }

    /** @var array<int, string> PNG files, referenced by materials */
    private array $images = [];

    /**
     * @param  string|null  $map  PNG bytes used as the base colour texture (the geometry then needs UVs: see plane())
     * @param  string|null  $glow  PNG bytes used as the emissive texture (screens and LEDs that light up)
     * @param  bool  $cutout  the map has transparent parts (a product photo without its background) that must not be drawn
     */
    public function material(string $name, string $hex, float $metal = 0.2, float $rough = 0.5, ?string $emissive = null, ?string $map = null, ?string $glow = null, bool $cutout = false): static
    {
        $this->materials[$name] = [
            'color' => [...self::linear($hex), 1.0],
            'metal' => $metal,
            'rough' => $rough,
            'emissive' => $glow ? [1.0, 1.0, 1.0] : ($emissive ? self::linear($emissive) : null),
            'map' => $map ? $this->image($map) : null,
            'glow' => $glow ? $this->image($glow) : null,
            'cutout' => $cutout,
        ];

        return $this;
    }

    /**
     * A flat rectangle facing +Z with texture coordinates (top-left of the picture = top-left of the plane).
     *
     * @param  array{0: float, 1: float, 2: float}  $pos
     * @param  array{0: float, 1: float, 2: float}  $rot
     */
    public function plane(string $material, float $width, float $height, array $pos = [0, 0, 0], array $rot = [0, 0, 0]): static
    {
        [$hw, $hh] = [$width / 2, $height / 2];

        return $this->emit(
            $material,
            [[-$hw, $hh, 0.0], [$hw, $hh, 0.0], [-$hw, -$hh, 0.0], [$hw, -$hh, 0.0]],
            array_fill(0, 4, [0.0, 0.0, 1.0]),
            [[0, 2, 1], [1, 2, 3]],
            $pos,
            $rot,
            [[0.0, 0.0], [1.0, 0.0], [0.0, 1.0], [1.0, 1.0]],
        );
    }

    private function image(string $png): int
    {
        $key = array_search($png, $this->images, true);
        if ($key !== false) {
            return $key;
        }
        $this->images[] = $png;

        return count($this->images) - 1;
    }

    /**
     * @param  array{0: float, 1: float, 2: float}  $size
     * @param  array{0: float, 1: float, 2: float}  $pos
     * @param  array{0: float, 1: float, 2: float}  $rot  degrees, applied X then Y then Z
     */
    public function box(string $material, array $size, array $pos = [0, 0, 0], array $rot = [0, 0, 0], float $radius = 0.0, int $segments = 3): static
    {
        [$sx, $sy, $sz] = array_map(fn ($v) => $v / 2, $size);
        $r = max(0.0, min($radius, min($sx, $sy, $sz) * 0.98));
        $h = [$sx, $sy, $sz];

        $axisCoords = function (float $half) use ($r, $segments): array {
            if ($r <= 0) {
                return [-$half, $half];
            }

            $coords = [];
            for ($i = 0; $i <= $segments; $i++) {
                $coords[] = -$half + $r * ($i / $segments);
            }
            for ($i = 0; $i <= $segments; $i++) {
                $coords[] = $half - $r + $r * ($i / $segments);
            }

            return $coords;
        };

        $positions = [];
        $normals = [];
        $indices = [];

        foreach ([0, 1, 2] as $axis) {
            foreach ([-1, 1] as $sign) {
                [$u, $v] = array_values(array_diff([0, 1, 2], [$axis]));
                $us = $axisCoords($h[$u]);
                $vs = $axisCoords($h[$v]);
                $base = count($positions);
                $cols = count($vs);

                foreach ($us as $uc) {
                    foreach ($vs as $vc) {
                        $p = [0.0, 0.0, 0.0];
                        $p[$axis] = $sign * $h[$axis];
                        $p[$u] = $uc;
                        $p[$v] = $vc;

                        if ($r > 0) {
                            $inner = [];
                            foreach ($p as $k => $c) {
                                $inner[$k] = max(-($h[$k] - $r), min($h[$k] - $r, $c));
                            }
                            $d = [$p[0] - $inner[0], $p[1] - $inner[1], $p[2] - $inner[2]];
                            $len = sqrt($d[0] ** 2 + $d[1] ** 2 + $d[2] ** 2) ?: 1.0;
                            $n = [$d[0] / $len, $d[1] / $len, $d[2] / $len];
                            $p = [$inner[0] + $n[0] * $r, $inner[1] + $n[1] * $r, $inner[2] + $n[2] * $r];
                        } else {
                            $n = [0.0, 0.0, 0.0];
                            $n[$axis] = (float) $sign;
                        }

                        $positions[] = $p;
                        $normals[] = $n;
                    }
                }

                for ($i = 0; $i < count($us) - 1; $i++) {
                    for ($j = 0; $j < $cols - 1; $j++) {
                        $a = $base + $i * $cols + $j;
                        $indices[] = [$a, $a + 1, $a + $cols];
                        $indices[] = [$a + 1, $a + $cols + 1, $a + $cols];
                    }
                }
            }
        }

        return $this->emit($material, $positions, $normals, $indices, $pos, $rot);
    }

    /**
     * A cylinder along Y (use $rot to lay it down). `$radiusTop` makes it a cone / tapered cylinder.
     *
     * @param  array{0: float, 1: float, 2: float}  $pos
     * @param  array{0: float, 1: float, 2: float}  $rot
     */
    public function cylinder(string $material, float $radius, float $height, array $pos = [0, 0, 0], array $rot = [0, 0, 0], int $segments = 24, ?float $radiusTop = null): static
    {
        $rt = $radiusTop ?? $radius;
        $hh = $height / 2;
        $positions = [];
        $normals = [];
        $indices = [];
        $slope = ($radius - $rt) / max($height, 1e-6);

        // side
        for ($i = 0; $i <= $segments; $i++) {
            $a = 2 * M_PI * $i / $segments;
            $cx = cos($a);
            $cz = sin($a);
            $len = sqrt(1 + $slope ** 2);
            $n = [$cx / $len, $slope / $len, $cz / $len];
            $positions[] = [$radius * $cx, -$hh, $radius * $cz];
            $normals[] = $n;
            $positions[] = [$rt * $cx, $hh, $rt * $cz];
            $normals[] = $n;
        }
        for ($i = 0; $i < $segments; $i++) {
            $a = $i * 2;
            $indices[] = [$a, $a + 2, $a + 1];
            $indices[] = [$a + 1, $a + 2, $a + 3];
        }

        // caps
        foreach ([[-1, $radius, -$hh], [1, $rt, $hh]] as [$sign, $rad, $y]) {
            if ($rad <= 0) {
                continue;
            }
            $centre = count($positions);
            $positions[] = [0.0, $y, 0.0];
            $normals[] = [0.0, (float) $sign, 0.0];
            for ($i = 0; $i <= $segments; $i++) {
                $a = 2 * M_PI * $i / $segments;
                $positions[] = [$rad * cos($a), $y, $rad * sin($a)];
                $normals[] = [0.0, (float) $sign, 0.0];
            }
            for ($i = 0; $i < $segments; $i++) {
                $indices[] = [$centre, $centre + 1 + $i, $centre + 2 + $i];
            }
        }

        return $this->emit($material, $positions, $normals, $indices, $pos, $rot);
    }

    /** The finished file. */
    public function toGlb(): string
    {
        if ($this->geometry === []) {
            throw new InvalidArgumentException('The model has no geometry.');
        }

        $bin = '';
        $views = [];
        $accessors = [];
        $primitives = [];
        $materialIndex = array_flip(array_keys($this->materials));

        foreach ($this->geometry as $material => $g) {
            if (! isset($materialIndex[$material])) {
                throw new InvalidArgumentException("Unknown material \"{$material}\".");
            }

            $count = intdiv(count($g['p']), 3);
            $min = [INF, INF, INF];
            $max = [-INF, -INF, -INF];
            for ($i = 0; $i < count($g['p']); $i += 3) {
                for ($k = 0; $k < 3; $k++) {
                    $min[$k] = min($min[$k], $g['p'][$i + $k]);
                    $max[$k] = max($max[$k], $g['p'][$i + $k]);
                }
            }

            $views[] = ['buffer' => 0, 'byteOffset' => strlen($bin), 'byteLength' => $count * 12, 'target' => 34962];
            $bin .= pack('g*', ...$g['p']);
            $positionAccessor = count($accessors);
            $accessors[] = ['bufferView' => count($views) - 1, 'componentType' => 5126, 'count' => $count, 'type' => 'VEC3', 'min' => $min, 'max' => $max];

            $views[] = ['buffer' => 0, 'byteOffset' => strlen($bin), 'byteLength' => $count * 12, 'target' => 34962];
            $bin .= pack('g*', ...$g['n']);
            $accessors[] = ['bufferView' => count($views) - 1, 'componentType' => 5126, 'count' => $count, 'type' => 'VEC3'];

            $views[] = ['buffer' => 0, 'byteOffset' => strlen($bin), 'byteLength' => count($g['i']) * 4, 'target' => 34963];
            $bin .= pack('V*', ...$g['i']);
            $accessors[] = ['bufferView' => count($views) - 1, 'componentType' => 5125, 'count' => count($g['i']), 'type' => 'SCALAR'];

            $attributes = ['POSITION' => $positionAccessor, 'NORMAL' => $positionAccessor + 1];

            $m = $this->materials[$material];
            if ($m['map'] !== null || $m['glow'] !== null) {
                if (count($g['uv']) !== $count * 2) {
                    throw new InvalidArgumentException("Material \"{$material}\" is textured: build its geometry with plane().");
                }

                $views[] = ['buffer' => 0, 'byteOffset' => strlen($bin), 'byteLength' => $count * 8, 'target' => 34962];
                $bin .= pack('g*', ...$g['uv']);
                $accessors[] = ['bufferView' => count($views) - 1, 'componentType' => 5126, 'count' => $count, 'type' => 'VEC2'];
                $attributes['TEXCOORD_0'] = count($accessors) - 1;
            }

            $primitives[] = [
                'attributes' => $attributes,
                'indices' => $positionAccessor + 2,
                'material' => $materialIndex[$material],
                'mode' => 4,
            ];
        }

        $images = [];
        foreach ($this->images as $png) {
            $views[] = ['buffer' => 0, 'byteOffset' => strlen($bin), 'byteLength' => strlen($png)];
            $bin .= $png.str_repeat("\0", (4 - strlen($png) % 4) % 4);
            $images[] = ['bufferView' => count($views) - 1, 'mimeType' => str_starts_with($png, "ÿØ") ? 'image/jpeg' : 'image/png'];
        }

        $materials = [];
        foreach ($this->materials as $name => $m) {
            $pbr = ['baseColorFactor' => $m['color'], 'metallicFactor' => $m['metal'], 'roughnessFactor' => $m['rough']];
            if ($m['map'] !== null) {
                $pbr['baseColorTexture'] = ['index' => $m['map']];
            }
            $entry = ['name' => $name, 'pbrMetallicRoughness' => $pbr];
            if ($m['emissive']) {
                $entry['emissiveFactor'] = $m['emissive'];
            }
            if ($m['glow'] !== null) {
                $entry['emissiveTexture'] = ['index' => $m['glow']];
            }
            if ($m['cutout']) {
                $entry['alphaMode'] = 'MASK';
                $entry['alphaCutoff'] = 0.5;
            }
            $materials[] = $entry;
        }

        $document = [
            'asset' => ['version' => '2.0', 'generator' => 'Al-Nasr GlbBuilder'],
            'scene' => 0,
            'scenes' => [['nodes' => [0]]],
            'nodes' => [['name' => $this->name, 'mesh' => 0]],
            'meshes' => [['name' => $this->name, 'primitives' => $primitives]],
            'materials' => $materials,
            'accessors' => $accessors,
            'bufferViews' => $views,
            'buffers' => [['byteLength' => strlen($bin)]],
        ];
        if ($images !== []) {
            $document['images'] = $images;
            $document['samplers'] = [['magFilter' => 9729, 'minFilter' => 9987, 'wrapS' => 33071, 'wrapT' => 33071]];
            $document['textures'] = array_map(fn ($i) => ['source' => $i, 'sampler' => 0], array_keys($images));
        }

        $json = json_encode($document, JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);

        $json .= str_repeat(' ', (4 - strlen($json) % 4) % 4);
        $bin .= str_repeat("\0", (4 - strlen($bin) % 4) % 4);

        $total = 12 + 8 + strlen($json) + 8 + strlen($bin);

        return pack('a4VV', 'glTF', 2, $total)
            .pack('Va4', strlen($json), 'JSON').$json
            .pack('Va4', strlen($bin), "BIN\0").$bin;
    }

    // -- internals --------------------------------------------------------------

    /**
     * @param  array<int, array<int, float>>  $positions
     * @param  array<int, array<int, float>>  $normals
     * @param  array<int, array<int, int>>  $triangles
     * @param  array<int, float>  $pos
     * @param  array<int, float>  $rot
     * @param  array<int, array<int, float>>|null  $uvs
     */
    private function emit(string $material, array $positions, array $normals, array $triangles, array $pos, array $rot, ?array $uvs = null): static
    {
        $matrix = self::rotation($rot);
        $g = &$this->geometry[$material];
        $g ??= ['p' => [], 'n' => [], 'i' => [], 'uv' => []];
        foreach ($uvs ?? [] as $uv) {
            array_push($g['uv'], $uv[0], $uv[1]);
        }
        $offset = intdiv(count($g['p']), 3);

        // make every triangle face the way its vertex normals point
        foreach ($triangles as [$a, $b, $c]) {
            $e1 = [$positions[$b][0] - $positions[$a][0], $positions[$b][1] - $positions[$a][1], $positions[$b][2] - $positions[$a][2]];
            $e2 = [$positions[$c][0] - $positions[$a][0], $positions[$c][1] - $positions[$a][1], $positions[$c][2] - $positions[$a][2]];
            $cross = [$e1[1] * $e2[2] - $e1[2] * $e2[1], $e1[2] * $e2[0] - $e1[0] * $e2[2], $e1[0] * $e2[1] - $e1[1] * $e2[0]];
            $avg = [
                $normals[$a][0] + $normals[$b][0] + $normals[$c][0],
                $normals[$a][1] + $normals[$b][1] + $normals[$c][1],
                $normals[$a][2] + $normals[$b][2] + $normals[$c][2],
            ];
            $dot = $cross[0] * $avg[0] + $cross[1] * $avg[1] + $cross[2] * $avg[2];

            if (abs($dot) < 1e-14) {
                continue; // degenerate
            }

            $dot < 0 ? array_push($g['i'], $offset + $a, $offset + $c, $offset + $b) : array_push($g['i'], $offset + $a, $offset + $b, $offset + $c);
        }

        foreach ($positions as $k => $p) {
            $t = self::apply($matrix, $p);
            array_push($g['p'], $t[0] + $pos[0], $t[1] + $pos[1], $t[2] + $pos[2]);

            $n = self::apply($matrix, $normals[$k]);
            array_push($g['n'], $n[0], $n[1], $n[2]);
        }

        return $this;
    }

    /** @param  array<int, float>  $deg @return array<int, array<int, float>> */
    private static function rotation(array $deg): array
    {
        [$x, $y, $z] = array_map('deg2rad', $deg);
        [$cx, $sx, $cy, $sy, $cz, $sz] = [cos($x), sin($x), cos($y), sin($y), cos($z), sin($z)];

        // Rz * Ry * Rx
        return [
            [$cz * $cy, $cz * $sy * $sx - $sz * $cx, $cz * $sy * $cx + $sz * $sx],
            [$sz * $cy, $sz * $sy * $sx + $cz * $cx, $sz * $sy * $cx - $cz * $sx],
            [-$sy, $cy * $sx, $cy * $cx],
        ];
    }

    /** @param  array<int, array<int, float>>  $m @param  array<int, float>  $v @return array<int, float> */
    private static function apply(array $m, array $v): array
    {
        return [
            $m[0][0] * $v[0] + $m[0][1] * $v[1] + $m[0][2] * $v[2],
            $m[1][0] * $v[0] + $m[1][1] * $v[1] + $m[1][2] * $v[2],
            $m[2][0] * $v[0] + $m[2][1] * $v[1] + $m[2][2] * $v[2],
        ];
    }

    /** @return array<int, float> sRGB hex → linear RGB (what glTF expects) */
    private static function linear(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $rgb = array_map(fn ($c) => hexdec($c) / 255, str_split($hex, 2));

        return array_map(fn ($c) => $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4, $rgb);
    }
}
