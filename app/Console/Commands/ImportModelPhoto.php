<?php

namespace App\Console\Commands;

use App\Support\Glb\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class ImportModelPhoto extends Command
{
    protected $signature = 'models3d:photo {slug : Library model the photo belongs to, e.g. inverter-felicity-hybrid} {source : URL or file path of a straight front photo on a plain white background}';

    protected $description = 'Save a product photo (from a manufacturer site) for a library model; models3d:build wraps it on the front of the model';

    public function handle(): int
    {
        $source = (string) $this->argument('source');

        try {
            $bytes = preg_match('#^https?://#i', $source)
                ? Http::withUserAgent('Mozilla/5.0')->timeout(40)->throw()->get($source)->body()
                : (is_file($source) ? File::get($source) : throw new InvalidArgumentException("File not found: {$source}"));

            $photo = Photo::crop($bytes);
        } catch (\Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        File::ensureDirectoryExists(Photo::dir());
        File::put(Photo::dir().'/'.$this->argument('slug').'.png', $photo['png']);

        $this->components->info("Saved {$photo['width']}×{$photo['height']} photo for {$this->argument('slug')}. Run: php artisan models3d:build --force");

        return self::SUCCESS;
    }
}
