<?php

namespace App\Filament\Pages;

use App\Enums\Background3dModel;
use App\Settings\SiteSettings;
use App\Support\Model3dChoices;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ManageSiteSettings extends SettingsPage
{
    protected static string $settings = SiteSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Site settings');
    }

    public function getTitle(): string
    {
        return __('Site settings');
    }

    protected static ?int $navigationSort = 10;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Settings')->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('Branding')->icon(Heroicon::OutlinedPaintBrush)->schema([
                    Section::make(__('Identity'))->columns(2)->schema([
                        TextInput::make('site_name_en')->label('Site name (EN)')->required(),
                        TextInput::make('site_name_ar')->label('Site name (AR)')->required()
                            ->extraInputAttributes(['dir' => 'rtl']),
                        FileUpload::make('logo')->image()->disk('public')->directory('branding')->visibility('public')
                            ->imageEditor()->helperText(__('Shown in the header and footer. Falls back to the site-name monogram.')),
                        FileUpload::make('favicon')->image()->disk('public')->directory('branding')->visibility('public'),
                        FileUpload::make('og_image')->label('Social share image')->image()->disk('public')
                            ->directory('branding')->visibility('public')->columnSpanFull(),
                    ]),
                    Section::make(__('Colour scheme'))
                        ->description(__('Every other shade on the storefront is derived from these four colours.'))
                        ->columns(2)
                        ->schema([
                            ColorPicker::make('primary_color')->label('Primary (buttons, links)')->required(),
                            ColorPicker::make('secondary_color')->label('Secondary (accent)')->required(),
                            ColorPicker::make('header_color')->label('Header & dark sections')->required(),
                            ColorPicker::make('footer_color')->label('Footer')->required(),
                        ]),
                    Section::make(__('Store'))->schema([
                        Select::make('currency')->options([
                            'USD' => __('USD — US dollar'),
                            'SAR' => __('SAR — Saudi riyal'),
                            'AED' => __('AED — UAE dirham'),
                            'KWD' => __('KWD — Kuwaiti dinar'),
                            'QAR' => __('QAR — Qatari riyal'),
                            'EGP' => __('EGP — Egyptian pound'),
                            'IQD' => __('IQD — Iraqi dinar'),
                            'EUR' => __('EUR — Euro'),
                        ])->searchable()->required(),
                    ]),
                ]),

                Tab::make('Contact')->icon(Heroicon::OutlinedPhone)->schema([
                    Section::make(__('Phone & email'))->columns(2)->schema([
                        PhoneInput::make('contact_phone')->label('Phone'),
                        PhoneInput::make('whatsapp_phone')->label('WhatsApp'),
                        TextInput::make('contact_email')->label('Email')->email(),
                    ]),
                    Section::make(__('Working hours'))->columns(2)->schema([
                        TextInput::make('working_hours_en')->label('Working hours (EN)')->placeholder(__('Sun – Thu · 8:00 – 17:00')),
                        TextInput::make('working_hours_ar')->label('Working hours (AR)')->extraInputAttributes(['dir' => 'rtl']),
                    ]),
                ]),

                Tab::make('Location')->icon(Heroicon::OutlinedMapPin)->schema([
                    Section::make(__('Address'))->columns(2)->schema([
                        Textarea::make('address_en')->label('Address (EN)')->rows(2),
                        Textarea::make('address_ar')->label('Address (AR)')->rows(2)->extraInputAttributes(['dir' => 'rtl']),
                    ]),
                    Section::make(__('Map'))
                        ->description(__('Used for the map on the contact page. Right-click your location in Google Maps to copy the coordinates.'))
                        ->columns(2)
                        ->schema([
                            TextInput::make('latitude')->numeric()->minValue(-90)->maxValue(90)->step('any'),
                            TextInput::make('longitude')->numeric()->minValue(-180)->maxValue(180)->step('any'),
                            TextInput::make('google_maps_url')->label('Google Maps link')->url()->columnSpanFull(),
                        ]),
                ]),

                Tab::make('Social')->icon(Heroicon::OutlinedShare)->schema([
                    Grid::make(2)->schema([
                        TextInput::make('facebook_url')->label('Facebook')->url(),
                        TextInput::make('instagram_url')->label('Instagram')->url(),
                        TextInput::make('x_url')->label('X (Twitter)')->url(),
                        TextInput::make('linkedin_url')->label('LinkedIn')->url(),
                        TextInput::make('youtube_url')->label('YouTube')->url(),
                        TextInput::make('tiktok_url')->label('TikTok')->url(),
                        TextInput::make('snapchat_url')->label('Snapchat')->url(),
                        TextInput::make('telegram_url')->label('Telegram')->url(),
                    ]),
                ]),

                Tab::make('3D background')->icon(Heroicon::OutlinedCube)->schema([
                    Section::make(__('3D background'))
                        ->description(__('A slowly turning 3D model behind the storefront pages — pick one that fits your business.'))
                        ->icon(Heroicon::OutlinedSparkles)
                        ->schema([
                            Toggle::make('bg3d_enabled')->label('Show the 3D background')
                                ->helperText(__('Switch it off to keep the storefront flat and fastest.'))
                                ->live(),
                        ]),

                    Section::make(__('Model'))
                        ->icon(Heroicon::OutlinedCube)
                        ->visible(fn (Get $get): bool => (bool) $get('bg3d_enabled'))
                        ->schema([
                            ViewField::make('bg3d_model')
                                ->label('3D model')
                                ->view('filament.forms.components.visual-picker')
                                ->viewData(fn (): array => ['options' => Model3dChoices::options(withNone: false)])
                                ->required()
                                ->in(fn (): array => Model3dChoices::keys(withNone: false))
                                ->live()
                                ->columnSpanFull(),
                            FileUpload::make('bg3d_custom_file')
                                ->label('Your 3D model file')
                                ->helperText(__('A .glb file (up to 15 MB). Export it from Blender, or download free models from Sketchfab or Poly Pizza.'))
                                ->disk('public')->directory('bg3d')->visibility('public')
                                ->acceptedFileTypes(['model/gltf-binary', 'application/octet-stream', 'model/gltf+json'])
                                ->rules(['extensions:glb,gltf'])
                                ->maxSize(15 * 1024)
                                ->visible(fn (Get $get): bool => $get('bg3d_model') === Background3dModel::Custom->value)
                                ->required(fn (Get $get): bool => $get('bg3d_model') === Background3dModel::Custom->value),
                        ]),

                    Section::make(__('Appearance'))
                        ->icon(Heroicon::OutlinedSwatch)
                        ->columns(2)
                        ->visible(fn (Get $get): bool => (bool) $get('bg3d_enabled'))
                        ->schema([
                            Select::make('bg3d_position')->label('Position')->options([
                                'right' => __('Right'),
                                'center' => __('Center'),
                                'left' => __('Left'),
                            ])->required(),
                            Select::make('bg3d_opacity')->label('Strength')->options([
                                30 => __('Subtle'),
                                55 => __('Balanced'),
                                80 => __('Strong'),
                                100 => __('Full'),
                            ])->required(),
                            Select::make('bg3d_speed')->label('Rotation speed')->options([
                                0 => __('Still'),
                                1 => __('Slow'),
                                2 => __('Normal'),
                                3 => __('Fast'),
                            ])->required(),
                            Select::make('bg3d_pages')->label('Show on')->options([
                                'all' => __('Every page'),
                                'home' => __('Home page only'),
                            ])->required(),
                            Toggle::make('bg3d_interactive')->label('React to mouse and scroll')
                                ->helperText(__('The model tilts gently as visitors move the mouse or scroll.'))
                                ->columnSpanFull(),
                        ]),
                ]),

                Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
                    Grid::make(2)->schema([
                        TextInput::make('seo_title_en')->label('Title (EN)')->required()->maxLength(100),
                        TextInput::make('seo_title_ar')->label('Title (AR)')->required()->maxLength(100)
                            ->extraInputAttributes(['dir' => 'rtl']),
                        Textarea::make('seo_description_en')->label('Description (EN)')->required()->rows(3)->maxLength(200),
                        Textarea::make('seo_description_ar')->label('Description (AR)')->required()->rows(3)->maxLength(200)
                            ->extraInputAttributes(['dir' => 'rtl']),
                    ]),
                ]),
            ]),
        ]);
    }
}
