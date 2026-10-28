<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('site.bg3d_enabled', true);
        $this->migrator->add('site.bg3d_model', 'solar-panel');
        $this->migrator->add('site.bg3d_custom_file', null);
        $this->migrator->add('site.bg3d_position', 'right');
        $this->migrator->add('site.bg3d_opacity', 55);
        $this->migrator->add('site.bg3d_speed', 1);
        $this->migrator->add('site.bg3d_interactive', true);
        $this->migrator->add('site.bg3d_pages', 'all');
    }
};
