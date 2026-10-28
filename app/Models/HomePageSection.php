<?php

namespace App\Models;

use App\Enums\Background3dModel;
use App\Enums\HomeSectionType;
use App\Enums\SectionShowType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * One configurable block on the storefront home page.
 *
 * `type` decides *what* the block shows, `design` decides *how* it looks
 * (see HomeSectionType::designs()), `show_type` decides *which* records fill it
 * (latest / featured / hand-picked) and `sort_order` its position on the page.
 */
class HomePageSection extends Model
{
    use HasFactory, HasTranslations;

    protected $guarded = ['id'];

    public array $translatable = ['title', 'subtitle'];

    protected function casts(): array
    {
        return [
            'type' => HomeSectionType::class,
            'show_type' => SectionShowType::class,
            'content' => 'array',
            'is_active' => 'bool',
            'limit' => 'int',
            'sort_order' => 'int',
        ];
    }

    /** "none" / blank from the admin form means: no 3D model in this section. */
    public function setModel3dAttribute(?string $value): void
    {
        $this->attributes['model_3d'] = in_array($value, [null, '', 'none'], true) ? null : $value;
    }

    /**
     * The decorative 3D model of this section for the storefront, or null.
     *
     * @return array{model: string, url: string|null, side: string}|null
     */
    public function model3dPayload(): ?array
    {
        $side = in_array($this->model_3d_side, ['start', 'end'], true) ? $this->model_3d_side : 'end';

        // a model of the equipment library ("library:{slug}") is served like an uploaded .glb
        if (ThreeDModel::slugFromKey($this->model_3d)) {
            $library = ThreeDModel::fromKey($this->model_3d);

            return $library ? ['model' => Background3dModel::Custom->value, 'url' => $library->fileUrl(), 'side' => $side] : null;
        }

        $model = Background3dModel::tryFrom((string) $this->model_3d);

        if (! $model) {
            return null;
        }

        $url = null;

        if ($model === Background3dModel::Custom) {
            if (blank($this->model_3d_file)) {
                return null; // chosen "my own model" but never uploaded one
            }

            $url = Storage::disk('public')->url($this->model_3d_file);
        }

        return [
            'model' => $model->value,
            'url' => $url,
            'side' => $side,
        ];
    }

    // -- hand-picked items ----------------------------------------------------

    public function products(): MorphToMany
    {
        return $this->picked(Product::class);
    }

    public function categories(): MorphToMany
    {
        return $this->picked(Category::class);
    }

    public function brands(): MorphToMany
    {
        return $this->picked(Brand::class);
    }

    public function bundles(): MorphToMany
    {
        return $this->picked(Bundle::class);
    }

    public function portfolioItems(): MorphToMany
    {
        return $this->picked(PortfolioItem::class);
    }

