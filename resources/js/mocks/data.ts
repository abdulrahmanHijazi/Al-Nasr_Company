/**
 * Sample catalogue used by `npm run preview` so the storefront design can be
 * reviewed without PHP, a database or the Filament panel. Names, prices and
 * contact details are placeholders — nothing here ships to production.
 */
import type { Brand, Bundle, Category, PortfolioItem, Product, SiteSettings } from '@/types';

const t = (en: string, ar: string) => ({ en, ar });

export const settings: SiteSettings = {
    site_name_en: 'Al-Nasr Company',
    site_name_ar: 'شركة النصر',
    logo: null,
    favicon: null,
    og_image: null,
    primary_color: '#1d4ed8',
    secondary_color: '#d99a1f',
    header_color: '#0b1f3a',
    footer_color: '#07152a',
    address_en: '12 King Fahd Road, Riyadh, Saudi Arabia',
    address_ar: '١٢ طريق الملك فهد، الرياض، المملكة العربية السعودية',
    latitude: 24.7136,
    longitude: 46.6753,
    google_maps_url: null,
    contact_phone: '+966 11 000 0000',
    whatsapp_phone: '+966 50 000 0000',
    contact_email: 'sales@alnasr.example',
    working_hours_en: 'Sun – Thu · 8:00 – 17:00',
    working_hours_ar: 'الأحد – الخميس · ٨:٠٠ – ١٧:٠٠',
    facebook_url: 'https://facebook.com',
    instagram_url: 'https://instagram.com',
    x_url: 'https://x.com',
    linkedin_url: 'https://linkedin.com',
    youtube_url: 'https://youtube.com',
    tiktok_url: null,
    snapchat_url: null,
    telegram_url: null,
    seo_title_en: 'Al-Nasr Company — Solar, Batteries, Industrial & Security Systems',
    seo_title_ar: 'شركة النصر — الطاقة الشمسية والبطاريات والمعدات الصناعية وأنظمة المراقبة',
    seo_description_en:
        'Solar panels, lithium batteries, industrial equipment and surveillance cameras — with a team that installs solar systems, control panels, inverters and alarm systems.',
    seo_description_ar:
        'ألواح شمسية وبطاريات ليثيوم ومعدات صناعية وكاميرات مراقبة — مع فريق متخصص في تركيب أنظمة الطاقة الشمسية ولوحات التحكم والانفرترات وأنظمة الإنذار.',
    currency: 'USD',
    bg3d_enabled: true,
    bg3d_model: 'solar-panel',
    bg3d_custom_file: null,
    bg3d_position: 'right',
    bg3d_opacity: 55,
    bg3d_speed: 1,
    bg3d_interactive: true,
    bg3d_pages: 'all',
};

// Brands (fictional) ------------------------------------------------------------
export const brands: Brand[] = [
    { id: 1, name: t('SunVolt', 'سن فولت'), slug: 'sunvolt', description: t('Monocrystalline solar panels and mounting systems.', 'ألواح شمسية أحادية البلورة وأنظمة التثبيت.'), website_url: 'https://example.com' },
    { id: 2, name: t('Voltix', 'فولتيكس'), slug: 'voltix', description: t('LiFePO4 lithium batteries for home and commercial storage.', 'بطاريات ليثيوم فوسفات الحديد للتخزين المنزلي والتجاري.') },
    { id: 3, name: t('Gridline', 'جريدلاين'), slug: 'gridline', description: t('Hybrid, on-grid and off-grid power inverters.', 'انفرترات هجينة ومتصلة بالشبكة ومنفصلة.') },
    { id: 4, name: t('Ironcore', 'آيرون كور'), slug: 'ironcore', description: t('Industrial drives, switchgear and control components.', 'محركات ولوحات وقطع تحكم صناعية.') },
    { id: 5, name: t('Sentinel', 'سنتينل'), slug: 'sentinel', description: t('IP cameras, recorders and intrusion alarm systems.', 'كاميرات IP ومسجلات وأنظمة إنذار اقتحام.') },
    { id: 6, name: t('Brightwave', 'برايت ويف'), slug: 'brightwave', description: t('Industrial LED lighting and solar street lights.', 'إضاءة LED صناعية وإنارة شوارع شمسية.') },
];

const brand = (id: number) => {
    const b = brands.find((x) => x.id === id)!;
    return { id: b.id, name: b.name, slug: b.slug, logo_url: b.logo_url };
};

