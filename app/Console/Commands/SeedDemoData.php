<?php

namespace App\Console\Commands;

use App\Support\DemoData;
use App\Support\PlaceholderImage;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

class SeedDemoData extends Command
{
    protected $description = 'Insert realistic demo data: brands, categories, products, offers, projects, messages and the home layout';

    protected $signature = 'demo:seed
        {--fresh : Delete the existing catalogue, offers, projects, messages and home sections first}
        {--extra=0 : Add this many random filler products (handy for testing pagination and filters)}
        {--messages=12 : How many contact messages to create}
        {--no-images : Skip generating placeholder images}
        {--no-models : Skip importing the 3D equipment library (models3d:import)}
        {--force : Do not ask for confirmation / allow running in production}';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        if (app()->isProduction() && ! $force) {
            $this->components->error('Refusing to insert demo data in production. Pass --force if you really mean it.');

            return self::FAILURE;
        }

        $withImages = ! $this->option('no-images');

        if ($withImages && ! PlaceholderImage::available()) {
            $this->components->warn('The GD PHP extension is missing — continuing without images.');
            $withImages = false;
        }

        $demo = new DemoData($withImages);

        if ($this->option('fresh')) {
            if (! $force && ! $this->confirmWipe()) {
                $this->components->warn('Cancelled — nothing was changed.');

                return self::FAILURE;
            }

            $this->components->task('Removing existing catalogue and content', fn () => $demo->wipe());
        }

        $counts = [];
        $this->components->task(
            $withImages ? 'Creating demo data and images' : 'Creating demo data',
            function () use ($demo, &$counts) {
                $counts = $demo->run(
                    extraProducts: max(0, (int) $this->option('extra')),
                    messages: max(0, (int) $this->option('messages')),
                );
            },
        );

        if (! $this->option('no-models')) {
            $this->call('models3d:import');
        }

        $this->newLine();
        $this->table(['Table', 'Rows'], collect($counts)->map(fn ($n, $label) => [ucfirst($label), $n])->values()->all());

        if ($withImages && ! is_link(public_path('storage')) && ! is_dir(public_path('storage'))) {
            $this->components->warn('Images are on the public disk but public/storage is missing — run: php artisan storage:link');
        }

        $this->components->info('Done. Open / for the storefront and /admin for the dashboard.');

        return self::SUCCESS;
    }

    private function confirmWipe(): bool
    {
        if (! $this->input->isInteractive()) {
            $this->components->error('--fresh deletes data. Add --force to run it non-interactively.');

            return false;
        }

        return confirm('This deletes all products, brands, categories, offers, projects, messages and home sections. Continue?', default: false);
    }
}
