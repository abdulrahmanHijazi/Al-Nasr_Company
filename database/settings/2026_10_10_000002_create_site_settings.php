<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'site_name_en' => 'Al-Nasr Company',
            'site_name_ar' => 'شركة النصر',
            'logo' => null,
            'favicon' => null,
            'og_image' => null,

            'primary_color' => '#1d4ed8',
            'secondary_color' => '#d99a1f',
            'header_color' => '#0b1f3a',
            'footer_color' => '#07152a',
            'currency' => 'USD',

            'address_en' => null,
            'address_ar' => null,
            'latitude' => null,
            'longitude' => null,
            'google_maps_url' => null,

            'contact_phone' => null,
            'whatsapp_phone' => null,
            'contact_email' => null,
            'working_hours_en' => null,
            'working_hours_ar' => null,

            'facebook_url' => null,
            'instagram_url' => null,
            'x_url' => null,
            'linkedin_url' => null,
            'youtube_url' => null,
            'tiktok_url' => null,
            'snapchat_url' => null,
            'telegram_url' => null,

            'seo_title_en' => 'Al-Nasr Company — Solar, Batteries, Industrial & Security Systems',
            'seo_title_ar' => 'شركة النصر — الطاقة الشمسية والبطاريات والمعدات الصناعية وأنظمة المراقبة',
            'seo_description_en' => 'Solar panels, lithium batteries, industrial equipment and surveillance cameras — with a team that installs solar systems, control panels, inverters and alarm systems.',
            'seo_description_ar' => 'ألواح شمسية وبطاريات ليثيوم ومعدات صناعية وكاميرات مراقبة — مع فريق متخصص في تركيب أنظمة الطاقة الشمسية ولوحات التحكم والانفرترات وأنظمة الإنذار.',
        ];

        foreach ($defaults as $property => $value) {
            $this->migrator->add("site.{$property}", $value);
        }
    }
};