// Categories ---------------------------------------------------------------------
export const categories: Category[] = [
    { id: 1, name: t('Solar Panels', 'الألواح الشمسية'), slug: 'solar-panels', parent_id: null },
    { id: 2, name: t('Lithium Batteries', 'بطاريات الليثيوم'), slug: 'lithium-batteries', parent_id: null },
    { id: 3, name: t('Inverters & Power', 'الانفرترات والطاقة'), slug: 'inverters-power', parent_id: null },
    { id: 4, name: t('Industrial Equipment', 'المعدات الصناعية'), slug: 'industrial-equipment', parent_id: null },
    { id: 5, name: t('Surveillance Cameras', 'كاميرات المراقبة'), slug: 'surveillance-cameras', parent_id: null },
    { id: 6, name: t('Control Panels & Alarms', 'لوحات التحكم والإنذار'), slug: 'control-panels-alarms', parent_id: null },
    { id: 7, name: t('Hybrid Inverters', 'انفرترات هجينة'), slug: 'hybrid-inverters', parent_id: 3 },
    { id: 8, name: t('Off-grid Inverters', 'انفرترات منفصلة'), slug: 'off-grid-inverters', parent_id: 3 },
];
categories.forEach((c) => {
    c.children = categories.filter((x) => x.parent_id === c.id);
});

const cat = (...ids: number[]) =>
    ids.map((id) => {
        const c = categories.find((x) => x.id === id)!;
        return { id: c.id, name: c.name, slug: c.slug };
    });

// Products -------------------------------------------------------------------------
const description = {
    en: '<p>Supplied with manufacturer warranty and full documentation. Our installation team can size, install and commission this item as part of a complete system.</p><ul><li>Genuine product, warranty included</li><li>Installation and commissioning available</li><li>Technical datasheet on request</li></ul>',
    ar: '<p>يُورَّد مع ضمان المصنّع ووثائق كاملة. يستطيع فريق التركيب لدينا تحديد الحجم المناسب وتركيبه وتشغيله ضمن نظام متكامل.</p><ul><li>منتج أصلي مع ضمان</li><li>خدمة التركيب والتشغيل متوفرة</li><li>الورقة الفنية عند الطلب</li></ul>',
};

let pid = 0;
const product = (
    name: ReturnType<typeof t>,
    short: ReturnType<typeof t>,
    sku: string,
    price: number,
    compare: number | null,
    stock: number,
    brandId: number,
    catIds: number[],
    featured = false,
): Product => ({
    id: ++pid,
    sku,
    name,
    slug: name.en.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''),
    short_description: short,
    description,
    price,
    compare_price: compare,
    stock_qty: stock,
    is_featured: featured,
    brand: brand(brandId),
    categories: cat(...catIds),
    cover_url: null,
    gallery: [],
});

