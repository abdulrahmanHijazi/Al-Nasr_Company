<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\HomePageSection;
use App\Models\PortfolioItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Starter catalogue for Al-Nasr: inverters (Voltronic, Felicity, Solis), lithium batteries (Tobac, Felicity),
 * solar charge controllers (Voltronic, EPever) and breakers (MCB, DC, MCCB, shunt-trip).
 * Safe to run once on a fresh database (`php artisan db:seed`); `php artisan demo:seed` loads the full catalogue.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $brands = collect([
            ['Voltronic', 'فواترونيك'],
            ['Felicity', 'فلاستي'],
            ['Solis', 'سوليز'],
            ['Tobac', 'توباك'],
            ['EPever', 'إي بي إيفر'],
            ['Al-Nasr Electric', 'النصر للكهرباء'],
        ])->mapWithKeys(fn ($b, $i) => [$b[0] => Brand::factory()->create([
            'name' => ['en' => $b[0], 'ar' => $b[1]],
            'slug' => ['en' => Str::slug($b[0]), 'ar' => Str::slug($b[0])],
            'sort_order' => $i,
        ])]);

        $categories = collect([
            ['inverters', 'Inverters', 'الانفرترات'],
            ['lithium-batteries', 'Lithium Batteries', 'بطاريات الليثيوم'],
            ['charge-controllers', 'Solar Charge Controllers', 'منظمات الشحن الشمسية'],
            ['breakers', 'Circuit Breakers', 'القواطع'],
        ])->mapWithKeys(fn ($c, $i) => [$c[0] => Category::factory()->create([
            'name' => ['en' => $c[1], 'ar' => $c[2]],
            'slug' => ['en' => $c[0], 'ar' => $c[0]],
            'sort_order' => $i,
        ])]);

        // [sku, name en, name ar, brand, category, price, compare, stock, featured]
        $catalogue = [
            ['VLT-AXK5', 'Voltronic Axpert King 5kW Hybrid Inverter', 'انفرتر فواترونيك Axpert King هجين ٥ كيلوواط', 'Voltronic', 'inverters', 690, 760, 24, true],
            ['FLC-IVEM5K', 'Felicity 5kW Hybrid Inverter 48V', 'انفرتر فلاستي هجين ٥ كيلوواط ٤٨ فولت', 'Felicity', 'inverters', 560, null, 30, false],
            ['SOL-S6-5K', 'Solis 5kW Hybrid Inverter (single-phase)', 'انفرتر سوليز هجين ٥ كيلوواط أحادي الطور', 'Solis', 'inverters', 1180, 1290, 14, true],
            ['TBC-48100', 'Tobac 48V 100Ah LiFePO4 Rack Battery', 'بطارية توباك ليثيوم ٤٨ فولت ١٠٠ أمبير (راك)', 'Tobac', 'lithium-batteries', 1090, 1190, 16, true],
            ['TBC-24200', 'Tobac 24V 200Ah LiFePO4 Battery', 'بطارية توباك ليثيوم ٢٤ فولت ٢٠٠ أمبير', 'Tobac', 'lithium-batteries', 940, null, 9, false],
            ['FLC-LFP48100', 'Felicity 48V 100Ah Wall-mount LiFePO4 Battery', 'بطارية فلاستي ليثيوم ٤٨ فولت ١٠٠ أمبير (جدارية)', 'Felicity', 'lithium-batteries', 1180, 1290, 12, false],
            ['VLT-MPPT80', 'Voltronic MPPT 80A Solar Charge Controller 48V', 'منظم شحن شمسي فواترونيك MPPT ٨٠ أمبير ٤٨ فولت', 'Voltronic', 'charge-controllers', 210, null, 40, false],
            ['EPV-TR4210', 'EPever Tracer 4210AN MPPT 40A Controller', 'منظم شحن إي بي إيفر Tracer 4210AN MPPT ٤٠ أمبير', 'EPever', 'charge-controllers', 190, null, 25, false],
            ['ANE-DCB2-63', 'DC MCB 2-Pole 63A 500VDC (solar / battery)', 'قاطع تيار مستمر DC قطبين ٦٣ أمبير ٥٠٠ فولت', 'Al-Nasr Electric', 'breakers', 14, null, 200, false],
            ['ANE-MCB2-40', 'MCB 2-Pole 40A (C curve)', 'قاطع MCB قطبين ٤٠ أمبير (منحنى C)', 'Al-Nasr Electric', 'breakers', 9, null, 300, false],
            ['ANE-MCCB3-100', 'MCCB 3-Pole 100A', 'قاطع MCCB ثلاثة أقطاب ١٠٠ أمبير', 'Al-Nasr Electric', 'breakers', 52, null, 60, false],
            ['ANE-MCBST-2P', 'MCB 2-Pole 40A with Shunt-trip Release 230V', 'قاطع MCB قطبين ٤٠ أمبير مع ملف فصل شنت ٢٣٠ فولت', 'Al-Nasr Electric', 'breakers', 38, 45, 80, true],
        ];

        $products = collect($catalogue)->map(function ($row) use ($brands, $categories) {
            [$sku, $en, $ar, $brand, $category, $price, $compare, $stock, $featured] = $row;

            $product = Product::factory()->create([
                'sku' => $sku,
                'name' => ['en' => $en, 'ar' => $ar],
                'slug' => ['en' => Str::slug($en), 'ar' => Str::slug($en)],
                'brand_id' => $brands[$brand]->id,
                'price' => $price,
                'compare_price' => $compare,
                'stock_qty' => $stock,
                'is_featured' => $featured,
            ]);
            $product->categories()->attach($categories[$category]);

            return $product;
        })->keyBy('sku');

        $bundle = Bundle::factory()->create([
            'name' => ['en' => 'Home Hybrid Backup Kit 5 kW', 'ar' => 'طقم احتياطي منزلي هجين ٥ كيلوواط'],
            'slug' => ['en' => 'hybrid-backup-kit-5kw', 'ar' => 'hybrid-backup-kit-5kw'],
            'bundle_price' => 2890,
        ]);
        $bundle->products()->attach($products['VLT-AXK5'], ['qty' => 1]);
        $bundle->products()->attach($products['TBC-48100'], ['qty' => 2]);
        $bundle->products()->attach($products['VLT-MPPT80'], ['qty' => 1]);
        $bundle->products()->attach($products['ANE-DCB2-63'], ['qty' => 2]);
        $bundle->products()->attach($products['ANE-MCB2-40'], ['qty' => 2]);

        PortfolioItem::factory()->create([
            'title' => ['en' => '2 MW Rooftop Solar — Logistics Hub', 'ar' => 'محطة شمسية ٢ ميجاوات — مركز لوجستي'],
            'slug' => ['en' => 'rooftop-solar-logistics-hub', 'ar' => 'rooftop-solar-logistics-hub'],
            'client_name' => ['en' => 'Najd Logistics', 'ar' => 'نجد للخدمات اللوجستية'],
            'is_featured' => true,
        ]);

        // Home page: persist the built-in layout so admins can edit/re-order it in Filament.
        HomePageSection::seedDefaults();
    }
}
