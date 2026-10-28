<?php

namespace App\Support;

use App\Enums\HomeSectionType;
use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\HomePageSection;
use App\Models\PortfolioItem;
use App\Models\Product;
use App\Models\ThreeDModel;
use App\Settings\SiteSettings;
use App\Support\Glb\Photo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

/**
 * Realistic Al-Nasr demo content: solar, lithium batteries, inverters,
 * industrial equipment, surveillance and alarms — bilingual, with generated images.
 *
 * Re-running is safe: records are matched by SKU / slug and updated, never duplicated.
 * Used by `php artisan demo:seed`.
 */
class DemoData
{
    public function __construct(private readonly bool $withImages = true)
    {
    }

    /**
     * @return array<string, int> row counts after seeding
     */
    public function run(int $extraProducts = 0, int $messages = 12): array
    {
        $brands = $this->brands();
        $categories = $this->categories();
        $products = $this->products($brands, $categories);

        $this->extraProducts($extraProducts, $brands, $categories);
        $this->bundles($products);
        $this->portfolio();
        $this->messages($messages);
        $this->homeSections();
        $this->settings();

        return $this->counts();
    }

    /** Removes catalogue, content and inbox rows (users, roles and settings are kept). */
    public function wipe(): void
    {
        HomePageSection::query()->delete();

        foreach ([Bundle::class, Product::class, Category::class, Brand::class, PortfolioItem::class] as $model) {
            // one by one so media-library removes the files too
            $model::query()->get()->each->delete();
        }

        ContactMessage::query()->delete();
    }

    /** @return array<string, int> */
    public function counts(): array
    {
        return [
            'brands' => Brand::count(),
            'categories' => Category::count(),
            'products' => Product::count(),
            'bundles' => Bundle::count(),
            'portfolio' => PortfolioItem::count(),
            'messages' => ContactMessage::count(),
            'home sections' => HomePageSection::count(),
            '3D models' => ThreeDModel::count(),
        ];
    }

    // -- catalogue ---------------------------------------------------------------

    /** @return array<string, Brand> keyed by slug */
    private function brands(): array
    {
        $rows = [
            'voltronic' => ['Voltronic', 'فواترونيك', 'Axpert hybrid and off-grid inverters and MPPT solar charge controllers.', 'انفرترات Axpert الهجينة والمنفصلة ومنظمات شحن شمسية MPPT.'],
            'felicity' => ['Felicity', 'فلاستي', 'Inverters, wall-mount lithium batteries and charge controllers for homes and shops.', 'انفرترات وبطاريات ليثيوم جدارية ومنظمات شحن للمنازل والمحلات.'],
            'solis' => ['Solis', 'سوليز', 'Grid-tie and hybrid string inverters for homes, farms and commercial roofs.', 'انفرترات سلاسل متصلة بالشبكة وهجينة للمنازل والمزارع والأسطح التجارية.'],
            'growatt' => ['Growatt', 'جروات', 'Off-grid and hybrid inverters with built-in solar chargers.', 'انفرترات منفصلة وهجينة مع شواحن شمسية مدمجة.'],
            'deye' => ['Deye', 'ديي', 'Single and three-phase hybrid inverters for large storage systems.', 'انفرترات هجينة أحادية وثلاثية الأطوار لأنظمة التخزين الكبيرة.'],
            'tobac' => ['Tobac', 'توباك', 'LiFePO4 lithium batteries — rack-mount modules and battery cabinets.', 'بطاريات ليثيوم فوسفات الحديد — وحدات راك وخزائن بطاريات.'],
            'pylontech' => ['Pylontech', 'بايلونتك', 'Modular rack-mount lithium storage with built-in BMS.', 'تخزين ليثيوم راك معياري مع نظام BMS مدمج.'],
            'dyness' => ['Dyness', 'داينس', 'Wall-mount and stackable lithium batteries for home storage.', 'بطاريات ليثيوم جدارية وقابلة للتكديس للتخزين المنزلي.'],
            'epever' => ['EPever', 'إي بي إيفر', 'MPPT and PWM solar charge controllers.', 'منظمات شحن شمسية MPPT وPWM.'],
            'victron' => ['Victron Energy', 'فيكترون', 'SmartSolar MPPT charge controllers with Bluetooth monitoring.', 'منظمات شحن SmartSolar MPPT مع مراقبة بلوتوث.'],
            'alnasr-electric' => ['Al-Nasr Electric', 'النصر للكهرباء', 'AC and DC circuit breakers (MCB, MCCB) and shunt-trip breakers.', 'قواطع تيار متردد ومستمر (MCB وMCCB) وقواطع الشنت.'],
        ];

        $brands = [];
        $order = 0;

        foreach ($rows as $slug => [$en, $ar, $descEn, $descAr]) {
            $brands[$slug] = $this->upsert(Brand::class, $slug, [
                'name' => ['en' => $en, 'ar' => $ar],
                'description' => ['en' => $descEn, 'ar' => $descAr],
                'is_active' => true,
                'sort_order' => $order++,
            ]);

            $this->attachImage($brands[$slug], 'logo', "brand-logo-{$slug}", 600, 400);
            $this->attachImage($brands[$slug], 'cover', "brand-cover-{$slug}", 1400, 500);
        }

        return $brands;
    }

