/**
 * Sample home-page sections for `npm run preview`.
 *
 *   /            → the built-in layout (what HomePageSection::defaults() ships)
 *   /?variant=alt → the same sections with their *second* design, plus the
 *                   extra types an admin can add (banners, counters, testimonials)
 *
 * Mirrors HomePageSection::toStorefront() on the PHP side.
 */
import type { HomeSection } from '@/types';
import { brands, bundles, categories, portfolio, products } from './data';

const t = (en: string, ar: string) => ({ en, ar });

const features = {
    items: [
        { icon: 'sun', title: t('Solar & storage', 'الطاقة الشمسية والتخزين'), text: t('Panels, lithium batteries and inverters sized to your load.', 'ألواح وبطاريات ليثيوم وانفرترات مناسبة لحِملك الكهربائي.') },
        { icon: 'factory', title: t('Industrial equipment', 'المعدات الصناعية'), text: t('Control gear and power equipment for plants and sites.', 'معدات تحكم وطاقة للمصانع والمواقع.') },
        { icon: 'cctv', title: t('Security systems', 'أنظمة الأمان'), text: t('Surveillance cameras and alarm systems, supplied and set up.', 'كاميرات مراقبة وأنظمة إنذار، توريد وتركيب.') },
        { icon: 'wrench', title: t('Our own installers', 'فريق تركيب خاص بنا'), text: t('The same team supplies, installs, commissions and supports.', 'الفريق نفسه يورّد ويركّب ويشغّل ويدعم.') },
    ],
};

const services = {
    items: [
        { icon: 'sun', title: t('Solar power systems', 'أنظمة الطاقة الشمسية'), text: t('High-capacity on-grid, off-grid and hybrid solar plants — from design and mounting to grid connection.', 'محطات شمسية عالية السعة (متصلة بالشبكة، منفصلة، هجينة) — من التصميم والتثبيت حتى الربط بالشبكة.'), subject: t('Solar power system installation', 'تركيب نظام طاقة شمسية') },
        { icon: 'cpu', title: t('Industrial control panels', 'لوحات التحكم الصناعية'), text: t('Custom-built motor control, distribution and automation panels, wired, tested and commissioned.', 'لوحات تحكم بالمحركات وتوزيع وأتمتة مصنّعة حسب الطلب، مع التمديد والاختبار والتشغيل.'), subject: t('Industrial control panel', 'لوحة تحكم صناعية') },
        { icon: 'zap', title: t('Inverters & power systems', 'الانفرترات وأنظمة الطاقة'), text: t('Power inverters, UPS and lithium battery storage installed for homes, farms and factories.', 'تركيب انفرترات وUPS وتخزين بطاريات الليثيوم للمنازل والمزارع والمصانع.'), subject: t('Inverter / battery storage installation', 'تركيب انفرتر / تخزين بطاريات') },
        { icon: 'bell', title: t('Surveillance & alarm systems', 'أنظمة المراقبة والإنذار'), text: t('IP camera networks, access control and intrusion alarms designed for complete site coverage.', 'شبكات كاميرات IP وتحكم بالدخول وإنذار اقتحام بتغطية كاملة للموقع.'), subject: t('Surveillance / alarm system installation', 'تركيب نظام مراقبة / إنذار') },
    ],
};

const process = {
    items: [
        { title: t('Site survey', 'معاينة الموقع'), text: t('We visit, measure the load and the site, and recommend the right system.', 'نزور الموقع ونقيس الحِمل ونوصي بالنظام المناسب.') },
        { title: t('Quotation', 'عرض السعر'), text: t('A clear, itemised quote covering equipment, installation and warranty.', 'عرض واضح ومفصّل يشمل المعدات والتركيب والضمان.') },
        { title: t('Installation', 'التركيب'), text: t('Our certified team installs, tests and commissions on schedule.', 'يقوم فريقنا المعتمد بالتركيب والاختبار والتشغيل في الموعد.') },
        { title: t('Support', 'الدعم'), text: t('Training, handover documents and after-sales maintenance.', 'تدريب ووثائق تسليم وصيانة ما بعد البيع.') },
    ],
};

const hero = {
    badge: t('Supply & installation', 'توريد وتركيب'),
    headline: t('Solar power, batteries and security — supplied and installed.', 'طاقة شمسية وبطاريات وأنظمة أمان — توريد وتركيب.'),
    text: t('Shop solar panels, lithium batteries, industrial equipment and cameras — or let our team design and install the whole system.', 'تسوّق الألواح الشمسية وبطاريات الليثيوم والمعدات الصناعية وكاميرات المراقبة — أو اترك لفريقنا تصميم وتركيب النظام كاملًا.'),
    primary_label: t('Browse products', 'تصفّح المنتجات'),
    primary_url: '/shop',
    secondary_label: t('Request a site survey', 'اطلب معاينة للموقع'),
    secondary_url: '/contact',
    slides: [
        { title: t('Power your site with the sun', 'شغّل موقعك بطاقة الشمس'), subtitle: t('High-capacity solar plants, designed and installed by our own team.', 'محطات شمسية عالية السعة، تصميم وتركيب بفريقنا.'), button_label: t('See solar products', 'شاهد المنتجات الشمسية'), button_url: '/categories/solar-panels', image: null },
        { title: t('Store it with lithium', 'خزّنها بالليثيوم'), subtitle: t('Rack-mount LiFePO4 batteries with built-in BMS and monitoring.', 'بطاريات ليثيوم راك مع نظام BMS ومراقبة.'), button_label: t('Browse batteries', 'تصفّح البطاريات'), button_url: '/categories/lithium-batteries', image: null },
        { title: t('Secure every corner', 'أمّن كل زاوية'), subtitle: t('IP cameras, recorders and intrusion alarms, installed and supported.', 'كاميرات IP ومسجلات وإنذار اقتحام، تركيب ودعم.'), button_label: t('Surveillance systems', 'أنظمة المراقبة'), button_url: '/categories/surveillance-cameras', image: null },
    ],
};

