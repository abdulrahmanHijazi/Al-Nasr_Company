<?php

namespace App\Console\Commands;

use App\Enums\Background3dModel;
use App\Enums\HomeSectionType;
use App\Support\DesignPreview;
use App\Support\ModelPreview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateDesignPreviews extends Command
{
    protected $signature = 'design:previews';

    protected $description = 'Regenerate the preview images of every home-section design and 3D background model (public/images)';

    public function handle(): int
    {
        File::ensureDirectoryExists(public_path('images/designs'));

        $count = 0;

        foreach (HomeSectionType::cases() as $type) {
            foreach (array_keys($type->designs()) as $design) {
                File::put(public_path(DesignPreview::path($type, $design)), DesignPreview::svg($type, $design));
                $count++;
            }
        }

        $this->components->info("Wrote {$count} design previews to public/images/designs.");

        File::ensureDirectoryExists(public_path('images/models'));

        foreach (Background3dModel::cases() as $model) {
            File::put(public_path($model->previewPath()), ModelPreview::svg($model));
        }

        File::put(public_path('images/models/none.svg'), ModelPreview::noneSvg());

        $this->components->info(count(Background3dModel::cases()).' 3D-model previews written to public/images/models.');

        return self::SUCCESS;
    }
}