    /** @return array<string, Category> keyed by slug */
    private function categories(): array
    {
        $rows = [
            // slug => [en, ar, parent slug|null]
            'inverters' => ['Inverters', 'الانفرترات', null],
            'hybrid-inverters' => ['Hybrid Inverters', 'انفرترات هجينة', 'inverters'],
            'off-grid-inverters' => ['Off-grid Inverters', 'انفرترات منفصلة', 'inverters'],
            'grid-tie-inverters' => ['Grid-tie Inverters', 'انفرترات متصلة بالشبكة', 'inverters'],
            'lithium-batteries' => ['Lithium Batteries', 'بطاريات الليثيوم', null],
            'rack-batteries' => ['Rack-mount Batteries', 'بطاريات راك', 'lithium-batteries'],
            'wall-batteries' => ['Wall-mount Batteries', 'بطاريات جدارية', 'lithium-batteries'],
            'charge-controllers' => ['Solar Charge Controllers', 'منظمات الشحن الشمسية', null],
            'mppt-controllers' => ['MPPT Controllers', 'منظمات MPPT', 'charge-controllers'],
            'pwm-controllers' => ['PWM Controllers', 'منظمات PWM', 'charge-controllers'],
            'breakers' => ['Circuit Breakers', 'القواطع', null],
            'mcb-breakers' => ['MCB Breakers', 'قواطع MCB', 'breakers'],
            'dc-breakers' => ['DC Breakers', 'قواطع التيار المستمر', 'breakers'],
            'mccb-breakers' => ['MCCB Breakers', 'قواطع MCCB', 'breakers'],
            'shunt-trip-breakers' => ['Shunt-trip Breakers', 'قواطع الشنت', 'breakers'],
        ];

        $categories = [];
        $order = 0;

        foreach ($rows as $slug => [$en, $ar, $parent]) {
            $categories[$slug] = $this->upsert(Category::class, $slug, [
                'name' => ['en' => $en, 'ar' => $ar],
                'description' => null,
                'parent_id' => $parent ? $categories[$parent]->id : null,
                'is_active' => true,
                'sort_order' => $order++,
            ]);

            $this->attachImage($categories[$slug], 'image', "category-{$slug}", 800, 640);
            $this->attachImage($categories[$slug], 'banner', "category-banner-{$slug}", 1600, 420);
        }

        return $categories;
    }