export const products: Product[] = [
    product(t('Mono Solar Panel 550W', 'لوح شمسي أحادي ٥٥٠ واط'), t('Half-cut cells, 21.3% efficiency, 25-year performance warranty.', 'خلايا نصف مقطوعة، كفاءة ٢١٫٣٪، ضمان أداء ٢٥ سنة.'), 'SNV-M550', 118, 135, 240, 1, [1], true),
    product(t('Bifacial Solar Panel 600W', 'لوح شمسي ثنائي الوجه ٦٠٠ واط'), t('Dual-glass bifacial module for ground-mount and commercial roofs.', 'وحدة زجاج مزدوج ثنائية الوجه للتثبيت الأرضي والأسطح التجارية.'), 'SNV-B600', 142, null, 120, 1, [1]),
    product(t('LiFePO4 Battery 48V 100Ah', 'بطارية ليثيوم ٤٨ فولت ١٠٠ أمبير'), t('Rack-mount 5.12 kWh, 6000 cycles, built-in BMS and RS485/CAN.', 'تركيب راك ٥٫١٢ كيلوواط ساعة، ٦٠٠٠ دورة، نظام BMS مدمج.'), 'VTX-48100', 1180, 1320, 18, 2, [2], true),
    product(t('LiFePO4 Battery 24V 200Ah', 'بطارية ليثيوم ٢٤ فولت ٢٠٠ أمبير'), t('Deep-cycle storage for off-grid homes, farms and telecom sites.', 'تخزين عميق للمنازل والمزارع ومواقع الاتصالات المنفصلة.'), 'VTX-24200', 920, null, 9, 2, [2]),
    product(t('Hybrid Inverter 10kW Three-Phase', 'انفرتر هجين ١٠ كيلوواط ثلاثي الأطوار'), t('MPPT hybrid inverter with battery priority and Wi-Fi monitoring.', 'انفرتر هجين MPPT مع أولوية البطارية ومراقبة عبر Wi-Fi.'), 'GRD-H10T', 1650, 1790, 14, 3, [3, 7], true),
    product(t('Off-grid Inverter 5kW 48V', 'انفرتر منفصل ٥ كيلوواط ٤٨ فولت'), t('Pure sine wave, built-in 80A MPPT charger, parallel up to 6 units.', 'موجة جيبية نقية، شاحن MPPT مدمج ٨٠ أمبير، توصيل حتى ٦ وحدات.'), 'GRD-O5K', 640, null, 22, 3, [3, 8]),
    product(t('Variable Frequency Drive 15kW', 'محرك تردد متغير ١٥ كيلوواط'), t('Vector control VFD for pumps, fans and conveyors, IP20.', 'محرك تردد بتحكم متجه للمضخات والمراوح والسيور، IP20.'), 'IRC-V15', 580, 640, 11, 4, [4]),
    product(t('Soft Starter 37kW', 'بادئ تشغيل ناعم ٣٧ كيلوواط'), t('Smooth motor starting with bypass contactor and overload protection.', 'تشغيل ناعم للمحركات مع كونتاكتور تجاوز وحماية حمل زائد.'), 'IRC-SS37', 410, null, 8, 4, [4]),
    product(t('Industrial Control Panel Enclosure IP65', 'خزانة لوحة تحكم صناعية IP65'), t('Powder-coated steel, 800×600×300 mm with mounting plate.', 'فولاذ مطلي بالبودرة، ٨٠٠×٦٠٠×٣٠٠ مم مع لوح تثبيت.'), 'IRC-E866', 135, null, 40, 4, [6]),
    product(t('4K IP Bullet Camera 8MP', 'كاميرا IP بوليت ٨ ميجابكسل 4K'), t('Night vision to 60 m, IP67, built-in microphone, PoE.', 'رؤية ليلية حتى ٦٠ م، IP67، ميكروفون مدمج، PoE.'), 'SEN-B8K', 89, 105, 150, 5, [5], true),
    product(t('PTZ Speed Dome Camera 30× Zoom', 'كاميرا PTZ دوّارة بتقريب ٣٠×'), t('Auto-tracking, 200 m IR, ideal for yards and perimeters.', 'تتبع تلقائي، أشعة تحت حمراء ٢٠٠ م، مثالية للساحات والمحيط.'), 'SEN-PTZ30', 365, null, 17, 5, [5]),
    product(t('16-Channel NVR with 8TB Storage', 'مسجل NVR ١٦ قناة مع تخزين ٨ تيرا'), t('4K recording, AI person/vehicle detection, RAID-ready.', 'تسجيل 4K، كشف أشخاص ومركبات بالذكاء الاصطناعي، يدعم RAID.'), 'SEN-N16', 420, 470, 12, 5, [5]),
    product(t('Wireless Intrusion Alarm Kit', 'طقم إنذار اقتحام لاسلكي'), t('Control panel, 4 door sensors, 2 PIR detectors, siren and app control.', 'لوحة تحكم و٤ حساسات أبواب و٢ حساس حركة وسارينة وتحكم بالتطبيق.'), 'SEN-AK4', 195, null, 33, 5, [6]),
    product(t('Solar Street Light 150W', 'إنارة شوارع شمسية ١٥٠ واط'), t('All-in-one, motion sensor, 12 h autonomy, IP65.', 'متكاملة، حساس حركة، تشغيل ١٢ ساعة، IP65.'), 'BRW-SS150', 158, 180, 0, 6, [1]),
    product(t('Mounting Rail Kit for 10 Panels', 'طقم سكك تثبيت لـ ١٠ ألواح'), t('Anodised aluminium rails, clamps and hooks for tiled or metal roofs.', 'سكك ألمنيوم مؤكسد وماسكات وخطافات للأسطح القرميدية أو المعدنية.'), 'SNV-RK10', 96, null, 60, 1, [1]),
];

// Bundles --------------------------------------------------------------------------
const inBundle = (ids: number[], qtys: number[] = []) =>
    ids.map((id, i) => ({ ...products.find((p) => p.id === id)!, pivot: { qty: qtys[i] ?? 1, price_override: null } }));

const daysFromNow = (d: number) => new Date(Date.now() + d * 86_400_000).toISOString();

const bundleOf = (
    id: number,
    name: ReturnType<typeof t>,
    description: ReturnType<typeof t>,
    price: number,
    ids: number[],
    qtys: number[],
    endsIn: number | null,
): Bundle => {
    const items = inBundle(ids, qtys);
    const original = items.reduce((s, p) => s + Number(p.price) * (p.pivot?.qty ?? 1), 0);
    return {
        id,
        name,
        slug: name.en.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''),
        description,
        bundle_price: price,
        original_total: original,
        savings: Math.max(0, original - price),
        starts_at: null,
        ends_at: endsIn ? daysFromNow(endsIn) : null,
        cover_url: null,
        products: items,
    };
};