const cta = {
    title: t('Planning a solar, power or security project?', 'تخطّط لمشروع طاقة شمسية أو كهرباء أو أمان؟'),
    text: t('Tell us about your site and load — we will survey, quote and install.', 'أخبرنا عن موقعك وحِملك — ونتولى المعاينة والتسعير والتركيب.'),
    primary_label: t('Request a site survey', 'اطلب معاينة للموقع'),
    primary_url: '/contact',
    show_whatsapp: true,
};

const section = (
    id: number,
    type: HomeSection['type'],
    design: string,
    extra: Partial<HomeSection> = {},
): HomeSection => ({ id, type, design, title: null, subtitle: null, background_color: null, content: {}, items: [], ...extra });

const stats = { products: products.length * 12, brands: brands.length, projects: 120 };
const topCategories = categories.filter((c) => !c.parent_id);

const PHOTOS = import.meta.glob('../../../database/models3d/photos/*.png', { query: '?url', import: 'default', eager: true }) as Record<string, string>;
const photo = (slug: string) => PHOTOS[`../../../database/models3d/photos/${slug}.png`] ?? null;
const LIBRARY = import.meta.glob('../../../database/models3d/*.glb', { query: '?url', import: 'default', eager: true }) as Record<string, string>;

/** the sample 3D model each section type gets (what `php artisan db:seed` stores) */
const MODELS: Partial<Record<HomeSection['type'], [string, 'start' | 'end']>> = {
    hero: ['solar-panel', 'end'],
    categories: ['mixed', 'end'],
    services: ['control-panel', 'end'],
    process: ['wind-turbine', 'start'],
    products: ['battery', 'end'],
    bundles: ['solar-panel', 'end'],
    brands: ['cctv-camera', 'start'],
    portfolio: ['inverter', 'end'],
    testimonials: ['battery', 'end'],
    cta: ['solar-panel', 'end'],
};

/** `?m3d=off` hides every section model; `?m3d=<key>` forces one model everywhere */
export function buildHomeSections(variant: string | null, m3d: string | null = null): HomeSection[] {
    return withModels(buildSections(variant), m3d);
}

function withModels(sections: HomeSection[], m3d: string | null): HomeSection[] {
    return sections.map((s) => {
        const preset = MODELS[s.type];

        // ?m3d=glb:<slug> shows a file of the equipment library (database/models3d) in every section
        if (m3d?.startsWith('glb:')) {
            const url = LIBRARY[`../../../database/models3d/${m3d.slice(4)}.glb`];

            return { ...s, model_3d: url ? { model: 'custom', url, side: preset?.[1] ?? 'end' } : null };
        }

        const model = m3d === 'off' ? null : m3d ? m3d : preset?.[0];

        return { ...s, model_3d: model ? { model, url: null, side: preset?.[1] ?? 'end' } : null };
    });
}