    /**
     * @param  array<string, Brand>  $brands
     * @param  array<string, Category>  $categories
     * @return array<string, Product> keyed by SKU
     */
    private function products(array $brands, array $categories): array
    {
        // sku, name en, name ar, short en, short ar, brand, categories, price, compare, stock, featured
        $rows = [
            // inverters
            ['VLT-AXK5', 'Voltronic Axpert King 5kW Hybrid Inverter', 'انفرتر فواترونيك Axpert King هجين ٥ كيلوواط', 'MPPT hybrid inverter, 48V battery, parallel-ready, Wi-Fi monitoring.', 'انفرتر هجين MPPT، بطارية ٤٨ فولت، يدعم التوصيل على التوازي، مراقبة Wi-Fi.', 'voltronic', ['hybrid-inverters'], 690, 760, 24, true],
            ['VLT-MKS3', 'Voltronic Axpert MKS 3kW Off-grid Inverter 24V', 'انفرتر فواترونيك Axpert MKS منفصل ٣ كيلوواط ٢٤ فولت', 'Pure sine wave with built-in MPPT charger, LCD display.', 'موجة جيبية نقية مع شاحن MPPT مدمج وشاشة LCD.', 'voltronic', ['off-grid-inverters'], 420, null, 31, false],
            ['VLT-MKS5', 'Voltronic Axpert MKS 5.5kW Off-grid Inverter 48V', 'انفرتر فواترونيك Axpert MKS منفصل ٥٫٥ كيلوواط ٤٨ فولت', 'Pure sine wave, 80A MPPT charger, up to 6 units in parallel.', 'موجة جيبية نقية، شاحن MPPT ٨٠ أمبير، حتى ٦ وحدات على التوازي.', 'voltronic', ['off-grid-inverters'], 640, 690, 22, false],
            ['FLC-IVEM5K', 'Felicity 5kW Hybrid Inverter 48V', 'انفرتر فلاستي هجين ٥ كيلوواط ٤٨ فولت', 'Hybrid inverter with MPPT, lithium battery ready, grid or generator input.', 'انفرتر هجين مع MPPT، جاهز لبطاريات الليثيوم، دخل شبكة أو مولد.', 'felicity', ['hybrid-inverters'], 560, null, 30, false],
            ['FLC-IVPS3K', 'Felicity 3kW Off-grid Inverter 24V', 'انفرتر فلاستي منفصل ٣ كيلوواط ٢٤ فولت', 'Pure sine wave off-grid inverter with built-in 60A MPPT charger.', 'انفرتر منفصل بموجة جيبية نقية مع شاحن MPPT مدمج ٦٠ أمبير.', 'felicity', ['off-grid-inverters'], 350, 385, 28, false],
            ['SOL-S6-5K', 'Solis 5kW Hybrid Inverter (single-phase)', 'انفرتر سوليز هجين ٥ كيلوواط أحادي الطور', 'High-voltage battery hybrid inverter, dual MPPT, backup output.', 'انفرتر هجين لبطاريات الجهد العالي، MPPT مزدوج، مخرج احتياطي.', 'solis', ['hybrid-inverters'], 1180, 1290, 14, true],
            ['SOL-S5-10K', 'Solis 10kW Grid-tie Inverter (three-phase)', 'انفرتر سوليز متصل بالشبكة ١٠ كيلوواط ثلاثي الأطوار', 'String inverter, dual MPPT, IP66, 98.6% efficiency.', 'انفرتر سلاسل، MPPT مزدوج، IP66، كفاءة ٩٨٫٦٪.', 'solis', ['grid-tie-inverters'], 1150, null, 9, false],
            ['GRW-SPF5K', 'Growatt SPF 5000 ES Off-grid Inverter', 'انفرتر جروات SPF 5000 ES منفصل', '5kW off-grid inverter with 80A MPPT charger and parallel support.', 'انفرتر منفصل ٥ كيلوواط مع شاحن MPPT ٨٠ أمبير ودعم التوازي.', 'growatt', ['off-grid-inverters'], 780, null, 12, false],
            ['DEY-SUN12K', 'Deye 12kW Hybrid Inverter (three-phase)', 'انفرتر ديي هجين ١٢ كيلوواط ثلاثي الأطوار', 'Three-phase hybrid inverter for large storage, parallel up to 10 units.', 'انفرتر هجين ثلاثي الأطوار للتخزين الكبير، توصيل حتى ١٠ وحدات.', 'deye', ['hybrid-inverters'], 2350, 2500, 6, true],
            ['DEY-SUN5K', 'Deye 5kW Hybrid Inverter (single-phase)', 'انفرتر ديي هجين ٥ كيلوواط أحادي الطور', 'Compact hybrid inverter for homes, 48V lithium battery ready.', 'انفرتر هجين مدمج للمنازل، جاهز لبطارية ليثيوم ٤٨ فولت.', 'deye', ['hybrid-inverters'], 1250, null, 15, false],

            // lithium batteries
            ['TBC-48100', 'Tobac 48V 100Ah LiFePO4 Rack Battery', 'بطارية توباك ليثيوم ٤٨ فولت ١٠٠ أمبير (راك)', '5.12 kWh rack module, 6000 cycles, built-in BMS, RS485/CAN.', 'وحدة راك ٥٫١٢ كيلوواط ساعة، ٦٠٠٠ دورة، BMS مدمج، RS485/CAN.', 'tobac', ['rack-batteries'], 1090, 1190, 16, true],
            ['TBC-48200', 'Tobac 48V 200Ah LiFePO4 Rack Battery', 'بطارية توباك ليثيوم ٤٨ فولت ٢٠٠ أمبير (راك)', '10.24 kWh rack battery for commercial storage and telecom sites.', 'بطارية راك ١٠٫٢٤ كيلوواط ساعة للتخزين التجاري ومواقع الاتصالات.', 'tobac', ['rack-batteries'], 2050, null, 8, false],
            ['TBC-24200', 'Tobac 24V 200Ah LiFePO4 Battery', 'بطارية توباك ليثيوم ٢٤ فولت ٢٠٠ أمبير', 'Deep-cycle storage for off-grid homes and farms.', 'تخزين عميق للمنازل والمزارع المنفصلة عن الشبكة.', 'tobac', ['rack-batteries'], 940, null, 9, false],
            ['TBC-CAB4', 'Tobac Battery Cabinet (4 modules)', 'خزانة بطاريات توباك (٤ وحدات)', 'Ventilated cabinet for up to 4 rack batteries with DC isolators.', 'خزانة مهوّاة تتسع لـ ٤ بطاريات راك مع فاصلات DC.', 'tobac', ['rack-batteries'], 320, null, 14, false],
            ['FLC-LFP48100', 'Felicity 48V 100Ah Wall-mount LiFePO4 Battery', 'بطارية فلاستي ليثيوم ٤٨ فولت ١٠٠ أمبير (جدارية)', 'Wall-mount 5.12 kWh battery with SOC indicator and communication ports.', 'بطارية جدارية ٥٫١٢ كيلوواط ساعة مع مؤشر شحن ومنافذ اتصال.', 'felicity', ['wall-batteries'], 1180, 1290, 12, true],
            ['FLC-LFP12100', 'Felicity 12V 100Ah LiFePO4 Battery', 'بطارية فلاستي ليثيوم ١٢ فولت ١٠٠ أمبير', 'Drop-in replacement for lead-acid batteries with Bluetooth monitoring.', 'بديل مباشر لبطاريات الرصاص مع مراقبة بلوتوث.', 'felicity', ['wall-batteries'], 290, 320, 40, false],
            ['PYL-US5000', 'Pylontech US5000 4.8kWh Rack Battery', 'بطارية بايلونتك US5000 راك ٤٫٨ كيلوواط ساعة', 'Modular 48V lithium module, stack up to 16 units.', 'وحدة ليثيوم ٤٨ فولت معيارية، تكديس حتى ١٦ وحدة.', 'pylontech', ['rack-batteries'], 1290, null, 10, false],
            ['DYN-PB51', 'Dyness Power Brick 51.2V 100Ah Battery', 'بطارية داينس Power Brick ٥١٫٢ فولت ١٠٠ أمبير', 'Wall or floor mount home battery, 5.12 kWh.', 'بطارية منزلية للتثبيت على الجدار أو الأرض، ٥٫١٢ كيلوواط ساعة.', 'dyness', ['wall-batteries'], 1240, null, 7, false],

            // solar charge controllers
            ['VLT-MPPT80', 'Voltronic MPPT 80A Solar Charge Controller 48V', 'منظم شحن شمسي فواترونيك MPPT ٨٠ أمبير ٤٨ فولت', 'MPPT charge controller with LCD, 150V PV input, RS232 monitoring.', 'منظم شحن MPPT مع شاشة LCD، دخل ألواح ١٥٠ فولت، مراقبة RS232.', 'voltronic', ['mppt-controllers'], 210, null, 40, false],
            ['FLC-MPPT60', 'Felicity MPPT 60A Solar Charge Controller', 'منظم شحن شمسي فلاستي MPPT ٦٠ أمبير', 'MPPT controller 12/24/48V auto-detect with backlit display.', 'منظم MPPT يتعرف تلقائيًا على ١٢/٢٤/٤٨ فولت مع شاشة مضاءة.', 'felicity', ['mppt-controllers'], 150, 170, 25, false],
            ['EPV-TR4210', 'EPever Tracer 4210AN MPPT 40A Controller', 'منظم شحن إي بي إيفر Tracer 4210AN MPPT ٤٠ أمبير', '100V PV input, 12/24V auto, RS485 and remote meter support.', 'دخل ألواح ١٠٠ فولت، ١٢/٢٤ فولت تلقائي، دعم RS485 وعدّاد خارجي.', 'epever', ['mppt-controllers'], 190, null, 25, false],
            ['EPV-TR6415', 'EPever Tracer 6415AN MPPT 60A Controller', 'منظم شحن إي بي إيفر Tracer 6415AN MPPT ٦٠ أمبير', '150V PV input, 12/24/36/48V auto, LCD display.', 'دخل ألواح ١٥٠ فولت، ١٢/٢٤/٣٦/٤٨ فولت تلقائي، شاشة LCD.', 'epever', ['mppt-controllers'], 330, 360, 11, false],
            ['VIC-SS10050', 'Victron SmartSolar MPPT 100/50 Controller', 'منظم شحن فيكترون SmartSolar MPPT ‏100/50', 'Bluetooth built in, ultra-fast tracking, 12/24/48V.', 'بلوتوث مدمج، تتبع فائق السرعة، ١٢/٢٤/٤٨ فولت.', 'victron', ['mppt-controllers'], 190, null, 18, false],
            ['FLC-PWM30', 'Felicity PWM 30A Solar Charge Controller 12/24V', 'منظم شحن شمسي فلاستي PWM ٣٠ أمبير ١٢/٢٤ فولت', 'Simple PWM controller with dual USB output, ideal for small systems.', 'منظم PWM بسيط مع مخرجي USB، مناسب للأنظمة الصغيرة.', 'felicity', ['pwm-controllers'], 28, null, 120, false],

            // breakers (MCB / DC / MCCB / shunt-trip)
            ['ANE-MCB1-32', 'MCB 1-Pole 32A (C curve)', 'قاطع MCB قطب واحد ٣٢ أمبير (منحنى C)', '6 kA breaking capacity, DIN-rail mount.', 'قدرة قطع ٦ كيلو أمبير، تثبيت على سكة DIN.', 'alnasr-electric', ['mcb-breakers'], 4.5, null, 400, false],
            ['ANE-MCB2-40', 'MCB 2-Pole 40A (C curve)', 'قاطع MCB قطبين ٤٠ أمبير (منحنى C)', 'Double-pole breaker for single-phase mains and inverter AC input.', 'قاطع ثنائي القطب للتغذية أحادية الطور ودخل AC للانفرتر.', 'alnasr-electric', ['mcb-breakers'], 9, null, 300, false],
            ['ANE-MCB3-63', 'MCB 3-Pole 63A (C curve)', 'قاطع MCB ثلاثة أقطاب ٦٣ أمبير (منحنى C)', 'Three-phase miniature breaker, 10 kA.', 'قاطع صغير ثلاثي الأطوار، ١٠ كيلو أمبير.', 'alnasr-electric', ['mcb-breakers'], 16, null, 180, false],
            ['ANE-MCB4-63', 'MCB 4-Pole 63A (C curve)', 'قاطع MCB أربعة أقطاب ٦٣ أمبير (منحنى C)', 'Four-pole breaker with switched neutral for three-phase installations.', 'قاطع رباعي الأقطاب مع فصل المحايد للتركيبات ثلاثية الأطوار.', 'alnasr-electric', ['mcb-breakers'], 22, null, 150, false],
            ['ANE-DCB2-63', 'DC MCB 2-Pole 63A 500VDC (solar / battery)', 'قاطع تيار مستمر DC قطبين ٦٣ أمبير ٥٠٠ فولت (للألواح والبطاريات)', 'Polarity-neutral DC breaker for battery and PV strings.', 'قاطع تيار مستمر بلا قطبية للبطاريات وسلاسل الألواح.', 'alnasr-electric', ['dc-breakers'], 14, null, 200, true],
            ['ANE-DCB2-32', 'DC MCB 2-Pole 32A 1000VDC (PV string)', 'قاطع تيار مستمر DC قطبين ٣٢ أمبير ١٠٠٠ فولت (سلاسل الألواح)', 'High-voltage DC breaker for PV strings up to 1000V.', 'قاطع DC عالي الجهد لسلاسل الألواح حتى ١٠٠٠ فولت.', 'alnasr-electric', ['dc-breakers'], 18, null, 160, false],
            ['ANE-MCCB3-100', 'MCCB 3-Pole 100A', 'قاطع MCCB ثلاثة أقطاب ١٠٠ أمبير', 'Moulded-case breaker, adjustable thermal trip, 25 kA.', 'قاطع علبة مصبوبة، فصل حراري قابل للضبط، ٢٥ كيلو أمبير.', 'alnasr-electric', ['mccb-breakers'], 52, null, 60, false],
            ['ANE-MCCB3-250', 'MCCB 3-Pole 250A', 'قاطع MCCB ثلاثة أقطاب ٢٥٠ أمبير', 'Moulded-case breaker for main distribution boards, 36 kA.', 'قاطع علبة مصبوبة للوحات التوزيع الرئيسية، ٣٦ كيلو أمبير.', 'alnasr-electric', ['mccb-breakers'], 85, 95, 35, false],
            ['ANE-MCBST-2P', 'MCB 2-Pole 40A with Shunt-trip Release 230V', 'قاطع MCB قطبين ٤٠ أمبير مع ملف فصل شنت ٢٣٠ فولت', 'Remote tripping from a fire-alarm or emergency-stop signal.', 'فصل عن بُعد بإشارة من إنذار الحريق أو زر الطوارئ.', 'alnasr-electric', ['shunt-trip-breakers', 'mcb-breakers'], 38, 45, 80, true],
            ['ANE-MCCBST-100', 'MCCB 3-Pole 100A with Shunt-trip Release 230V', 'قاطع MCCB ثلاثة أقطاب ١٠٠ أمبير مع ملف فصل شنت ٢٣٠ فولت', 'Moulded-case breaker with side-mounted shunt-trip coil.', 'قاطع علبة مصبوبة مع ملف فصل شنت جانبي.', 'alnasr-electric', ['shunt-trip-breakers', 'mccb-breakers'], 98, null, 40, false],
            ['ANE-MCCBST-250', 'MCCB 3-Pole 250A with Shunt-trip Release 230V', 'قاطع MCCB ثلاثة أقطاب ٢٥٠ أمبير مع ملف فصل شنت ٢٣٠ فولت', 'For main incomers that must trip remotely (fire alarm, BMS, emergency stop).', 'للمداخل الرئيسية التي تحتاج فصلًا عن بُعد (إنذار حريق، BMS، طوارئ).', 'alnasr-electric', ['shunt-trip-breakers', 'mccb-breakers'], 135, 150, 22, false],
        ];

        $products = [];

        foreach ($rows as $i => [$sku, $en, $ar, $shortEn, $shortAr, $brand, $categorySlugs, $price, $compare, $stock, $featured]) {
            $product = Product::query()->where('sku', $sku)->first() ?? new Product();

            $product->fill([
                'sku' => $sku,
                'name' => ['en' => $en, 'ar' => $ar],
                'slug' => ['en' => Str::slug($en)],
                'short_description' => ['en' => $shortEn, 'ar' => $shortAr],
                'description' => [
                    'en' => "<p>{$shortEn}</p><ul><li>Genuine product with manufacturer warranty</li><li>Installation and commissioning available from our team</li><li>Technical datasheet on request</li></ul>",
                    'ar' => "<p>{$shortAr}</p><ul><li>منتج أصلي مع ضمان المصنّع</li><li>خدمة التركيب والتشغيل متوفرة من فريقنا</li><li>الورقة الفنية عند الطلب</li></ul>",
                ],
                'brand_id' => $brands[$brand]->id,
                'price' => $price,
                'compare_price' => $compare,
                'cost_price' => round($price * 0.72, 2),
                'stock_qty' => $stock,
                'is_active' => true,
                'is_featured' => $featured,
                'sort_order' => $i,
            ])->save();

            $product->categories()->sync(collect($categorySlugs)->map(fn ($slug) => $categories[$slug]->id)->all());

            $this->attachImage($product, 'cover', "product-{$sku}", 900, 900);
            if ($i % 3 === 0) {
                $this->attachImage($product, 'gallery', "product-{$sku}-b", 900, 900);
            }

            $products[$sku] = $product;
        }

        return $products;
    }