export const bundles: Bundle[] = [
    bundleOf(1, t('10 kW Hybrid Solar Kit', 'طقم طاقة شمسية هجين ١٠ كيلوواط'), t('18 panels, hybrid inverter, battery and mounting — installation available.', '١٨ لوحًا وانفرتر هجين وبطارية وتثبيت — مع خدمة التركيب.'), 4290, [1, 5, 3, 15], [18, 1, 2, 2], 14),
    bundleOf(2, t('16-Camera CCTV Kit', 'طقم مراقبة ١٦ كاميرا'), t('Complete IP surveillance for a warehouse or compound.', 'نظام مراقبة IP متكامل لمستودع أو مجمّع.'), 1840, [10, 12, 11], [14, 1, 1], 21),
    bundleOf(3, t('Backup Power Pack', 'حزمة طاقة احتياطية'), t('Off-grid inverter paired with lithium storage for outages.', 'انفرتر منفصل مع تخزين ليثيوم لانقطاع الكهرباء.'), 1480, [6, 4], [1, 1], null),
];

// Portfolio ------------------------------------------------------------------------
export const portfolio: PortfolioItem[] = [
    { id: 1, title: t('2 MW Rooftop Solar — Logistics Hub', 'محطة شمسية ٢ ميجاوات على الأسطح — مركز لوجستي'), slug: 'rooftop-solar-logistics-hub', description: t('<p>Design, supply and installation of 3,600 panels across a 40,000 m² warehouse roof, with grid-tied inverters and remote monitoring.</p>', '<p>تصميم وتوريد وتركيب ٣٦٠٠ لوح على سقف مستودع بمساحة ٤٠ ألف م²، مع انفرترات مرتبطة بالشبكة ومراقبة عن بعد.</p>'), client_name: t('Najd Logistics', 'نجد للخدمات اللوجستية'), project_url: null, completed_at: '2026-03-15', is_featured: true, cover_url: null, gallery: [] },
    { id: 2, title: t('Hybrid Solar + Lithium Storage — Medical Clinic', 'طاقة شمسية هجينة مع تخزين ليثيوم — عيادة طبية'), slug: 'hybrid-solar-clinic', description: t('<p>40 kW hybrid system with 80 kWh lithium storage keeping critical loads online through outages.</p>', '<p>نظام هجين ٤٠ كيلوواط مع تخزين ليثيوم ٨٠ كيلوواط ساعة يُبقي الأحمال الحرجة تعمل أثناء الانقطاع.</p>'), client_name: t('Al-Shifa Clinic', 'عيادة الشفاء'), project_url: null, completed_at: '2025-11-02', is_featured: true, cover_url: null, gallery: [] },
    { id: 3, title: t('Control Panel Upgrade — Packaging Plant', 'تحديث لوحات التحكم — مصنع تغليف'), slug: 'control-panel-packaging-plant', description: t('<p>Replaced legacy motor control centres with VFD-based panels, cutting energy use and downtime.</p>', '<p>استبدال مراكز التحكم القديمة بلوحات تعتمد محركات تردد متغير لخفض الطاقة والتوقف.</p>'), client_name: t('Eastern Packaging', 'الشرقية للتغليف'), project_url: null, completed_at: '2025-06-20', cover_url: null, gallery: [] },
    { id: 4, title: t('CCTV & Alarm — 120-Camera Compound', 'مراقبة وإنذار — مجمّع ١٢٠ كاميرا'), slug: 'cctv-alarm-compound', description: t('<p>IP surveillance, access control and perimeter intrusion alarms across a residential compound.</p>', '<p>مراقبة IP وتحكم بالدخول وإنذار محيطي لمجمّع سكني.</p>'), client_name: t('Marjan Residences', 'مساكن مرجان'), project_url: null, completed_at: '2025-02-10', cover_url: null, gallery: [] },
    { id: 5, title: t('Solar Pumping System — Date Farm', 'نظام ضخ شمسي — مزرعة نخيل'), slug: 'solar-pumping-date-farm', description: t('<p>Solar-powered irrigation pumping with VFD control, replacing diesel generators.</p>', '<p>ضخ ري بالطاقة الشمسية مع تحكم بمحرك تردد متغير بديلًا عن مولدات الديزل.</p>'), client_name: t('Wadi Farms', 'مزارع الوادي'), project_url: null, completed_at: '2024-09-01', cover_url: null, gallery: [] },
];

// Keep the sample counters honest so filters and cards agree.
brands.forEach((b) => {
    b.products_count = products.filter((p) => p.brand?.id === b.id).length;
});
categories.forEach((c) => {
    const ids = new Set([c.id, ...(c.children ?? []).map((x) => x.id)]);
    c.products_count = products.filter((p) => p.categories?.some((pc) => ids.has(pc.id))).length;
});