    private function picked(string $related): MorphToMany
    {
        return $this->morphedByMany($related, 'itemable', 'home_page_section_items')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    // -- scopes ---------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // -- storefront payload ---------------------------------------------------

    public function designKey(): string
    {
        $designs = $this->type->designs();

        return array_key_exists((string) $this->design, $designs) ? $this->design : $this->type->defaultDesign();
    }

    /** Everything the Vue home page needs to render this block. */
    public function toStorefront(): array
    {
        $type = $this->type;

        $payload = [
            'id' => $this->id,
            'type' => $type->value,
            'design' => $this->designKey(),
            'title' => $this->getTranslations('title') ?: null,
            'subtitle' => $this->getTranslations('subtitle') ?: null,
            'background_color' => $this->background_color,
            'model_3d' => $this->model3dPayload(),
            'content' => self::publicUrls($this->content ?? []),
            'items' => $type->usesItems() ? $this->resolveItems()->values() : [],
        ];

        if ($type === HomeSectionType::Hero) {
            $payload['stats'] = [
                'products' => Product::where('is_active', true)->count(),
                'brands' => Brand::where('is_active', true)->count(),
                'projects' => PortfolioItem::where('is_active', true)->count(),
            ];
        }

        return $payload;
    }

    /** @return Collection<int, Model> */
    public function resolveItems(): Collection
    {
        $type = $this->type;
        $limit = max(1, (int) $this->limit);
        $showType = $this->show_type ?? SectionShowType::Latest;

        if ($showType === SectionShowType::Custom && $this->exists && $type->relation()) {
            return $this->{$type->relation()}()
                ->with($this->eagerLoads())
                ->where($this->itemTable().'.is_active', true)
                ->limit($limit)
                ->get();
        }

        $model = $type->itemModel();
        $query = $model::query()->with($this->eagerLoads())->where('is_active', true);

        match ($type) {
            HomeSectionType::Categories => $query->whereNull('parent_id')->withCount(['products' => fn ($q) => $q->where('is_active', true)])->orderBy('sort_order'),
            HomeSectionType::Brands => $query->withCount(['products' => fn ($q) => $q->where('is_active', true)])->orderBy('sort_order'),
            HomeSectionType::Portfolio => $query->orderByDesc('is_featured')->orderBy('sort_order'),
            HomeSectionType::Bundles => $query
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->orderBy('sort_order'),
            default => $query->latest('id'),
        };

        if ($showType === SectionShowType::Featured && $type->supportsFeatured()) {
            // Featured items lead; any free slots are filled with the newest visible ones, so an item
            // switched on in the admin never disappears just because nothing else is featured.
            $featured = (clone $query)->where('is_featured', true)->limit($limit)->get();

            if ($featured->count() >= $limit) {
                return $featured;
            }

            $rest = (clone $query)
                ->whereNotIn((new $model)->getQualifiedKeyName(), $featured->modelKeys())
                ->limit($limit - $featured->count())
                ->get();

            return $featured->concat($rest)->values();
        }

        return $query->limit($limit)->get();
    }

    private function itemTable(): string
    {
        return (new ($this->type->itemModel()))->getTable();
    }

    private function eagerLoads(): array
    {
        return match ($this->type) {
            HomeSectionType::Hero, HomeSectionType::Products => ['brand', 'categories', 'media'],
            HomeSectionType::Bundles => ['media', 'products.media'],
            default => ['media'],
        };
    }

    /**
     * Uploaded files are stored as disk paths; hand the browser real URLs.
     * Any `image` / `product_image` key (at any depth) is converted, absolute URLs pass through.
     */
    public static function publicUrls(array $content): array
    {
        foreach ($content as $key => $value) {
            if (is_array($value)) {
                $content[$key] = self::publicUrls($value);
            } elseif (in_array($key, ['image', 'product_image'], true) && is_string($value) && $value !== '' && ! preg_match('#^(https?:)?//|^/#', $value)) {
                $content[$key] = Storage::disk('public')->url($value);
            }
        }

        return $content;
    }

    // -- built-in layout ------------------------------------------------------

    /**
     * The home page used until an admin creates their own sections.
     * Also what DatabaseSeeder persists, so the admin starts from this layout.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaults(): array
    {
        $t = fn (string $en, string $ar) => ['en' => $en, 'ar' => $ar];

        return [
            [
                'type' => HomeSectionType::Hero, 'design' => 'split', 'model_3d' => 'solar-panel', 'show_type' => SectionShowType::Featured, 'limit' => 3,
                'content' => [
                    'badge' => $t('Supply & installation', 'توريد وتركيب'),
                    'headline' => $t('Inverters, lithium batteries and protection — supplied and installed.', 'انفرترات وبطاريات ليثيوم وحماية كهربائية — توريد وتركيب.'),
                    'text' => $t(
                        'Shop inverters, lithium batteries, solar charge controllers and breakers from leading brands — or let our team design and install the whole system.',
                        'تسوّق الانفرترات وبطاريات الليثيوم ومنظمات الشحن الشمسية والقواطع من أفضل العلامات — أو اترك لفريقنا تصميم وتركيب النظام كاملًا.'
                    ),
                    'primary_label' => $t('Browse products', 'تصفّح المنتجات'),
                    'primary_url' => '/shop',
                    'secondary_label' => $t('Request a site survey', 'اطلب معاينة للموقع'),
                    'secondary_url' => '/contact',
                ],
            ],
            [
                'type' => HomeSectionType::Slider, 'design' => 'product',
                'content' => ['autoplay' => true, 'interval' => 6, 'slides' => [
                    [
                        'badge' => $t('Hybrid inverters', 'انفرترات هجينة'), 'image' => null, 'product_image' => null,
                        'title' => $t('Power your home day and night', 'كهرباء لمنزلك ليلًا ونهارًا'),
                        'subtitle' => $t('Hybrid inverters from Voltronic, Felicity and Solis with MPPT charging and battery backup.', 'انفرترات هجينة من فواترونيك وفلاستي وسوليز مع شاحن MPPT ودعم البطاريات.'),
                        'button_label' => $t('Shop inverters', 'تسوّق الانفرترات'), 'button_url' => '/shop',
                    ],
                    [
                        'badge' => $t('Lithium batteries', 'بطاريات الليثيوم'), 'image' => null, 'product_image' => null,
                        'title' => $t('LiFePO4 storage that lasts 6,000 cycles', 'تخزين LiFePO4 يدوم ٦٠٠٠ دورة'),
                        'subtitle' => $t('Rack and wall batteries from Tobac, Felicity, Pylontech and Dyness.', 'بطاريات رف وجدارية من توباك وفلاستي وبايلونتك وداينس.'),
                        'button_label' => $t('View batteries', 'شاهد البطاريات'), 'button_url' => '/shop',
                    ],
                    [
                        'badge' => $t('Protection', 'الحماية'), 'image' => null, 'product_image' => null,
                        'title' => $t('Breakers for every circuit', 'قواطع لكل دائرة'),
                        'subtitle' => $t('MCB, DC, MCCB and shunt-trip breakers — sized and supplied for your system.', 'قواطع MCB وDC وMCCB وقواطع الشنت — بالمقاس المناسب لنظامك.'),
                        'button_label' => $t('Talk to an engineer', 'تحدّث مع مهندس'), 'button_url' => '/contact',
                    ],
                ]],
            ],
            [
                'type' => HomeSectionType::Features, 'design' => 'row',
                'content' => ['items' => [
                    ['icon' => 'sun', 'title' => $t('Solar & storage', 'الطاقة الشمسية والتخزين'), 'text' => $t('Panels, lithium batteries and inverters sized to your load.', 'ألواح وبطاريات ليثيوم وانفرترات مناسبة لحِملك الكهربائي.')],
                    ['icon' => 'factory', 'title' => $t('Industrial equipment', 'المعدات الصناعية'), 'text' => $t('Control gear and power equipment for plants and sites.', 'معدات تحكم وطاقة للمصانع والمواقع.')],
                    ['icon' => 'cctv', 'title' => $t('Security systems', 'أنظمة الأمان'), 'text' => $t('Surveillance cameras and alarm systems, supplied and set up.', 'كاميرات مراقبة وأنظمة إنذار، توريد وتركيب.')],
                    ['icon' => 'wrench', 'title' => $t('Our own installers', 'فريق تركيب خاص بنا'), 'text' => $t('The same team supplies, installs, commissions and supports.', 'الفريق نفسه يورّد ويركّب ويشغّل ويدعم.')],
                ]],
            ],
            [
                'type' => HomeSectionType::Categories, 'design' => 'tiles', 'model_3d' => 'mixed', 'show_type' => SectionShowType::Latest, 'limit' => 8,
                'title' => $t('Shop by category', 'تسوّق حسب الفئة'), 'subtitle' => null,
            ],
            [
                'type' => HomeSectionType::Services, 'design' => 'cards', 'model_3d' => 'control-panel',
                'title' => $t('We install what we sell', 'نركّب ما نبيعه'),
                'subtitle' => $t('A dedicated in-house team handles high-capacity systems from first survey to final commissioning.', 'فريق متخصص يتولى الأنظمة عالية السعة من المعاينة الأولى حتى التشغيل النهائي.'),
                'content' => ['items' => [
                    ['icon' => 'sun', 'title' => $t('Solar power systems', 'أنظمة الطاقة الشمسية'), 'text' => $t('High-capacity on-grid, off-grid and hybrid solar plants — from design and mounting to grid connection.', 'محطات شمسية عالية السعة (متصلة بالشبكة، منفصلة، هجينة) — من التصميم والتثبيت حتى الربط بالشبكة.'), 'subject' => $t('Solar power system installation', 'تركيب نظام طاقة شمسية')],
                    ['icon' => 'cpu', 'title' => $t('Industrial control panels', 'لوحات التحكم الصناعية'), 'text' => $t('Custom-built motor control, distribution and automation panels, wired, tested and commissioned.', 'لوحات تحكم بالمحركات وتوزيع وأتمتة مصنّعة حسب الطلب، مع التمديد والاختبار والتشغيل.'), 'subject' => $t('Industrial control panel', 'لوحة تحكم صناعية')],
                    ['icon' => 'zap', 'title' => $t('Inverters & power systems', 'الانفرترات وأنظمة الطاقة'), 'text' => $t('Power inverters, UPS and lithium battery storage installed for homes, farms and factories.', 'تركيب انفرترات وUPS وتخزين بطاريات الليثيوم للمنازل والمزارع والمصانع.'), 'subject' => $t('Inverter / battery storage installation', 'تركيب انفرتر / تخزين بطاريات')],
                    ['icon' => 'bell', 'title' => $t('Surveillance & alarm systems', 'أنظمة المراقبة والإنذار'), 'text' => $t('IP camera networks, access control and intrusion alarms designed for complete site coverage.', 'شبكات كاميرات IP وتحكم بالدخول وإنذار اقتحام بتغطية كاملة للموقع.'), 'subject' => $t('Surveillance / alarm system installation', 'تركيب نظام مراقبة / إنذار')],
                ]],
            ],
            [
                'type' => HomeSectionType::Process, 'design' => 'grid', 'model_3d' => 'wind-turbine', 'model_3d_side' => 'start',
                'title' => $t('How we work', 'كيف نعمل'),
                'subtitle' => $t('From the first visit to after-sales support — four clear steps.', 'من الزيارة الأولى إلى دعم ما بعد البيع — أربع خطوات واضحة.'),
                'content' => ['items' => [
                    ['title' => $t('Site survey', 'معاينة الموقع'), 'text' => $t('We visit, measure the load and the site, and recommend the right system.', 'نزور الموقع ونقيس الحِمل ونوصي بالنظام المناسب.')],
                    ['title' => $t('Quotation', 'عرض السعر'), 'text' => $t('A clear, itemised quote covering equipment, installation and warranty.', 'عرض واضح ومفصّل يشمل المعدات والتركيب والضمان.')],
                    ['title' => $t('Installation', 'التركيب'), 'text' => $t('Our certified team installs, tests and commissions on schedule.', 'يقوم فريقنا المعتمد بالتركيب والاختبار والتشغيل في الموعد.')],
                    ['title' => $t('Support', 'الدعم'), 'text' => $t('Training, handover documents and after-sales maintenance.', 'تدريب ووثائق تسليم وصيانة ما بعد البيع.')],
                ]],
            ],
            [
                'type' => HomeSectionType::Banners, 'design' => 'split',
                'content' => ['items' => [
                    ['title' => $t('Home Hybrid Backup Kit 5 kW', 'طقم احتياطي منزلي هجين ٥ كيلوواط'), 'text' => $t('Save up to 20% on the full package', 'وفّر حتى ٢٠٪ على الحزمة الكاملة'), 'button_label' => $t('View offer', 'شاهد العرض'), 'url' => '/offers', 'image' => null],
                    ['title' => $t('Free site survey', 'معاينة مجانية للموقع'), 'text' => $t('Book a visit from our engineers', 'احجز زيارة من مهندسينا'), 'button_label' => $t('Book now', 'احجز الآن'), 'url' => '/contact', 'image' => null],
                ]],
            ],
            [
                'type' => HomeSectionType::Counters, 'design' => 'band',
                'content' => ['items' => [
                    ['value' => '120', 'suffix' => '+', 'label' => $t('Projects delivered', 'مشروع منجز')],
                    ['value' => '8', 'suffix' => ' MW', 'label' => $t('Solar capacity installed', 'قدرة شمسية مركّبة')],
                    ['value' => '1,500', 'suffix' => '+', 'label' => $t('Cameras installed', 'كاميرا مركّبة')],
                    ['value' => '15', 'suffix' => '', 'label' => $t('Years of experience', 'سنة خبرة')],
                ]],
            ],
            [
                'type' => HomeSectionType::Products, 'design' => 'grid', 'model_3d' => 'battery', 'show_type' => SectionShowType::Featured, 'limit' => 8,
                'title' => $t('Popular right now', 'الأكثر طلبًا الآن'),
                'subtitle' => $t('Hand-picked products our customers ask for most.', 'منتجات مختارة بعناية يطلبها عملاؤنا أكثر من غيرها.'),
            ],
            [
                'type' => HomeSectionType::Bundles, 'design' => 'band', 'model_3d' => 'solar-panel', 'show_type' => SectionShowType::Latest, 'limit' => 3,
                'title' => $t('Bundles that save you more', 'حزم توفّر عليك أكثر'),
                'subtitle' => $t('Everything you need in one package, priced below buying each item separately.', 'كل ما تحتاجه في حزمة واحدة بسعر أقل من شراء كل عنصر على حدة.'),
            ],
            [
                'type' => HomeSectionType::Brands, 'design' => 'logos', 'model_3d' => 'inverter', 'model_3d_side' => 'start', 'show_type' => SectionShowType::Latest, 'limit' => 6,
                'title' => $t('Brands we carry', 'العلامات التي نوفّرها'),
            ],
            [
                'type' => HomeSectionType::Portfolio, 'design' => 'grid', 'model_3d' => 'wind-turbine', 'show_type' => SectionShowType::Latest, 'limit' => 3,
                'title' => $t('Work we are proud of', 'أعمال نفخر بها'),
                'subtitle' => $t('A selection of recent projects delivered for our clients.', 'مختارات من مشاريعنا الأخيرة لعملائنا.'),
            ],
            [
                'type' => HomeSectionType::Testimonials, 'design' => 'cards', 'model_3d' => 'battery',
                'title' => $t('What our clients say', 'ماذا يقول عملاؤنا'),
                'content' => ['items' => [
                    ['name' => 'Khalid Al-Otaibi', 'role' => $t('Operations Manager, Najd Logistics', 'مدير العمليات، نجد للخدمات اللوجستية'), 'quote' => $t('The 2 MW rooftop plant was commissioned ahead of schedule and our energy bill dropped by more than half.', 'تم تشغيل المحطة الشمسية ٢ ميجاوات قبل الموعد وانخفضت فاتورة الطاقة لدينا بأكثر من النصف.')],
                    ['name' => 'Dr. Sara Al-Harbi', 'role' => $t('Al-Shifa Clinic', 'عيادة الشفاء'), 'quote' => $t('Hybrid solar with lithium storage means our critical equipment never goes down during outages.', 'الطاقة الشمسية الهجينة مع تخزين الليثيوم تعني ألا تتوقف أجهزتنا الحرجة أثناء الانقطاع.')],
                    ['name' => 'Omar Haddad', 'role' => $t('Plant Engineer, Eastern Packaging', 'مهندس مصنع، الشرقية للتغليف'), 'quote' => $t('Their control panels were installed and commissioned in a week — and support answers the phone.', 'تم تركيب لوحات التحكم وتشغيلها خلال أسبوع — وفريق الدعم يرد على الهاتف فعلًا.')],
                ]],
            ],
            [
                'type' => HomeSectionType::Cta, 'design' => 'primary', 'model_3d' => 'solar-panel',
                'content' => [
                    'title' => $t('Planning a solar, power or security project?', 'تخطّط لمشروع طاقة شمسية أو كهرباء أو أمان؟'),
                    'text' => $t('Tell us about your site and load — we will survey, quote and install.', 'أخبرنا عن موقعك وحِملك — ونتولى المعاينة والتسعير والتركيب.'),
                    'primary_label' => $t('Request a site survey', 'اطلب معاينة للموقع'),
                    'primary_url' => '/contact',
                    'show_whatsapp' => true,
                ],
            ],
        ];
    }

    /**
     * Saves the default layout so it can be edited in Filament. Safe to repeat: it only adds the
     * section types that are missing, and gives sections nobody has touched yet their sample 3D model.
     *
     * @return int how many sections were created
     */
    public static function seedDefaults(): int
    {
        $existing = static::query()->get()->groupBy(fn (self $section) => $section->type->value);
        $fresh = $existing->isEmpty();
        $order = (int) static::query()->max('sort_order');
        $created = 0;

        foreach (self::defaults() as $i => $attributes) {
            $sections = $existing->get($attributes['type']->value);

            if ($sections === null) {
                static::create($attributes + ['sort_order' => $fresh ? $i : ++$order]);
                $created++;

                continue;
            }

            $model = Arr::only($attributes, ['model_3d', 'model_3d_side']);

            foreach ($sections as $section) {
                if ($model && blank($section->model_3d) && $section->created_at?->eq($section->updated_at)) {
                    $section->update($model);
                }
            }
        }

        return $created;
    }

    /** Unsaved models for the built-in layout (used when the table is empty). */
    public static function builtIn(): \Illuminate\Support\Collection
    {
        return collect(self::defaults())->map(function (array $attributes, int $i) {
            return (new self())->forceFill($attributes + [
                'sort_order' => $i,
                'is_active' => true,
                'show_type' => SectionShowType::Latest,
                'limit' => 8,
            ]);
        });
    }
}