    /**
     * Bulk filler for pagination / filter testing.
     *
     * @param  array<string, Brand>  $brands
     * @param  array<string, Category>  $categories
     */
    private function extraProducts(int $count, array $brands, array $categories): void
    {
        if ($count < 1) {
            return;
        }

        $brandIds = collect($brands)->pluck('id')->values();
        $categoryIds = collect($categories)->pluck('id')->values();

        Product::factory()->count($count)->create()->each(function (Product $product) use ($brandIds, $categoryIds) {
            $product->update([
                'brand_id' => $brandIds->random(),
                'compare_price' => fake()->boolean(30) ? round($product->price * 1.15, 2) : null,
                'is_featured' => fake()->boolean(10),
                'stock_qty' => fake()->boolean(85) ? fake()->numberBetween(1, 200) : 0,
            ]);
            $product->categories()->sync($categoryIds->random(fake()->numberBetween(1, 2))->all());
            $this->attachImage($product, 'cover', "extra-{$product->sku}", 900, 900);
        });
    }

    /** @param  array<string, Product>  $products */
    private function bundles(array $products): void
    {
        $rows = [
            'hybrid-backup-kit-5kw' => ['Home Hybrid Backup Kit 5 kW', 'طقم احتياطي منزلي هجين ٥ كيلوواط', 'Voltronic hybrid inverter, two Tobac lithium batteries, MPPT controller and protection breakers.', 'انفرتر فواترونيك هجين وبطاريتا ليثيوم توباك ومنظم MPPT وقواطع حماية.', 2890, ['VLT-AXK5' => 1, 'TBC-48100' => 2, 'VLT-MPPT80' => 1, 'ANE-DCB2-63' => 2, 'ANE-MCB2-40' => 2], 14],
            'felicity-storage-kit' => ['Felicity Solar Storage Kit', 'طقم تخزين شمسي فلاستي', 'Felicity inverter, two wall-mount lithium batteries, MPPT controller and DC breakers.', 'انفرتر فلاستي وبطاريتا ليثيوم جداريتان ومنظم MPPT وقواطع DC.', 2790, ['FLC-IVEM5K' => 1, 'FLC-LFP48100' => 2, 'FLC-MPPT60' => 1, 'ANE-DCB2-63' => 2], 21],
            'solis-tobac-storage-kit' => ['Solis + Tobac Storage Kit', 'طقم تخزين سوليز + توباك', 'Solis 5 kW hybrid inverter with three Tobac rack batteries, MCCB and DC protection.', 'انفرتر سوليز هجين ٥ كيلوواط مع ٣ بطاريات راك توباك وقاطع MCCB وحماية DC.', 4150, ['SOL-S6-5K' => 1, 'TBC-48100' => 3, 'ANE-MCCB3-100' => 1, 'ANE-DCB2-63' => 2], null],
            'protection-panel-pack' => ['Protection Panel Pack', 'حزمة لوحة الحماية', 'MCB, DC, MCCB and shunt-trip breakers for a complete solar distribution board.', 'قواطع MCB وDC وMCCB وقواطع شنت للوحة توزيع شمسية متكاملة.', 189, ['ANE-MCB1-32' => 4, 'ANE-MCB2-40' => 2, 'ANE-MCB3-63' => 2, 'ANE-DCB2-63' => 4, 'ANE-MCCB3-100' => 1, 'ANE-MCBST-2P' => 1], 30],
        ];

        foreach ($rows as $slug => [$en, $ar, $descEn, $descAr, $price, $items, $endsInDays]) {
            $bundle = $this->upsert(Bundle::class, $slug, [
                'name' => ['en' => $en, 'ar' => $ar],
                'description' => ['en' => $descEn, 'ar' => $descAr],
                'bundle_price' => $price,
                'starts_at' => null,
                'ends_at' => $endsInDays ? now()->addDays($endsInDays) : null,
                'is_active' => true,
            ]);

            $bundle->products()->sync(
                collect($items)->mapWithKeys(fn ($qty, $sku) => [$products[$sku]->id => ['qty' => $qty]])->all()
            );

            $this->attachImage($bundle, 'cover', "bundle-{$slug}", 1200, 750);
        }
    }

