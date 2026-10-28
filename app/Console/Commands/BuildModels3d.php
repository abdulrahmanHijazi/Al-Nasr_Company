<?php

namespace App\Console\Commands;

use App\Support\Glb\Library;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BuildModels3d extends Command
{
    protected $signature = 'models3d:build {--force : Overwrite .glb files that already exist (also ones you replaced with real models)}';

    protected $description = 'Write the equipment 3D library (.glb files + manifest.json) to database/models3d';

    public function handle(): int
    {
        $dir = database_path('models3d');
        File::ensureDirectoryExists($dir);

        $manifestPath = $dir.'/manifest.json';
        $manifest = File::exists($manifestPath) ? (json_decode(File::get($manifestPath), true) ?: []) : [];
        $byFile = collect($manifest)->keyBy('file');

        $written = 0;
        $kept = 0;

        foreach (Library::entries() as $entry) {
            $file = "{$entry['slug']}.glb";
            $path = "{$dir}/{$file}";

            if (File::exists($path) && ! $this->option('force')) {
                $kept++;
            } else {
                File::put($path, $entry['build']()->toGlb());
                $written++;
            }

            // an entry you edited by hand keeps your values unless --force
            if ($this->option('force') || ! $byFile->has($file)) {
                $byFile[$file] = [
                    'slug' => $entry['slug'],
                    'file' => $file,
                    'type' => $entry['type']->value,
                    'brand' => $entry['brand'],
                    'name' => $entry['name'],
                    'color' => $entry['color'],
                ];
            }
        }

        File::put($manifestPath, json_encode($byFile->values()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");

        $this->components->info("{$written} models written, {$kept} kept as they were → database/models3d");

        return self::SUCCESS;
    }
}