function buildSections(variant: string | null): HomeSection[] {
    const alt = variant === 'alt';

    return [
        section(1, 'hero', alt ? 'slider' : 'split', {
            content: hero,
            items: products.filter((p) => p.is_featured).slice(0, 3),
            stats,
        }),
        section(25, 'slider', variant?.startsWith('slider-') ? variant.slice(7) : 'product', {
            content: {
                autoplay: true,
                interval: 6,
                slides: [
                    { badge: t('Hybrid inverters', 'انفرترات هجينة'), title: t('Power your home day and night', 'كهرباء لمنزلك ليلًا ونهارًا'), subtitle: t('Hybrid inverters from Voltronic, Felicity and Solis with MPPT charging and battery backup.', 'انفرترات هجينة من فواترونيك وفلاستي وسوليز مع شاحن MPPT ودعم البطاريات.'), button_label: t('Shop inverters', 'تسوّق الانفرترات'), button_url: '/shop', image: null, product_image: photo('inverter-felicity-hybrid') },
                    { badge: t('Lithium batteries', 'بطاريات الليثيوم'), title: t('LiFePO4 storage that lasts 6,000 cycles', 'تخزين LiFePO4 يدوم ٦٠٠٠ دورة'), subtitle: t('Rack and wall batteries from Tobac, Felicity, Pylontech and Dyness.', 'بطاريات رف وجدارية من توباك وفلاستي وبايلونتك وداينس.'), button_label: t('View batteries', 'شاهد البطاريات'), button_url: '/shop', image: null, product_image: photo('battery-felicity-fla48350') },
                    { badge: t('Protection', 'الحماية'), title: t('Breakers for every circuit', 'قواطع لكل دائرة'), subtitle: t('MCB, DC, MCCB and shunt-trip breakers — sized and supplied for your system.', 'قواطع MCB وDC وMCCB وقواطع الشنت — بالمقاس المناسب لنظامك.'), button_label: t('Talk to an engineer', 'تحدّث مع مهندس'), button_url: '/contact', image: null, product_image: photo('inverter-voltronic-axpert') },
                ],
            },
        }),
        section(2, 'features', alt ? 'cards' : 'row', { content: features }),
        ...(alt
            ? [
                  section(20, 'banners', 'split', {
                      content: {
                          items: [
                              { title: t('10 kW Hybrid Solar Kit', 'طقم هجين ١٠ كيلوواط'), text: t('Save up to 20% on the full package', 'وفّر حتى ٢٠٪ على الحزمة الكاملة'), button_label: t('View offer', 'شاهد العرض'), url: '/offers', image: null },
                              { title: t('Free site survey', 'معاينة مجانية للموقع'), text: t('Book a visit from our engineers', 'احجز زيارة من مهندسينا'), button_label: t('Book now', 'احجز الآن'), url: '/contact', image: null },
                          ],
                      },
                  }),
              ]
            : []),
        section(3, 'categories', alt ? 'pills' : 'tiles', {
            title: t('Shop by category', 'تسوّق حسب الفئة'),
            items: topCategories,
        }),
        section(4, 'services', alt ? 'list' : 'cards', {
            title: t('We install what we sell', 'نركّب ما نبيعه'),
            subtitle: t('A dedicated in-house team handles high-capacity systems from first survey to final commissioning.', 'فريق متخصص يتولى الأنظمة عالية السعة من المعاينة الأولى حتى التشغيل النهائي.'),
            content: services,
        }),
        section(5, 'process', alt ? 'timeline' : 'grid', { content: process }),
        ...(alt
            ? [
                  section(21, 'counters', 'band', {
                      content: {
                          items: [
                              { value: '120', suffix: '+', label: t('Projects delivered', 'مشروع منجز') },
                              { value: '8', suffix: ' MW', label: t('Solar capacity installed', 'قدرة شمسية مركّبة') },
                              { value: '1,500', suffix: '+', label: t('Cameras installed', 'كاميرا مركّبة') },
                              { value: '15', suffix: '', label: t('Years of experience', 'سنة خبرة') },
                          ],
                      },
                  }),
              ]
            : []),
        section(6, 'products', alt ? 'carousel' : 'grid', {
            title: t('Popular right now', 'الأكثر طلبًا الآن'),
            subtitle: t('Hand-picked products our customers ask for most.', 'منتجات مختارة بعناية يطلبها عملاؤنا أكثر من غيرها.'),
            items: products.filter((p) => p.is_featured),
        }),
        section(7, 'bundles', alt ? 'cards' : 'band', {
            title: t('Bundles that save you more', 'حزم توفّر عليك أكثر'),
            subtitle: t('Everything you need in one package, priced below buying each item separately.', 'كل ما تحتاجه في حزمة واحدة بسعر أقل من شراء كل عنصر على حدة.'),
            items: bundles,
        }),
        section(8, 'brands', alt ? 'marquee' : 'logos', { title: t('Brands we carry', 'العلامات التي نوفّرها'), items: brands }),
        section(9, 'portfolio', alt ? 'featured' : 'grid', {
            title: t('Work we are proud of', 'أعمال نفخر بها'),
            subtitle: t('A selection of recent projects delivered for our clients.', 'مختارات من مشاريعنا الأخيرة لعملائنا.'),
            items: portfolio.slice(0, 3),
        }),
        ...(alt
            ? [
                  section(22, 'testimonials', 'quote', {
                      title: t('What our clients say', 'ماذا يقول عملاؤنا'),
                      content: {
                          items: [
                              { name: 'Khalid Al-Otaibi', role: t('Operations Manager, Najd Logistics', 'مدير العمليات، نجد للخدمات اللوجستية'), quote: t('The 2 MW rooftop plant was commissioned ahead of schedule and our energy bill dropped by more than half.', 'تم تشغيل المحطة الشمسية ٢ ميجاوات قبل الموعد وانخفضت فاتورة الطاقة لدينا بأكثر من النصف.') },
                              { name: 'Dr. Sara Al-Harbi', role: t('Al-Shifa Clinic', 'عيادة الشفاء'), quote: t('Hybrid solar with lithium storage means our critical equipment never goes down during outages.', 'الطاقة الشمسية الهجينة مع تخزين الليثيوم تعني ألا تتوقف أجهزتنا الحرجة أثناء الانقطاع.') },
                          ],
                      },
                  }),
              ]
            : []),
        section(10, 'cta', alt ? 'split' : 'primary', { content: cta }),
    ];
}