    private function portfolio(): void
    {
        $rows = [
            'rooftop-solar-logistics-hub' => ['2 MW Rooftop Solar — Logistics Hub', 'محطة شمسية ٢ ميجاوات على الأسطح — مركز لوجستي', 'Design, supply and installation of Solis grid-tie inverters on a 40,000 m² warehouse roof, with DC breakers and remote monitoring.', 'تصميم وتوريد وتركيب انفرترات سوليز المتصلة بالشبكة على سقف مستودع بمساحة ٤٠ ألف م²، مع قواطع DC ومراقبة عن بعد.', 'Najd Logistics', 'نجد للخدمات اللوجستية', '2026-03-15', true],
            'hybrid-solar-clinic' => ['Voltronic Hybrid + Tobac Lithium Storage — Medical Clinic', 'نظام فواترونيك هجين مع تخزين توباك ليثيوم — عيادة طبية', 'Hybrid inverters with 80 kWh of Tobac rack batteries keeping critical loads online through outages.', 'انفرترات هجينة مع ٨٠ كيلوواط ساعة من بطاريات توباك تُبقي الأحمال الحرجة تعمل أثناء الانقطاع.', 'Al-Shifa Clinic', 'عيادة الشفاء', '2025-11-02', true],
            'control-panel-packaging-plant' => ['Protection Panels with Shunt-trip Breakers — Packaging Plant', 'لوحات حماية بقواطع شنت — مصنع تغليف', 'MCCB incomers with shunt-trip release wired to the fire-alarm system for remote emergency disconnection.', 'قواطع MCCB رئيسية بملف فصل شنت مرتبطة بنظام إنذار الحريق للفصل الطارئ عن بُعد.', 'Eastern Packaging', 'الشرقية للتغليف', '2025-06-20', false],
            'felicity-homes-compound' => ['Felicity Storage Systems — 40 Villas', 'أنظمة تخزين فلاستي — ٤٠ فيلا', 'Felicity hybrid inverters, wall-mount lithium batteries and MPPT controllers installed across a residential compound.', 'انفرترات فلاستي هجينة وبطاريات ليثيوم جدارية ومنظمات MPPT في مجمّع سكني.', 'Marjan Residences', 'مساكن مرجان', '2025-02-10', false],
            'solar-pumping-date-farm' => ['Deye Hybrid + EPever Controllers — Date Farm', 'انفرترات ديي الهجينة ومنظمات إي بي إيفر — مزرعة نخيل', 'Off-grid solar power for irrigation pumps with Deye inverters, EPever MPPT controllers and DC protection, replacing diesel generators.', 'طاقة شمسية منفصلة لمضخات الري بانفرترات ديي ومنظمات إي بي إيفر وحماية DC بديلًا عن مولدات الديزل.', 'Wadi Farms', 'مزارع الوادي', '2024-09-01', false],
        ];

        $order = 0;

        foreach ($rows as $slug => [$titleEn, $titleAr, $descEn, $descAr, $clientEn, $clientAr, $completed, $featured]) {
            $item = $this->upsert(PortfolioItem::class, $slug, [
                'title' => ['en' => $titleEn, 'ar' => $titleAr],
                'description' => ['en' => "<p>{$descEn}</p>", 'ar' => "<p>{$descAr}</p>"],
                'client_name' => ['en' => $clientEn, 'ar' => $clientAr],
                'completed_at' => $completed,
                'is_active' => true,
                'is_featured' => $featured,
                'sort_order' => $order++,
            ]);

            $this->attachImage($item, 'cover', "portfolio-{$slug}", 1400, 900);
            $this->attachImage($item, 'gallery', "portfolio-{$slug}-1", 1000, 700);
            $this->attachImage($item, 'gallery', "portfolio-{$slug}-2", 1000, 700, nth: 2);
        }
    }

