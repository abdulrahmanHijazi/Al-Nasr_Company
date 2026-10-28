<?php

namespace App\Filament\Resources\HomePageSections\Schemas;

use App\Enums\Background3dModel;
use App\Enums\HomeSectionType;
use App\Enums\SectionShowType;
use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\PortfolioItem;
use App\Models\Product;
use App\Support\DesignPreview;
use App\Support\Model3dChoices;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HomePageSectionForm
{
    /** key => label; keys are mapped to lucide icons in resources/js/lib/icons.ts */
    private const ICONS = [
        'sun' => 'Sun / solar',
        'zap' => 'Lightning / power',
        'battery' => 'Battery',
        'factory' => 'Factory / industrial',
        'cpu' => 'Control panel / chip',
        'cctv' => 'Camera / CCTV',
        'bell' => 'Alarm',
        'wrench' => 'Wrench / installation',
        'shield' => 'Shield / security',
        'truck' => 'Delivery',
        'headset' => 'Support',
        'badge' => 'Quality badge',
        'clipboard' => 'Survey / checklist',
        'hardhat' => 'Site / engineering',
    ];

    /** @return array<string, string> translated icon labels */
    public static function icons(): array
    {
        return array_map(fn (string $label) => __($label), self::ICONS);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
            Section::make(__('Section'))
                ->description(__('What this block shows and how it looks on the storefront.'))
                ->icon(Heroicon::OutlinedRectangleGroup)
                ->columns(2)
                ->schema([
                Select::make('type')
                    ->label('Section type')
                    ->options(HomeSectionType::options())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state): void {
                        $set('design', self::type($state)?->defaultDesign());
                    }),
                ViewField::make('design')
                    ->label('Design')
                    ->view('filament.forms.components.visual-picker')
                    ->viewData(fn (Get $get): array => ['options' => self::designOptions(self::type($get('type')))])
                    ->required()
                    ->helperText(__('How this section looks on the storefront.'))
                    ->columnSpanFull(),
                TextInput::make('title')->maxLength(255)
                    ->visible(fn (Get $get): bool => ! in_array(self::type($get('type')), [null, HomeSectionType::Hero, HomeSectionType::Cta], true)),
                Textarea::make('subtitle')->rows(2)
                    ->visible(fn (Get $get): bool => ! in_array(self::type($get('type')), [null, HomeSectionType::Hero, HomeSectionType::Cta], true)),
            ]),

            Section::make(__('3D model'))
                ->description(__('An optional rotating 3D model shown inside this section (desktop screens). Pick "No 3D model" to keep it flat.'))
                ->icon(Heroicon::OutlinedCube)
                ->schema([
                    ViewField::make('model_3d')
                        ->label('3D model')
                        ->view('filament.forms.components.visual-picker')
                        ->viewData(fn (): array => ['options' => Model3dChoices::options()])
                        ->default('none')
                        ->formatStateUsing(fn ($state): string => filled($state) ? $state : 'none')
                        ->in(fn (): array => Model3dChoices::keys())
                        ->live()
                        ->columnSpanFull(),
                    ToggleButtons::make('model_3d_side')
                        ->label('Model position')
                        ->options(['end' => __('End (right in English)'), 'start' => __('Start (left in English)')])
                        ->default('end')
                        ->inline()
                        ->helperText(__('In hero sections the model replaces the floating product cards (Split and Centered designs).'))
                        ->visible(fn (Get $get): bool => filled($get('model_3d')) && $get('model_3d') !== 'none'),
                    FileUpload::make('model_3d_file')
                        ->label('Your 3D model file')
                        ->helperText(__('A .glb file (up to 15 MB). Export it from Blender, or download free models from Sketchfab or Poly Pizza.'))
                        ->disk('public')->directory('home/models')->visibility('public')
                        ->acceptedFileTypes(['model/gltf-binary', 'application/octet-stream', 'model/gltf+json'])
                        ->rules(['extensions:glb,gltf'])
                        ->maxSize(15 * 1024)
                        ->visible(fn (Get $get): bool => $get('model_3d') === Background3dModel::Custom->value)
                        ->required(fn (Get $get): bool => $get('model_3d') === Background3dModel::Custom->value),
                ]),

            Section::make(__('Which items to show'))
                ->icon(Heroicon::OutlinedQueueList)
                ->visible(fn (Get $get): bool => (bool) self::type($get('type'))?->usesItems())
                ->columns(2)
                ->schema([
                    ToggleButtons::make('show_type')
                        ->label('Source')
                        ->options(SectionShowType::options())
                        ->default(SectionShowType::Latest->value)
                        ->inline()
                        ->live()
                        ->columnSpanFull(),
                    TextInput::make('limit')->label('How many')->numeric()->integer()->minValue(1)->maxValue(24)->default(8)
                        ->visible(fn (Get $get): bool => self::showType($get('show_type')) !== SectionShowType::Custom),
                    self::picker('products', Product::class, 'name', [HomeSectionType::Hero, HomeSectionType::Products]),
                    self::picker('categories', Category::class, 'name', [HomeSectionType::Categories]),
                    self::picker('brands', Brand::class, 'name', [HomeSectionType::Brands]),
                    self::picker('bundles', Bundle::class, 'name', [HomeSectionType::Bundles]),
                    self::picker('portfolioItems', PortfolioItem::class, 'title', [HomeSectionType::Portfolio], 'Projects'),
                ]),

            self::content(HomeSectionType::Hero, [
                self::bilingual('badge', 'Badge'),
                self::bilingual('headline', 'Headline', textarea: true),
                self::bilingual('text', 'Text', textarea: true),
                self::bilingual('primary_label', 'Primary button label'),
                TextInput::make('primary_url')->label('Primary button link')->placeholder('/shop'),
                self::bilingual('secondary_label', 'Secondary button label'),
                TextInput::make('secondary_url')->label('Secondary button link')->placeholder('/contact'),
                Repeater::make('slides')
                    ->label('Slides (used by the "Full-width slider" design)')
                    ->collapsible()->collapsed()->itemLabel(fn (array $state): ?string => $state['title']['en'] ?? null)
                    ->schema([
                        FileUpload::make('image')->image()->disk('public')->directory('home')->visibility('public')->imageEditor(),
                        self::bilingual('title', 'Title'),
                        self::bilingual('subtitle', 'Subtitle', textarea: true),
                        self::bilingual('button_label', 'Button label'),
                        TextInput::make('button_url')->label('Button link'),
                    ]),
            ]),

            self::content(HomeSectionType::Slider, [
                Grid::make(2)->schema([
                    Toggle::make('autoplay')->label('Play automatically')->default(true)->inline(false),
                    TextInput::make('interval')->label('Seconds per slide')->numeric()->integer()->minValue(2)->maxValue(20)->default(6),
                ]),
                Repeater::make('slides')
                    ->label('Slides')
                    ->collapsible()->itemLabel(fn (array $state): ?string => $state['title']['en'] ?? null)
                    ->addActionLabel(__('Add slide'))
                    ->reorderable()
                    ->schema([
                        FileUpload::make('image')->label('Background picture')->image()->disk('public')->directory('home')->visibility('public')->imageEditor()
                            ->helperText(__('Wide picture (about 1920×800) shown behind the text.')),
                        FileUpload::make('product_image')->label('Product picture')->image()->disk('public')->directory('home')->visibility('public')->imageEditor()
                            ->helperText(__('Product on a transparent background, used by the "Product showcase" design.')),
                        self::bilingual('badge', 'Badge'),
                        self::bilingual('title', 'Title'),
                        self::bilingual('subtitle', 'Subtitle', textarea: true),
                        self::bilingual('button_label', 'Button label'),
                        TextInput::make('button_url')->label('Button link')->placeholder('/shop'),
                    ]),
            ]),

            self::content(HomeSectionType::Features, [
                self::repeater('items', 'Features', [
                    self::iconSelect(),
                    self::bilingual('title', 'Title'),
                    self::bilingual('text', 'Text', textarea: true),
                ]),
            ]),

            self::content(HomeSectionType::Services, [
                self::repeater('items', 'Services', [
                    self::iconSelect(),
                    self::bilingual('title', 'Title'),
                    self::bilingual('text', 'Text', textarea: true),
                    self::bilingual('subject', 'Contact form subject'),
                ]),
            ]),

            self::content(HomeSectionType::Process, [
                self::repeater('items', 'Steps', [
                    self::bilingual('title', 'Title'),
                    self::bilingual('text', 'Text', textarea: true),
                ]),
            ]),

            self::content(HomeSectionType::Counters, [
                self::repeater('items', 'Counters', [
                    TextInput::make('value')->required()->placeholder('120'),
                    TextInput::make('suffix')->placeholder('+'),
                    self::bilingual('label', 'Label'),
                ]),
            ]),

            self::content(HomeSectionType::Testimonials, [
                self::repeater('items', 'Testimonials', [
                    TextInput::make('name')->required(),
                    self::bilingual('role', 'Role / company'),
                    self::bilingual('quote', 'Quote', textarea: true),
                    FileUpload::make('image')->label('Photo')->image()->avatar()->disk('public')->directory('home')->visibility('public'),
                ]),
            ]),

            self::content(HomeSectionType::Banners, [
                self::repeater('items', 'Banners', [
                    FileUpload::make('image')->image()->disk('public')->directory('home')->visibility('public')->imageEditor(),
                    self::bilingual('title', 'Title'),
                    self::bilingual('text', 'Text'),
                    self::bilingual('button_label', 'Button label'),
                    TextInput::make('url')->label('Link')->placeholder('/offers'),
                ]),
            ]),

            self::content(HomeSectionType::Cta, [
                self::bilingual('title', 'Title'),
                self::bilingual('text', 'Text', textarea: true),
                self::bilingual('primary_label', 'Primary button label'),
                TextInput::make('primary_url')->label('Primary button link')->placeholder('/contact'),
                self::bilingual('secondary_label', 'Secondary button label'),
                TextInput::make('secondary_url')->label('Secondary button link'),
                Toggle::make('show_whatsapp')->label('Show the WhatsApp button')->default(true),
            ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Appearance'))
                    ->icon(Heroicon::OutlinedSwatch)
                    ->schema([
                        ColorPicker::make('background_color')
                            ->helperText(__('Optional — leave empty for the design default.')),
                    ]),
                Section::make(__('Publishing'))
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        Toggle::make('is_active')->label('Visible on the storefront')->default(true)
                            ->helperText(__('Shown on the storefront once this is on.')),
                        TextInput::make('sort_order')->numeric()->default(0)
                            ->helperText(__('Lower numbers appear first. You can also drag rows in the list.')),
                    ]),
            ]),
        ]);
    }

    // -- helpers --------------------------------------------------------------

    /**
     * @return array<string, array{label: string, image: string}> design key => label + wireframe preview
     */
    public static function designOptions(?HomeSectionType $type): array
    {
        if (! $type) {
            return [];
        }

        return collect($type->designs())
            ->map(fn (string $label, string $key) => ['label' => $label, 'image' => DesignPreview::url($type, $key)])
            ->all();
    }

    public static function type(mixed $state): ?HomeSectionType
    {
        return $state instanceof HomeSectionType ? $state : (is_string($state) ? HomeSectionType::tryFrom($state) : null);
    }

    public static function showType(mixed $state): ?SectionShowType
    {
        return $state instanceof SectionShowType ? $state : (is_string($state) ? SectionShowType::tryFrom($state) : null);
    }

    /** A content group that only appears for one section type, stored under `content`. */
    private static function content(HomeSectionType $type, array $components): Section
    {
        return Section::make(__(':type content', ['type' => $type->label()]))
            ->visible(fn (Get $get): bool => self::type($get('type')) === $type)
            ->schema([
                Group::make($components)->statePath('content')->columns(1),
            ]);
    }

    /** Two inputs, one per storefront language, stored as {en, ar}. */
    private static function bilingual(string $name, string $label, bool $textarea = false): Grid
    {
        $make = fn (string $locale) => ($textarea ? Textarea::make("{$name}.{$locale}")->rows(2) : TextInput::make("{$name}.{$locale}"))
            ->label($label.' ('.strtoupper($locale).')')
            ->extraInputAttributes($locale === 'ar' ? ['dir' => 'rtl'] : []);

        return Grid::make(2)->schema([$make('en'), $make('ar')]);
    }

    private static function iconSelect(): Select
    {
        return Select::make('icon')->options(self::icons())->default('sun')->required();
    }

    private static function repeater(string $name, string $label, array $schema): Repeater
    {
        return Repeater::make($name)->label($label)
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => $state['title']['en'] ?? $state['name'] ?? $state['value'] ?? null)
            ->addActionLabel(__('Add item'))
            ->reorderable()
            ->schema($schema);
    }

    /**
     * Multi-select of hand-picked records, shown only for the given section
     * types when the source is "Hand-picked items".
     *
     * @param  array<int, HomeSectionType>  $types
     */
    private static function picker(string $relationship, string $model, string $title, array $types, ?string $label = null): Select
    {
        return Select::make($relationship)
            ->label(__($label ?? ucfirst($relationship)))
            ->relationship($relationship, $title)
            ->getOptionLabelFromRecordUsing(fn ($record) => $record->{$title})
            ->multiple()
            ->searchable()
            ->preload()
            ->columnSpanFull()
            ->visible(fn (Get $get): bool => in_array(self::type($get('type')), $types, true)
                && self::showType($get('show_type')) === SectionShowType::Custom);
    }
}
