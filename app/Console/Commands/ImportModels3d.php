<?php

namespace App\Console\Commands;

use App\Enums\ThreeDModelType;
use App\Models\ThreeDModel;
use App\Support\Glb\LibraryPreview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportModels3d extends Command
{
    protected $signature = 'models3d:import
        {--path= : Folder with the .glb files and manifest.json (default: database/models3d)}
        {--fresh : Remove library models whose file is no longer in the folder}';

    protected $description = 'Import the 3D equipment library (inverters, batteries, charge controllers, breakers) into the database and storage';

    public function handle(): int
    {
        $dir = $this->option('path') ?: database_path('models3d');

        // first run on a fresh checkout: write the library files too
        if (! File::isDirectory($dir) || ! File::glob("{$dir}/*.glb")) {
            $this->components->info('No model files yet — building the library first.');
            $this->call('models3d:build');
        }

        $entries = $this->entries($dir);

        if ($entries === []) {
            $this->components->error("No .glb files found in {$dir}.");

            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $disk->makeDirectory('models3d/previews');

        $created = 0;
        $updated = 0;
        $rows = [];

        foreach (array_values($entries) as $i => $entry) {
            $source = "{$dir}/{$entry['file']}";

            if (! File::exists($source)) {
                $this->components->warn("Skipped {$entry['slug']}: {$entry['file']} is missing.");

                continue;
            }

            $type = ThreeDModelType::tryFrom($entry['type']) ?? ThreeDModelType::Other;
            $file = "models3d/{$entry['file']}";
            $preview = "models3d/previews/{$entry['slug']}.svg";

            $disk->put($file, File::get($source));
            $disk->put($preview, LibraryPreview::svg($type, $entry['color'], $entry['brand']));

            $model = ThreeDModel::query()->firstOrNew(['slug' => $entry['slug']]);
            $model->exists ? $updated++ : $created++;

            $model->fill([
                'name' => $entry['name'],
                'brand' => $entry['brand'],
                'type' => $type,
                'file' => $file,
                'preview' => $preview,
                'sort_order' => $i,
            ]);
            $model->is_active ??= true;
            $model->save();

            $rows[] = [$type->value, $entry['brand'] ?? '—', $entry['name']['en'], $entry['file']];
        }

        if ($this->option('fresh')) {
            ThreeDModel::query()->whereNotIn('slug', array_column($entries, 'slug'))->get()->each->delete();
        }

        $this->table(['Type', 'Brand', 'Model', 'File'], $rows);
        $this->components->info("3D library ready: {$created} added, {$updated} updated (".ThreeDModel::count().' models in total).');

        if (! File::exists(public_path('storage'))) {
            $this->components->warn('Run `php artisan storage:link` once so the models and pictures are served.');
        }

        return self::SUCCESS;
    }

    /**
     * Manifest entries, plus any .glb in the folder the manifest does not list (drop in a real model and it works).
     *
     * @return array<int, array{slug: string, file: string, type: string, brand: string|null, name: array{en: string, ar: string}, color: string}>
     */
    private function entries(string $dir): array
    {
        $manifestPath = "{$dir}/manifest.json";
        $manifest = File::exists($manifestPath) ? (json_decode(File::get($manifestPath), true) ?: []) : [];
        $entries = [];

        foreach ($manifest as $row) {
            $entries[$row['file']] = [
                'slug' => $row['slug'],
                'file' => $row['file'],
                'type' => $row['type'] ?? 'other',
                'brand' => $row['brand'] ?? null,
                'name' => $row['name'],
                'color' => $row['color'] ?? '#2563eb',
            ];
        }

        foreach (File::glob("{$dir}/*.glb") as $path) {
            $file = basename($path);

            if (isset($entries[$file])) {
                continue;
            }

            $slug = Str::slug(pathinfo($file, PATHINFO_FILENAME));
            $title = Str::headline($slug);
            $entries[$file] = ['slug' => $slug, 'file' => $file, 'type' => 'other', 'brand' => null, 'name' => ['en' => $title, 'ar' => $title], 'color' => '#2563eb'];
        }

        return array_values($entries);
    }
}