    // -- inbox, home page, settings -----------------------------------------------

    private function messages(int $count): void
    {
        $pool = [
            ['Khalid Al-Otaibi', 'khalid@example.com', '+966501112233', 'Quote: 10 kW solar system', 'We have a warehouse roof of about 600 m². Please send a quote for a Solis 10 kW grid-tie system with Tobac lithium storage.'],
            ['سارة الحربي', 'sara@example.com', '+966502223344', 'عرض سعر قواطع شنت', 'نحتاج قواطع MCCB مع ملف فصل شنت للوحة رئيسية مرتبطة بإنذار الحريق. نرجو إرسال عرض سعر.'],
            ['Omar Haddad', 'omar@example.com', '+971501234567', 'Charge controller for off-grid farm', 'Looking for EPever or Victron MPPT controllers for a 48 V off-grid farm system. Can you supply and commission?'],
            ['محمد القحطاني', 'm.qahtani@example.com', '+966503334455', 'استفسار عن بطاريات الليثيوم', 'ما الفرق بين بطارية توباك ٤٨ فولت ١٠٠ أمبير وبطارية فلاستي الجدارية لنظام منزلي؟ وهل يوجد ضمان؟'],
            ['Layla Mansour', 'layla@example.com', '+966504445566', 'Site survey request', 'We would like a site survey for a farm (5 hectares) to size a Deye hybrid system replacing diesel.'],
            ['فهد الدوسري', 'fahad@example.com', '+966505556677', 'انفرتر فواترونيك لمحل تجاري', 'أرغب في انفرتر فواترونيك ٥ كيلوواط لمحل تجاري مع بطارية ليثيوم. ما الأسعار وكم مدة التركيب؟'],
            ['Nadia Karim', 'nadia@example.com', null, 'Inverter compatibility', 'Is the Voltronic Axpert King hybrid inverter compatible with the Tobac 48 V rack battery? Need protocol details.'],
            ['عبدالله الشمري', 'a.shammari@example.com', '+966506667788', 'زيارة ميدانية', 'نرغب بزيارة مهندس لتقييم حمل مصنع صغير قبل تركيب نظام شمسي وقواطع حماية.'],
        ];

        $created = 0;

        foreach ($pool as $i => [$name, $email, $phone, $subject, $message]) {
            if ($created >= $count) {
                break;
            }

            ContactMessage::query()->firstOrCreate(
                ['email' => $email, 'subject' => $subject],
                [
                    'name' => $name,
                    'phone' => $phone,
                    'message' => $message,
                    'is_read' => $i % 3 === 0,
                    'created_at' => Carbon::now()->subHours(($i + 1) * 7),
                    'updated_at' => Carbon::now()->subHours(($i + 1) * 7),
                ],
            );
            $created++;
        }

        // Top up with random messages only until the inbox reaches the requested size,
        // so re-running never keeps growing it.
        $missing = $count - ContactMessage::count();

        if ($missing > 0) {
            ContactMessage::factory()->count($missing)->create();
        }
    }

    private function homeSections(): void
    {
        HomePageSection::seedDefaults();
        $this->sliderPictures();
    }

    /** Puts a product picture (from the 3D library photos) on the first slides of the home slider. */
    private function sliderPictures(): void
    {
        $slider = HomePageSection::where('type', HomeSectionType::Slider)->orderBy('sort_order')->first();
        if (! $slider) {
            return;
        }

        $content = $slider->content ?? [];
        foreach (['inverter-felicity-hybrid', 'battery-felicity-fla48350'] as $i => $slug) {
            $photo = Photo::for($slug);
            if ($photo && isset($content['slides'][$i]) && blank($content['slides'][$i]['product_image'] ?? null)) {
                Storage::disk('public')->put("home/slides/{$slug}.png", (string) file_get_contents($photo));
                $content['slides'][$i]['product_image'] = "home/slides/{$slug}.png";
            }
        }

        $slider->update(['content' => $content]);
    }

    /** Fills the contact details only where the admin has not set anything yet. */
    private function settings(): void
    {
        $settings = app(SiteSettings::class);

        $defaults = [
            'contact_phone' => '+966 11 000 0000',
            'whatsapp_phone' => '+966 50 000 0000',
            'contact_email' => 'sales@alnasr.example',
            'address_en' => '12 King Fahd Road, Riyadh, Saudi Arabia',
            'address_ar' => '١٢ طريق الملك فهد، الرياض، المملكة العربية السعودية',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'working_hours_en' => 'Sun – Thu · 8:00 – 17:00',
            'working_hours_ar' => 'الأحد – الخميس · ٨:٠٠ – ١٧:٠٠',
        ];

        foreach ($defaults as $key => $value) {
            if (blank($settings->{$key})) {
                $settings->{$key} = $value;
            }
        }

        $settings->save();
    }

    // -- helpers --------------------------------------------------------------------

    /**
     * Create or update a slug-addressed record (matched on the English slug).
     *
     * @template T of Model
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function upsert(string $class, string $slug, array $attributes): Model
    {
        $model = $class::query()->where('slug->en', $slug)->first() ?? new $class();

        $model->fill($attributes + ['slug' => ['en' => $slug]])->save();

        return $model;
    }

    private function attachImage(HasMedia $model, string $collection, string $seed, int $width, int $height, int $nth = 1): void
    {
        if (! $this->withImages || ! PlaceholderImage::available()) {
            return;
        }

        // `nth` lets a multi-image collection (gallery) be topped up without duplicating on re-runs
        if ($model->getMedia($collection)->count() >= $nth) {
            return;
        }

        $model->addMedia(PlaceholderImage::make($seed, $width, $height))
            ->usingFileName(Str::slug($seed).'.png')
            ->toMediaCollection($collection);
    }
}
