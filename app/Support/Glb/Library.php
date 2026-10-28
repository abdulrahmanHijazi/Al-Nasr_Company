<?php

namespace App\Support\Glb;

use App\Enums\ThreeDModelType;
use RuntimeException;

/**
 * The equipment 3D library: inverters, lithium batteries, solar charge controllers and breakers.
 *
 * Each model is built in code from the real form factor, proportions, colours, ports and printed panel of its
 * product family (LCD screens, labels, rating plates, terminals, DIN-rail profile ...). They are detailed
 * replicas, not manufacturer CAD files. `php artisan models3d:build --force` writes them to database/models3d/*.glb;
 * to use a manufacturer's own model, drop a .glb with the same file name there (or add a new one) and run
 * `php artisan models3d:import`.
 */
class Library
{
    /**
     * @return array<int, array{slug: string, type: ThreeDModelType, brand: string|null, name: array{en: string, ar: string}, color: string, build: callable(): GlbBuilder}>
     */
    public static function entries(): array
    {
        $e = fn (string $slug, ThreeDModelType $type, ?string $brand, string $en, string $ar, string $color, callable $build) => [
            'slug' => $slug, 'type' => $type, 'brand' => $brand, 'name' => ['en' => $en, 'ar' => $ar], 'color' => $color, 'build' => $build,
        ];

        $inv = ThreeDModelType::Inverter;
        $bat = ThreeDModelType::Battery;
        $mppt = ThreeDModelType::ChargeController;
        $brk = ThreeDModelType::Breaker;

        return [
            // -- inverters ---------------------------------------------------------
            // Voltronic: Axpert / InfiniSolar off-grid and hybrid range (photos from voltronicpower.com)
            $e('inverter-voltronic-axpert', $inv, 'Voltronic', 'Voltronic Axpert VM II TWIN 3.6-5.6 kW', 'فواترونيك Axpert VM II TWIN ‏3.6-5.6 كيلوواط', '#2563eb', fn () => self::photoOr('inverter-voltronic-axpert', ['h' => 0.5, 'd' => 0.19, 'fins' => true, 'wall' => true], fn () => self::wallInverter('Voltronic Axpert', [
                'w' => 0.33, 'h' => 0.5, 'd' => 0.15, 'body' => '#e6e9ed', 'panel' => '#1f2933', 'accent' => '#1e56b8', 'lcd' => '#38bdf8', 'fan' => true,
                'brand' => 'VOLTRONIC', 'model' => 'AXPERT KING 5KVA', 'rating' => ['INPUT  230VAC 50/60Hz  PV 90-450VDC', 'OUTPUT  5000VA / 5000W  230VAC', 'BATTERY  48VDC   MPPT 80A'],
            ]))),
            $e('inverter-voltronic-touch', $inv, 'Voltronic', 'Voltronic InfiniSolar V5 6.2 kW hybrid inverter', 'فواترونيك InfiniSolar V5 هجين ‏6.2 كيلوواط', '#2563eb', fn () => self::photoOr('inverter-voltronic-touch', ['h' => 0.52, 'd' => 0.19, 'fins' => true, 'wall' => true])),
            $e('inverter-voltronic-smart-load', $inv, 'Voltronic', 'Voltronic Axpert VM III TWIN 4-6 kW', 'فواترونيك Axpert VM III TWIN ‏4-6 كيلوواط', '#f97316', fn () => self::photoOr('inverter-voltronic-smart-load', ['h' => 0.5, 'd' => 0.19, 'fins' => true, 'wall' => true])),
            $e('inverter-voltronic-mks4', $inv, 'Voltronic', 'Voltronic Axpert MKS 4 TWIN 6 kW', 'فواترونيك Axpert MKS 4 TWIN ‏6 كيلوواط', '#2563eb', fn () => self::photoOr('inverter-voltronic-mks4', ['h' => 0.52, 'd' => 0.2, 'fins' => true, 'wall' => true])),
            $e('inverter-voltronic-vm4', $inv, 'Voltronic', 'Voltronic Axpert VM4 TWIN 4-6 kW', 'فواترونيك Axpert VM4 TWIN ‏4-6 كيلوواط', '#2563eb', fn () => self::photoOr('inverter-voltronic-vm4', ['h' => 0.52, 'd' => 0.2, 'fins' => true, 'wall' => true])),
            $e('inverter-voltronic-vm2-elite', $inv, 'Voltronic', 'Voltronic Axpert VM II Elite 6 kW', 'فواترونيك Axpert VM II Elite ‏6 كيلوواط', '#a78bfa', fn () => self::photoOr('inverter-voltronic-vm2-elite', ['h' => 0.54, 'd' => 0.2, 'fins' => true, 'wall' => true])),
            $e('inverter-voltronic-king2', $inv, 'Voltronic', 'Voltronic Axpert King II TWIN 6 kW', 'فواترونيك Axpert King II TWIN ‏6 كيلوواط', '#d6d3c7', fn () => self::photoOr('inverter-voltronic-king2', ['h' => 0.56, 'd' => 0.21, 'fins' => true, 'wall' => true])),
            $e('inverter-voltronic-king-ip52', $inv, 'Voltronic', 'Voltronic Axpert King IP52', 'فواترونيك Axpert King IP52', '#1f2937', fn () => self::photoOr('inverter-voltronic-king-ip52', ['h' => 0.56, 'd' => 0.21, 'fins' => true, 'wall' => true])),
            $e('inverter-voltronic-mks5', $inv, 'Voltronic', 'Voltronic Axpert MKS 5 TWIN 6.5 kW', 'فواترونيك Axpert MKS 5 TWIN ‏6.5 كيلوواط', '#0ea5e9', fn () => self::photoOr('inverter-voltronic-mks5', ['h' => 0.56, 'd' => 0.21, 'fins' => true, 'wall' => true])),
            $e('inverter-voltronic-max-duplex', $inv, 'Voltronic', 'Voltronic Axpert MAX II Duplex 8-11 kW', 'فواترونيك Axpert MAX II Duplex ‏8-11 كيلوواط', '#22d3ee', fn () => self::photoOr('inverter-voltronic-max-duplex', ['h' => 0.62, 'd' => 0.23, 'fins' => true, 'wall' => true])),
            $e('inverter-voltronic-max-hmi', $inv, 'Voltronic', 'Voltronic Axpert MAX TWIN HMI', 'فواترونيك Axpert MAX TWIN HMI', '#38bdf8', fn () => self::photoOr('inverter-voltronic-max-hmi', ['h' => 0.62, 'd' => 0.23, 'fins' => true, 'wall' => true])),
            $e('inverter-voltronic-ultra', $inv, 'Voltronic', 'Voltronic Axpert Ultra 8-11 kW', 'فواترونيك Axpert Ultra ‏8-11 كيلوواط', '#0b1220', fn () => self::photoOr('inverter-voltronic-ultra', ['h' => 0.6, 'd' => 0.22, 'wall' => true])),
            $e('inverter-voltronic-infini-v4', $inv, 'Voltronic', 'Voltronic InfiniSolar V4 TWIN 6.2 kW', 'فواترونيك InfiniSolar V4 TWIN ‏6.2 كيلوواط', '#facc15', fn () => self::photoOr('inverter-voltronic-infini-v4', ['h' => 0.46, 'd' => 0.18, 'wall' => true])),

            // Felicity Solar (photos from felicitysolar.nl)
            $e('inverter-felicity-hybrid', $inv, 'Felicity', 'Felicity IVEM6048-II hybrid inverter', 'انفرتر هجين فلاستي IVEM6048-II', '#f97316', fn () => self::photoOr('inverter-felicity-hybrid', ['h' => 0.45, 'd' => 0.18, 'wall' => true], fn () => self::wallInverter('Felicity hybrid', [
                'w' => 0.4, 'h' => 0.52, 'd' => 0.17, 'body' => '#3d434b', 'panel' => '#12161b', 'accent' => '#f97316', 'lcd' => '#4ade80', 'dc_switch' => true,
                'brand' => 'FELICITY SOLAR', 'model' => 'IVEM5048 · 5KW', 'rating' => ['INPUT  230VAC 50Hz  PV 120-500VDC', 'OUTPUT  5000W  230VAC', 'BATTERY  48VDC   MPPT 100A'],
            ]))),
            $e('inverter-felicity-ivcm3224', $inv, 'Felicity', 'Felicity IVCM3224 solar inverter charger', 'انفرتر وشاحن شمسي فلاستي IVCM3224', '#f97316', fn () => self::photoOr('inverter-felicity-ivcm3224', ['h' => 0.36, 'd' => 0.14, 'wall' => true])),
            $e('inverter-felicity-iam6048', $inv, 'Felicity', 'Felicity IVAM6048 outdoor hybrid inverter', 'انفرتر هجين خارجي فلاستي IVAM6048', '#f97316', fn () => self::photoOr('inverter-felicity-iam6048', ['h' => 0.5, 'd' => 0.2, 'wall' => true])),
            $e('inverter-felicity-ibm8048', $inv, 'Felicity', 'Felicity IVBM8048 hybrid inverter', 'انفرتر هجين فلاستي IVBM8048', '#f97316', fn () => self::photoOr('inverter-felicity-ibm8048', ['h' => 0.42, 'd' => 0.2, 'wall' => true])),
            $e('inverter-felicity-igm1216', $inv, 'Felicity', 'Felicity IVGM1216 hybrid inverter', 'انفرتر هجين فلاستي IVGM1216', '#f97316', fn () => self::photoOr('inverter-felicity-igm1216', ['h' => 0.62, 'd' => 0.22, 'wall' => true])),
            $e('inverter-felicity-ipm7548', $inv, 'Felicity', 'Felicity IVPM7548 low-frequency inverter', 'انفرتر فلاستي IVPM7548 (محول نحاسي)', '#f97316', fn () => self::photoOr('inverter-felicity-ipm7548', ['h' => 0.6, 'd' => 0.28])),
            $e('inverter-felicity-ips0712', $inv, 'Felicity', 'Felicity IVPS0712 pure-sine inverter', 'انفرتر فلاستي IVPS0712 موجة نقية', '#f97316', fn () => self::photoOr('inverter-felicity-ips0712', ['h' => 0.55, 'd' => 0.28])),

            // other brands (hand-built from their typical form factor)
            $e('inverter-solis-hybrid', $inv, 'Solis', 'Solis hybrid inverter', 'انفرتر هجين سوليز', '#1d4ed8', fn () => self::wallInverter('Solis hybrid', [
                'w' => 0.38, 'h' => 0.5, 'd' => 0.2, 'body' => '#d3d8de', 'panel' => '#232a33', 'accent' => '#1d4ed8', 'lcd' => '#60a5fa', 'fins' => true, 'dc_switch' => true,
                'brand' => 'SOLIS', 'model' => 'S6-EH1P5K-L', 'rating' => ['PV  MAX 600VDC   MPPT 90-520V', 'GRID  230VAC 50/60Hz  5000W', 'BATTERY  40-60VDC  IP65'],
            ])),
            $e('inverter-growatt-spf', $inv, 'Growatt', 'Growatt off-grid inverter', 'انفرتر منفصل جروات', '#16a34a', fn () => self::wallInverter('Growatt off-grid', [
                'w' => 0.36, 'h' => 0.46, 'd' => 0.16, 'body' => '#f1f3f4', 'panel' => '#2a3139', 'accent' => '#16a34a', 'lcd' => '#86efac', 'fan' => true,
                'brand' => 'GROWATT', 'model' => 'SPF 5000 ES', 'rating' => ['INPUT  230VAC 50/60Hz  PV 120-450VDC', 'OUTPUT  5000VA / 5000W  230VAC', 'BATTERY  48VDC   MPPT 80A'],
            ])),
            $e('inverter-deye-hybrid', $inv, 'Deye', 'Deye hybrid inverter', 'انفرتر هجين ديي', '#0ea5e9', fn () => self::wallInverter('Deye hybrid', [
                'w' => 0.5, 'h' => 0.6, 'd' => 0.21, 'body' => '#dfe4e9', 'panel' => '#111827', 'accent' => '#0b7fc4', 'lcd' => '#7dd3fc', 'fins' => true, 'dc_switch' => true,
                'brand' => 'DEYE', 'model' => 'SUN-5K-SG04LP3', 'rating' => ['PV  MAX 550VDC   MPPT 150-425V', 'GRID  230/400VAC 50/60Hz', 'BATTERY  40-60VDC  5000W'],
            ])),

            // -- lithium batteries -------------------------------------------------
            $e('battery-tobac-rack-48v', $bat, 'Tobac', 'Tobac 48V 100Ah rack LiFePO4 battery', 'بطارية ليثيوم توباك 48 فولت 100 أمبير (رف)', '#f59e0b', fn () => self::rackBattery('Tobac rack battery', [
                'body' => '#2b2f36', 'plate' => '#20242a', 'accent' => '#f59e0b', 'brand' => 'TOBAC', 'spec' => '48V 100Ah · 5.12kWh',
            ])),
            $e('battery-tobac-cabinet', $bat, 'Tobac', 'Tobac 4-module battery cabinet', 'خزانة بطاريات توباك (4 وحدات)', '#f59e0b', fn () => self::batteryCabinet('Tobac cabinet', '#f59e0b')),
            $e('battery-felicity-wall-48v', $bat, 'Felicity', 'Felicity LPBA48200 wall/floor LiFePO4 battery', 'بطارية ليثيوم فلاستي LPBA48200 (جدارية / أرضية)', '#f97316', fn () => self::photoOr('battery-felicity-wall-48v', ['h' => 0.8, 'd' => 0.2], fn () => self::wallBattery('Felicity wall battery', [
                'body' => '#e7eaee', 'panel' => '#2b3139', 'accent' => '#f97316', 'brand' => 'FELICITY SOLAR', 'spec' => 'LFP-48100 · 51.2V 100Ah · 5.12kWh',
            ]))),
            $e('battery-felicity-flb48206', $bat, 'Felicity', 'Felicity FLB48206 LiFePO4 battery', 'بطارية ليثيوم فلاستي FLB48206', '#f97316', fn () => self::photoOr('battery-felicity-flb48206', ['h' => 0.95, 'd' => 0.22])),
            $e('battery-felicity-flb48280', $bat, 'Felicity', 'Felicity FLB48280 floor-standing battery on wheels', 'بطارية فلاستي FLB48280 أرضية بعجلات', '#f97316', fn () => self::photoOr('battery-felicity-flb48280', ['h' => 1.0, 'd' => 0.22, 'feet' => 0.07])),
            $e('battery-felicity-fla48350', $bat, 'Felicity', 'Felicity FLA48350 floor-standing LiFePO4 battery', 'بطارية ليثيوم فلاستي FLA48350 أرضية', '#f97316', fn () => self::photoOr('battery-felicity-fla48350', ['h' => 0.95, 'd' => 0.22, 'feet' => 0.07])),
            $e('battery-felicity-flh48100', $bat, 'Felicity', 'Felicity FLH48100 stackable LiFePO4 battery rack', 'رف بطاريات ليثيوم فلاستي FLH48100 قابل للتكديس', '#f97316', fn () => self::photoOr('battery-felicity-flh48100', ['h' => 1.6, 'd' => 0.45, 'feet' => 0.03])),
            $e('battery-felicity-lux-x-96050', $bat, 'Felicity', 'Felicity LUX-X high-voltage battery tower', 'برج بطاريات فلاستي LUX-X عالي الجهد', '#f97316', fn () => self::photoOr('battery-felicity-lux-x-96050', ['h' => 1.7, 'd' => 0.3, 'feet' => 0.03])),
            $e('battery-pylontech-rack', $bat, 'Pylontech', 'Pylontech rack battery', 'بطارية بايلونتك (رف)', '#16a34a', fn () => self::rackBattery('Pylontech rack battery', [
                'body' => '#4b5058', 'plate' => '#363a41', 'accent' => '#22a45a', 'brand' => 'PYLONTECH', 'spec' => 'US5000 · 48V 100Ah',
            ])),
            $e('battery-dyness-wall', $bat, 'Dyness', 'Dyness wall-mount battery', 'بطارية داينس (جدارية)', '#0ea5e9', fn () => self::wallBattery('Dyness wall battery', [
                'body' => '#1f2937', 'panel' => '#0f172a', 'accent' => '#0ea5e9', 'w' => 0.5, 'h' => 0.58, 'd' => 0.17, 'brand' => 'DYNESS', 'spec' => 'Powerbox Pro · 51.2V 100Ah',
            ])),

            // -- solar charge controllers (regulators) -----------------------------
            $e('controller-voltronic-mppt', $mppt, 'Voltronic', 'Voltronic MPPT 80A solar charge controller', 'منظم شحن شمسي MPPT فواترونيك 80 أمبير', '#2563eb', fn () => self::controller('Voltronic MPPT', [
                'body' => '#2d333b', 'panel' => '#0f1318', 'accent' => '#2563eb', 'lcd' => '#38bdf8', 'w' => 0.2, 'h' => 0.32, 'd' => 0.1,
                'brand' => 'VOLTRONIC', 'model' => 'MPPT 80A · 48V', 'rating' => 'PV 145Voc max · 48VDC · 80A',
            ])),
            $e('controller-felicity-mppt', $mppt, 'Felicity', 'Felicity MPPT 60A solar charge controller', 'منظم شحن شمسي MPPT فلاستي 60 أمبير', '#f97316', fn () => self::controller('Felicity MPPT', [
                'body' => '#e5e8ec', 'panel' => '#1c2128', 'accent' => '#f97316', 'lcd' => '#4ade80', 'w' => 0.18, 'h' => 0.3, 'd' => 0.09,
                'brand' => 'FELICITY SOLAR', 'model' => 'MPPT 60A', 'rating' => 'PV 150Voc max · 12/24/48V · 60A',
            ])),
            $e('controller-epever-tracer', $mppt, 'EPever', 'EPever Tracer MPPT charge controller', 'منظم شحن شمسي MPPT إي بي إيفر Tracer', '#0ea5e9', fn () => self::controller('EPever Tracer', [
                'body' => '#1d4ed8', 'panel' => '#0b1220', 'accent' => '#e5e7eb', 'lcd' => '#7dd3fc', 'w' => 0.22, 'h' => 0.28, 'd' => 0.1,
                'brand' => 'EPEVER', 'model' => 'Tracer 4210AN', 'rating' => 'MPPT 40A · 12/24VDC · PV 100Voc', 'light_text' => true,
            ])),
            $e('controller-victron-smartsolar', $mppt, 'Victron', 'Victron SmartSolar MPPT charge controller', 'منظم شحن شمسي MPPT فيكترون SmartSolar', '#0284c7', fn () => self::controller('Victron SmartSolar', [
                'body' => '#1d6fb8', 'panel' => '#16579a', 'accent' => '#f4f6f8', 'w' => 0.2, 'h' => 0.3, 'd' => 0.11,
                'brand' => 'victron energy', 'model' => 'SmartSolar', 'rating' => 'MPPT 150/35', 'victron' => true, 'light_text' => true,
            ])),
            $e('controller-pwm-30a', $mppt, null, 'PWM 30A solar charge controller', 'منظم شحن شمسي PWM 30 أمبير', '#22c55e', fn () => self::controller('PWM 30A', [
                'body' => '#f1f3f5', 'panel' => '#1f2937', 'accent' => '#22c55e', 'lcd' => '#86efac', 'w' => 0.14, 'h' => 0.2, 'd' => 0.05, 'fins' => false,
                'brand' => 'SOLAR CHARGE', 'model' => 'PWM 30A', 'rating' => '12/24V auto · 30A', 'pwm' => true,
            ])),

            // -- breakers (MCB / MCCB, DC and shunt-trip) --------------------------
            $e('breaker-mcb-1p', $brk, null, 'MCB 1-pole circuit breaker', 'قاطع صغير MCB قطب واحد', '#64748b', fn () => self::mcb('MCB 1P', 1, rating: 'C32')),
            $e('breaker-mcb-2p', $brk, null, 'MCB 2-pole circuit breaker', 'قاطع صغير MCB قطبين', '#64748b', fn () => self::mcb('MCB 2P', 2, rating: 'C40')),
            $e('breaker-mcb-3p', $brk, null, 'MCB 3-pole circuit breaker', 'قاطع صغير MCB ثلاثة أقطاب', '#64748b', fn () => self::mcb('MCB 3P', 3, rating: 'C63')),
            $e('breaker-mcb-4p', $brk, null, 'MCB 4-pole circuit breaker', 'قاطع صغير MCB أربعة أقطاب', '#64748b', fn () => self::mcb('MCB 4P', 4, rating: 'C63')),
            $e('breaker-dc-mcb-2p', $brk, null, 'DC MCB 2-pole breaker (solar)', 'قاطع تيار مستمر DC قطبين (للطاقة الشمسية)', '#1d4ed8', fn () => self::mcb('DC MCB 2P', 2, dc: true, rating: 'C63')),
            $e('breaker-mcb-shunt-trip', $brk, null, 'MCB with shunt-trip release', 'قاطع MCB مع ملف فصل شنت', '#f97316', fn () => self::mcb('MCB shunt trip', 2, shunt: true, rating: 'C40')),
            // MCCBs: photos from chint-ajlanbros.com (NXMG, NM8N, NXMD)
            $e('breaker-mccb-3p', $brk, 'Chint', 'Chint NXMG-100 3-pole MCCB 100A', 'قاطع علبة مصبوبة CHINT NXMG-100 ثلاثة أقطاب ‏100 أمبير', '#334155', fn () => self::photoOr('breaker-mccb-3p', ['h' => 0.155, 'd' => 0.07, 'inset' => 0.004], fn () => self::mccb('MCCB 3P'))),
            $e('breaker-mccb-shunt-trip', $brk, 'Chint', 'Chint NXMG-100 MCCB with shunt-trip release', 'قاطع علبة مصبوبة CHINT NXMG-100 مع ملف فصل شنت', '#f97316', fn () => self::photoOr('breaker-mccb-shunt-trip', ['h' => 0.155, 'd' => 0.07, 'inset' => 0.004, 'shunt' => true], fn () => self::mccb('MCCB shunt trip', shunt: true))),
            $e('breaker-mccb-nxmg-250', $brk, 'Chint', 'Chint NXMG-250 MCCB 250A', 'قاطع علبة مصبوبة CHINT NXMG-250 ‏250 أمبير', '#334155', fn () => self::photoOr('breaker-mccb-nxmg-250', ['h' => 0.2, 'd' => 0.09, 'inset' => 0.004])),
            $e('breaker-mccb-nxmg-400', $brk, 'Chint', 'Chint NXMG-400 MCCB 400A', 'قاطع علبة مصبوبة CHINT NXMG-400 ‏400 أمبير', '#334155', fn () => self::photoOr('breaker-mccb-nxmg-400', ['h' => 0.27, 'd' => 0.11, 'inset' => 0.004])),
            $e('breaker-mccb-nxmg-key', $brk, 'Chint', 'Chint NXMG-100C MCCB with key lock', 'قاطع علبة مصبوبة CHINT NXMG-100C بقفل مفتاح', '#334155', fn () => self::photoOr('breaker-mccb-nxmg-key', ['h' => 0.165, 'd' => 0.07, 'inset' => 0.004])),
            $e('breaker-mccb-nm8n-4p', $brk, 'Chint', 'Chint NM8N-125S 4-pole MCCB 125A', 'قاطع علبة مصبوبة CHINT NM8N-125S أربعة أقطاب ‏125 أمبير', '#e5e7eb', fn () => self::photoOr('breaker-mccb-nm8n-4p', ['h' => 0.155, 'd' => 0.068, 'inset' => 0.004])),
            $e('breaker-mccb-nxmd', $brk, 'Chint', 'Chint NXMD-250 automatic transfer MCCB', 'قاطع CHINT NXMD-250 للتحويل الأوتوماتيكي', '#334155', fn () => self::photoOr('breaker-mccb-nxmd', ['h' => 0.2, 'd' => 0.09, 'inset' => 0.004])),
        ];
    }

    // -- real product photos --------------------------------------------------------------

    /**
     * A model wrapped in the manufacturer's own front photo (saved with `models3d:photo`); falls back to the
     * hand-built model when there is no photo yet.
     *
     * @param  array<string, mixed>  $o  h (height, m), d (depth, m), fins, wall, feet (share of the height that is feet/wheels)
     * @param  (callable(): GlbBuilder)|null  $fallback
     */
    private static function photoOr(string $slug, array $o, ?callable $fallback = null): GlbBuilder
    {
        $photo = Photo::for($slug);

        if ($photo === null) {
            return $fallback ? $fallback() : throw new RuntimeException("Save a photo first: php artisan models3d:photo {$slug} <url>");
        }

        $info = Photo::describe($photo);
        $h = $o['h'];
        $d = $o['d'];
        $w = $h * $info['width'] / $info['height'];
        $feet = $o['feet'] ?? 0.0;
        $inset = $o['inset'] ?? 0.012;
        $bodyH = $h * (1 - $feet) - 2 * $inset;
        $bodyY = $h * $feet / 2;

        $b = self::base($slug)
            ->material('body', $info['edge'], 0.15, 0.5)
            ->material('fin', '#15171a', 0.4, 0.5)
            ->material('front', '#ffffff', 0.1, 0.55, null, (string) file_get_contents($photo), null, true);

        $b->box('body', [$w - 2 * $inset, $bodyH, $d], [0, $bodyY, 0], radius: 0.012, segments: 3);
        $b->plane('front', $w, $h, [0, 0, $d / 2 + 0.0006]);

        if ($o['fins'] ?? false) {   // the black heat-sink ribs down the side
            $x = $w / 2 - $inset;
            $b->box('fin', [0.014, $bodyH * 0.92, $d * 0.9], [$x + 0.007, $bodyY, 0]);
            for ($i = 0; $i < 9; $i++) {
                $b->box('fin', [0.03, $bodyH * 0.88, 0.004], [$x + 0.026, $bodyY, -$d * 0.4 + $i * ($d * 0.8 / 8)]);
            }
        }

        if ($o['shunt'] ?? false) {   // shunt-trip release module on the side
            $b->material('orange', '#f97316', 0.15, 0.45);
            $sw = 0.032;
            $x = $w / 2 - $inset + $sw / 2;
            $b->box('orange', [$sw, $bodyH * 0.96, $d * 0.96], [$x, $bodyY, 0], radius: 0.004);
            $b->box('black', [$sw * 0.7, 0.03, 0.002], [$x, $bodyY - 0.01, $d * 0.48 + 0.001]);
            $b->box('white', [$sw * 0.7, 0.02, 0.002], [$x, $bodyY + 0.025, $d * 0.48 + 0.001]);
            foreach (['red', 'black'] as $k => $c) {
                $b->cylinder($c, 0.0025, 0.07, [$x + ($k ? 0.006 : -0.006), $bodyY + $bodyH / 2 + 0.03, 0], segments: 8);
            }
        }

        if ($o['wall'] ?? false) {   // wall tabs on top, cable glands underneath
            foreach ([-1, 1] as $s) {
                $b->box('fin', [0.05, 0.012, 0.012], [$s * $w * 0.28, $bodyY + $bodyH / 2 + 0.005, -$d / 2 + 0.014], radius: 0.002);
            }
            foreach ([-1, 0, 1] as $i) {
                $b->cylinder('black', 0.014, 0.04, [$i * $w * 0.2, $bodyY - $bodyH / 2 - 0.02, $d * 0.05], segments: 18);
                $b->cylinder('steel', 0.0165, 0.008, [$i * $w * 0.2, $bodyY - $bodyH / 2 - 0.04, $d * 0.05], segments: 18);
            }
        }

        return $b;
    }

    // -- shared parts -----------------------------------------------------------------

    private static function base(string $name): GlbBuilder
    {
        return (new GlbBuilder($name))
            ->material('black', '#111418', 0.3, 0.6)
            ->material('steel', '#a3acb8', 0.9, 0.35)
            ->material('white', '#f4f6f8', 0.05, 0.5)
            ->material('led_g', '#22c55e', 0.0, 0.4, '#22c55e')
            ->material('led_r', '#ef4444', 0.0, 0.4, '#ef4444')
            ->material('led_y', '#f59e0b', 0.0, 0.4, '#f59e0b')
            ->material('red', '#dc2626', 0.1, 0.5)
            ->material('blue', '#1d4ed8', 0.2, 0.4)
            ->material('copper', '#b87333', 0.9, 0.35)
            ->material('rubber', '#1a1c1f', 0.0, 0.85);
    }

    /** @param  array{map: string, glow: string|null}  $png */
    private static function front(GlbBuilder $b, string $name, array $png, float $metal = 0.12, float $rough = 0.5): void
    {
        $b->material($name, '#ffffff', $metal, $rough, null, $png['map'], $png['glow']);
    }

    /** A cross-slot screw head facing +Z. */
    private static function screw(GlbBuilder $b, float $x, float $y, float $z, float $r = 0.0045): void
    {
        $b->cylinder('steel', $r, 0.003, [$x, $y, $z], [90, 0, 0], 14);
        $b->box('black', [$r * 1.7, $r * 0.3, 0.0008], [$x, $y, $z + 0.0016], [0, 0, 35]);
    }

    /** A recessed push button: dark ring in the texture, a raised cap here. */
    private static function button(GlbBuilder $b, float $x, float $y, float $z, float $r = 0.0085): void
    {
        $b->cylinder('rubber', $r, 0.005, [$x, $y, $z + 0.0025], [90, 0, 0], 20);
        $b->cylinder('steel', $r * 0.78, 0.0025, [$x, $y, $z + 0.0062], [90, 0, 0], 20);
    }

    private static function led(GlbBuilder $b, string $material, float $x, float $y, float $z, float $r = 0.0035): void
    {
        $b->cylinder($material, $r, 0.003, [$x, $y, $z + 0.0015], [90, 0, 0], 12);
    }

    /** An RJ45-style socket on the front face. */
    private static function socket(GlbBuilder $b, float $x, float $y, float $z, float $w = 0.016, float $h = 0.012): void
    {
        $b->box('black', [$w, $h, 0.006], [$x, $y, $z + 0.003], radius: 0.001, segments: 1);
        $b->box('steel', [$w * 0.7, $h * 0.28, 0.0012], [$x, $y - $h * 0.18, $z + 0.0062]);
    }

    /** Printed LCD of a solar inverter: PV / grid / load / battery flow with readings. */
    private static function inverterLcd(Panel $p, float $cx, float $cy, float $w, float $h, string $color): void
    {
        $p->rect($cx, $cy, $w + 0.006, $h + 0.006, '#05070a', 0.004);
        $p->rect($cx, $cy, $w, $h, Panel::mix($color, '#000000', 0.88), 0.002, Panel::mix($color, '#000000', 0.78));

        $iy = $cy + $h * 0.26;
        $r = $h * 0.13;
        $xs = [-0.36, -0.12, 0.12, 0.36];

        $p->sun($cx + $xs[0] * $w, $iy, $r, $color, true);
        $p->bolt($cx + $xs[1] * $w, $iy, $r * 2, $color, true);
        // house (load)
        $hx = $cx + $xs[2] * $w;
        $p->polygon([[$hx - $r * 1.1, $iy + $r * 0.1], [$hx, $iy + $r * 1.05], [$hx + $r * 1.1, $iy + $r * 0.1]], $color, true)
            ->rect($hx, $iy - $r * 0.45, $r * 1.6, $r * 1.0, $color, 0.0, true);
        $p->battery($cx + $xs[3] * $w - $r * 0.15, $iy, $r * 2.2, $r * 1.3, 3, $color, true);

        foreach ([0, 1, 2] as $i) {
            $p->line($cx + ($xs[$i] + 0.075) * $w, $iy, $cx + ($xs[$i + 1] - 0.075) * $w, $iy, $color, $h * 0.012, true);
        }

        $vals = ['3.2kW', '0.0kW', '1.8kW', '52.4V'];
        foreach ($vals as $i => $v) {
            $p->text($v, $cx + $xs[$i] * $w, $cy - $h * 0.04, $h * 0.075, $color, 'center', false, true);
        }
        $p->line($cx - $w * 0.46, $cy - $h * 0.16, $cx + $w * 0.46, $cy - $h * 0.16, Panel::mix($color, '#000000', 0.55), $h * 0.01, true);
        $p->text('230', $cx - $w * 0.32, $cy - $h * 0.33, $h * 0.17, $color, 'center', true, true)
            ->text('V', $cx - $w * 0.18, $cy - $h * 0.36, $h * 0.07, $color, 'center', false, true)
            ->text('50.0', $cx + $w * 0.05, $cy - $h * 0.33, $h * 0.17, $color, 'center', true, true)
            ->text('Hz', $cx + $w * 0.2, $cy - $h * 0.36, $h * 0.07, $color, 'center', false, true)
            ->text('LOAD 36%', $cx + $w * 0.37, $cy - $h * 0.33, $h * 0.065, $color, 'center', false, true);
    }

    // -- inverters ------------------------------------------------------------------

    /** @param  array<string, mixed>  $o */
    private static function wallInverter(string $name, array $o): GlbBuilder
    {
        ['w' => $w, 'h' => $h, 'd' => $d] = $o;
        $fw = $w - 0.03;
        $fh = $h - 0.03;
        $top = $fh / 2;

        // -- the printed face
        $p = new Panel($fw, $fh, 1024, $o['body']);
        $p->gradient(Panel::mix($o['body'], '#ffffff', 0.4), Panel::mix($o['body'], '#000000', 0.1));
        $p->rect(0, $top - 0.013, $fw, 0.026, $o['accent']);
        $p->text($o['brand'], -$fw / 2 + 0.025, $top - 0.013, 0.0105, '#ffffff', 'left', true, null, 0.0012);
        $p->text($o['model'], $fw / 2 - 0.025, $top - 0.013, 0.0065, '#ffffff', 'right');

        $cw = $fw * 0.9;
        $ch = $fh * 0.57;
        $cpTop = $top - 0.04;
        $cy = $cpTop - $ch / 2;
        $p->rect(0, $cy, $cw + 0.004, $ch + 0.004, Panel::mix($o['panel'], '#ffffff', 0.28), 0.012);
        $p->rect(0, $cy, $cw, $ch, $o['panel'], 0.011);
        $p->gradient(Panel::mix($o['panel'], '#ffffff', 0.08), $o['panel'], 0, $cy, $cw - 0.01, $ch - 0.01);

        self::inverterLcd($p, 0, $cpTop - $ch * 0.31, $cw * 0.8, $ch * 0.46, $o['lcd']);

        $ledY = $cpTop - $ch * 0.68;
        $btnY = $cpTop - $ch * 0.85;
        $ledX = [-0.22 * $cw, 0.0, 0.22 * $cw];
        $btnX = [-0.3 * $cw, -0.1 * $cw, 0.1 * $cw, 0.3 * $cw];
        foreach (['AC/INV', 'CHG', 'FAULT'] as $i => $label) {
            $p->circle($ledX[$i], $ledY, 0.0058, '#07090c')->text($label, $ledX[$i], $ledY + 0.0125, 0.0042, '#aeb7c4');
        }
        foreach (['ESC', 'UP', 'DOWN', 'ENTER'] as $i => $label) {
            $p->circle($btnX[$i], $btnY, 0.0105, '#07090c')->text($label, $btnX[$i], $btnY - 0.0165, 0.0042, '#aeb7c4');
        }

        // rating sticker + fan grille / vents below the control panel
        $lowTop = $cy - $ch / 2 - 0.014;
        $lowBottom = -$fh / 2 + 0.014;
        $lowH = $lowTop - $lowBottom;
        $lowY = ($lowTop + $lowBottom) / 2;
        $sh = min($lowH, 0.092);
        $sx = -$fw * 0.18;
        $sw = $fw * 0.58;
        $p->rect($sx, $lowY, $sw, $sh, '#ffffff', 0.002);
        $p->text($o['brand'].'  '.$o['model'], $sx - $sw / 2 + 0.008, $lowY + $sh * 0.32, 0.0042, '#111827', 'left', true);
        foreach ($o['rating'] as $i => $line) {
            $p->text($line, $sx - $sw / 2 + 0.008, $lowY + $sh * 0.08 - $i * 0.0085, 0.0034, '#374151', 'left');
        }
        $p->barcode($sx - $sw * 0.2, $lowY - $sh * 0.37, $sw * 0.5, $sh * 0.2, $o['model']);
        $p->text('CE', $sx + $sw * 0.3, $lowY - $sh * 0.36, 0.0065, '#111827', 'center', true)
            ->text('IP20 · MADE IN CHINA', $sx + $sw / 2 - 0.006, $lowY - $sh * 0.36, 0.0028, '#6b7280', 'right');

        $gx = $fw * 0.27;
        if ($o['fan'] ?? false) {
            $gr = min($lowH / 2, 0.052);
            $p->circle($gx, $lowY, $gr, Panel::mix($o['body'], '#000000', 0.25))->circle($gx, $lowY, $gr * 0.92, '#0d1013');
            foreach ([0.78, 0.56, 0.34] as $f) {
                $p->arc($gx, $lowY, $gr * $f, 0, 360, '#2b3138', 0.0014);
            }
            foreach ([0, 45, 90, 135] as $a) {
                $rad = deg2rad($a);
                $p->line($gx - cos($rad) * $gr * 0.9, $lowY - sin($rad) * $gr * 0.9, $gx + cos($rad) * $gr * 0.9, $lowY + sin($rad) * $gr * 0.9, '#2b3138', 0.0014);
            }
            $p->circle($gx, $lowY, $gr * 0.14, '#1a1e23');
        } else {
            $p->slots($gx, $lowY, $fw * 0.3, $sh * 0.92, 9, Panel::mix($o['body'], '#000000', 0.55));
        }

        $png = $p->png();
        $b = self::base($name)
            ->material('body', $o['body'], 0.3, 0.45)
            ->material('accent', $o['accent'], 0.3, 0.4);
        self::front($b, 'front', $png);

        $z = $d / 2;
        $b->box('body', [$w, $h, $d], radius: 0.018, segments: 4);
        $b->plane('front', $fw, $fh, [0, 0, $z + 0.0006]);
        foreach ([-1, 1] as $s) {
            self::screw($b, $s * ($fw / 2 - 0.012), $top - 0.013, $z + 0.0016, 0.0042);
        }

        foreach ($btnX as $x) {
            self::button($b, $x, $btnY, $z);
        }
        foreach (['led_g', 'led_y', 'led_r'] as $i => $m) {
            self::led($b, $m, $ledX[$i], $ledY, $z, 0.0046);
        }

        // top vents
        for ($i = -4; $i <= 4; $i++) {
            $b->box('black', [0.004, 0.0012, $d * 0.55], [$i * $w * 0.07, $h / 2 + 0.0004, 0], radius: 0.0005, segments: 1);
        }

        // underside: PV (MC4) and battery cables, glands, DC breaker, comm ports
        $by = -$h / 2;
        foreach ([[-0.36, 'red'], [-0.25, 'black']] as [$k, $m]) {
            $x = $k * $w;
            $b->cylinder($m, 0.0085, 0.04, [$x, $by - 0.02, $d * 0.2], segments: 16);
            $b->cylinder('steel', 0.011, 0.008, [$x, $by - 0.004, $d * 0.2], segments: 16);
        }
        foreach ([-0.1, 0.0] as $k) {
            $b->cylinder('black', 0.0085, 0.04, [$k * $w, $by - 0.02, $d * 0.2], segments: 16);
        }
        foreach ([0.05, 0.22] as $k) {
            $b->cylinder('black', 0.016, 0.055, [$k * $w + 0.0, $by - 0.0275, -$d * 0.05], segments: 20);
            $b->cylinder('steel', 0.019, 0.01, [$k * $w, $by - 0.05, -$d * 0.05], segments: 20);
        }
        $b->box('black', [0.05, 0.03, 0.04], [$w * 0.36, $by - 0.015, $d * 0.18], radius: 0.003);      // breaker block
        $b->box('white', [0.012, 0.016, 0.014], [$w * 0.36, $by - 0.036, $d * 0.18], radius: 0.002);
        $b->box('black', [0.024, 0.012, 0.016], [$w * 0.12, $by - 0.006, $d * 0.3], radius: 0.002);   // RS232 / USB
        $b->box('steel', [0.016, 0.003, 0.01], [$w * 0.12, $by - 0.0125, $d * 0.3]);
        $b->cylinder('black', 0.011, 0.034, [$w * 0.24, $by - 0.017, $d * 0.3], segments: 16);          // Wi-Fi dongle
        $b->cylinder('white', 0.0045, 0.004, [$w * 0.24, $by - 0.035, $d * 0.3], segments: 12);

        if ($o['dc_switch'] ?? false) {
            $b->box('black', [0.06, 0.06, 0.03], [-$w * 0.34, -$h * 0.36, $z + 0.015], radius: 0.006);
            $b->cylinder('red', 0.019, 0.016, [-$w * 0.34, -$h * 0.36, $z + 0.036], [90, 0, 0], 24);
            $b->box('red', [0.034, 0.012, 0.012], [-$w * 0.34, -$h * 0.36, $z + 0.047], [0, 0, 30], radius: 0.002);
        }

        // wall bracket
        $b->box('steel', [$w * 1.1, 0.03, 0.008], [0, $h * 0.4, -$d / 2 - 0.004]);
        $b->box('steel', [$w * 1.1, 0.03, 0.008], [0, -$h * 0.4, -$d / 2 - 0.004]);
        foreach ([-1, 1] as $s) {
            $b->cylinder('steel', 0.006, 0.004, [$s * $w * 0.52, $h * 0.4, -$d / 2 - 0.008], [90, 0, 0], 14);
            $b->cylinder('steel', 0.006, 0.004, [$s * $w * 0.52, -$h * 0.4, -$d / 2 - 0.008], [90, 0, 0], 14);
        }

        if ($o['fins'] ?? false) {
            $b->box('steel', [0.008, $h * 0.9, $d * 0.86], [$w / 2 + 0.004, 0, 0]);
            for ($i = 0; $i < 12; $i++) {
                $b->box('steel', [0.04, $h * 0.88, 0.004], [$w / 2 + 0.028, 0, -$d * 0.42 + $i * ($d * 0.84 / 11)]);
            }
        } else {
            for ($i = 0; $i < 6; $i++) {   // side vent louvres
                $b->box('black', [0.002, $h * 0.5, 0.006], [$w / 2 + 0.0008, 0, -$d * 0.3 + $i * 0.014], radius: 0.0008, segments: 1);
            }
        }

        return $b;
    }

    // -- batteries ------------------------------------------------------------------

    /** @param  array<string, mixed>  $o */
    private static function rackBattery(string $name, array $o): GlbBuilder
    {
        $b = self::base($name)
            ->material('body', $o['body'], 0.45, 0.5)
            ->material('accent', $o['accent'], 0.3, 0.4);

        self::rackModule($b, 0.0, 0.48, 0.13, 0.4, $o);

        return $b;
    }

    /**
     * One 19" rack battery module centred on y = $y; builds its printed face the first time.
     *
     * @param  array<string, mixed>  $o
     */
    private static function rackModule(GlbBuilder $b, float $y, float $w, float $h, float $d, array $o): void
    {
        $fw = $w - 0.012;
        $fh = $h - 0.012;

        $p = new Panel($fw, $fh, 1024, $o['plate']);
        $p->gradient(Panel::mix($o['plate'], '#ffffff', 0.16), Panel::mix($o['plate'], '#000000', 0.18));
        $p->rect(0, $fh / 2 - 0.0035, $fw, 0.007, $o['accent']);
        $p->text($o['brand'], -$fw / 2 + 0.032, $fh * 0.2, $fh * 0.1, $o['accent'], 'left', true, null, 0.0012);
        $p->text($o['spec'], -$fw / 2 + 0.032, $fh * 0.2 - 0.02, 0.0052, '#e5e7eb', 'left', true);
        $p->text('LiFePO4 RACK BATTERY · 16S1P', -$fw / 2 + 0.032, $fh * 0.2 - 0.0305, 0.0036, '#9ca3af', 'left');
        $p->text('CE  RoHS  UN38.3', -$fw / 2 + 0.032, $fh * 0.2 - 0.0405, 0.0034, '#9ca3af', 'left');

        // status LEDs: RUN / ALM and the 4-step state of charge
        $lx = -0.075;
        foreach (['RUN', 'ALM'] as $i => $label) {
            $p->circle($lx + $i * 0.022, $fh * 0.17, 0.0048, '#06080a')->text($label, $lx + $i * 0.022, $fh * 0.17 + 0.0105, 0.0032, '#cbd5e1');
        }
        foreach (['25', '50', '75', '100'] as $i => $label) {
            $p->circle($lx - 0.004 + $i * 0.0145, -$fh * 0.1, 0.0042, '#06080a')->text($label, $lx - 0.004 + $i * 0.0145, -$fh * 0.1 - 0.0095, 0.0029, '#cbd5e1');
        }
        $p->text('SOC %', $lx + 0.021, -$fh * 0.1 + 0.012, 0.0032, '#cbd5e1');

        // communication sockets
        $px = 0.045;
        foreach (['RS485', 'CAN', 'RS232', 'LINK0', 'LINK1'] as $i => $label) {
            $p->rect($px + $i * 0.0215, $fh * 0.06, 0.0185, 0.0135, '#07090c', 0.001)->text($label, $px + $i * 0.0215, $fh * 0.06 + 0.0115, 0.0029, '#cbd5e1');
        }
        // reset, address dip-switch, breaker
        $p->circle($px + 0.002, -$fh * 0.28, 0.0045, '#07090c')->text('RESET', $px + 0.014, -$fh * 0.28, 0.0029, '#cbd5e1', 'left');
        $p->rect($px + 0.058, -$fh * 0.28, 0.026, 0.011, '#07090c', 0.001);
        foreach ([0, 1, 2, 3] as $i) {
            $p->rect($px + 0.0495 + $i * 0.0063, -$fh * 0.28 + ($i % 2 ? 0.0015 : -0.0015), 0.0038, 0.005, '#e5e7eb');
        }
        $p->text('ADD', $px + 0.058, -$fh * 0.28 - 0.0105, 0.0029, '#cbd5e1');
        $p->text('B+', 0.171, $fh * 0.27, 0.0042, '#fca5a5', 'center', true)->text('B-', 0.19, $fh * 0.27, 0.0042, '#e5e7eb', 'center', true);
        $p->text('ON/OFF', 0.138, -$fh * 0.1, 0.0032, '#cbd5e1');

        $png = $p->png();
        $material = 'front'.substr(md5($png['map']), 0, 6);
        self::front($b, $material, $png, 0.35, 0.45);

        $z = $d / 2;
        $b->box('body', [$w, $h, $d], [0, $y, 0], radius: 0.007, segments: 3);
        $b->plane($material, $fw, $fh, [0, $y, $z + 0.0006]);

        foreach ([-1, 1] as $s) {
            $b->box('steel', [0.024, $h, 0.005], [$s * ($w / 2 + 0.009), $y, $z - 0.01]);                 // rack ears
            foreach ([-1, 1] as $t) {
                $b->box('black', [0.006, 0.014, 0.0008], [$s * ($w / 2 + 0.009), $y + $t * $h * 0.3, $z - 0.0075], radius: 0.002, segments: 1);   // slotted holes
            }
            $b->box('accent', [0.012, $h * 0.5, 0.02], [$s * ($w / 2 - 0.02), $y, $z + 0.011], radius: 0.004);   // handles
        }

        foreach ([0, 1] as $i) {
            self::led($b, $i ? 'led_y' : 'led_g', $lx + $i * 0.022, $y + $fh * 0.17, $z, 0.0038);
        }
        foreach ([0, 1, 2, 3] as $i) {
            self::led($b, $i < 3 ? 'led_g' : 'led_y', $lx - 0.004 + $i * 0.0145, $y - $fh * 0.1, $z, 0.0034);
        }
        foreach ([0, 1, 2, 3, 4] as $i) {
            self::socket($b, $px + $i * 0.0215, $y + $fh * 0.06, $z, 0.0165, 0.0115);
        }
        self::button($b, $px + 0.002, $y - $fh * 0.28, $z, 0.0036);

        // breaker rocker and power terminals with covers
        $b->box('black', [0.022, 0.016, 0.01], [0.138, $y - $fh * 0.02, $z + 0.005], radius: 0.002, segments: 1);
        $b->box('white', [0.01, 0.01, 0.006], [0.138, $y - $fh * 0.02 + 0.002, $z + 0.012], [-12, 0, 0], radius: 0.001, segments: 1);
        foreach ([[0.171, 'red'], [0.19, 'black']] as [$x, $m]) {
            $b->box('rubber', [0.016, 0.026, 0.008], [$x, $y - $fh * 0.12, $z + 0.004], radius: 0.002, segments: 1);
            $b->cylinder($m, 0.0072, 0.012, [$x, $y - $fh * 0.12, $z + 0.012], [90, 0, 0], 18);
            $b->cylinder('steel', 0.0036, 0.004, [$x, $y - $fh * 0.12, $z + 0.019], [90, 0, 0], 12);
        }
    }

    private static function batteryCabinet(string $name, string $accent): GlbBuilder
    {
        $b = self::base($name)
            ->material('body', '#2b2f36', 0.45, 0.5)
            ->material('accent', $accent, 0.3, 0.4)
            ->material('frame', '#5b6470', 0.75, 0.4);

        $w = 0.54;
        $d = 0.44;
        $h = 0.76;
        $opts = ['plate' => '#20242a', 'accent' => $accent, 'brand' => 'TOBAC', 'spec' => '48V 100Ah · 5.12kWh'];

        foreach ([-1, 1] as $sx) {
            foreach ([-1, 1] as $sz) {
                $b->box('frame', [0.025, $h, 0.025], [$sx * $w / 2, 0, $sz * $d / 2], radius: 0.003, segments: 1);
                $b->cylinder('rubber', 0.016, 0.02, [$sx * ($w / 2 - 0.01), -$h / 2 - 0.02, $sz * ($d / 2 - 0.01)], segments: 16);   // feet
            }
        }
        foreach ([-1, 1] as $s) {
            $b->box('frame', [$w + 0.025, 0.02, $d + 0.025], [0, $s * $h / 2, 0], radius: 0.004);
        }
        $b->box('frame', [0.012, $h, $d], [-$w / 2 + 0.006, 0, 0]);           // side panels
        $b->box('frame', [0.012, $h, $d], [$w / 2 - 0.006, 0, 0]);
        $b->box('frame', [$w, $h, 0.008], [0, 0, -$d / 2 + 0.004]);           // back plate

        foreach ([0, 1, 2, 3] as $i) {
            self::rackModule($b, -0.27 + $i * 0.18, 0.46, 0.14, 0.38, $opts);
        }

        return $b;
    }

    /** @param  array<string, mixed>  $o */
    private static function wallBattery(string $name, array $o): GlbBuilder
    {
        $w = $o['w'] ?? 0.66;
        $h = $o['h'] ?? 0.43;
        $d = $o['d'] ?? 0.15;
        $fw = $w - 0.024;
        $fh = $h - 0.024;

        $p = new Panel($fw, $fh, 1024, $o['body']);
        $p->gradient(Panel::mix($o['body'], '#ffffff', 0.35), Panel::mix($o['body'], '#000000', 0.12));
        $p->rect(0, $fh / 2 - 0.012, $fw, 0.024, $o['accent']);
        $p->text($o['brand'], -$fw / 2 + 0.025, $fh / 2 - 0.012, 0.0095, '#ffffff', 'left', true, null, 0.0012);
        $p->text('LiFePO4 BATTERY', $fw / 2 - 0.025, $fh / 2 - 0.012, 0.0065, '#ffffff', 'right');

        $pw = $fw * 0.9;
        $ph = $fh * 0.52;
        $py = $fh / 2 - 0.04 - $ph / 2;
        $p->rect(0, $py, $pw + 0.004, $ph + 0.004, Panel::mix($o['panel'], '#ffffff', 0.3), 0.01);
        $p->rect(0, $py, $pw, $ph, $o['panel'], 0.009);
        $p->text($o['spec'], 0, $py + $ph * 0.38, 0.0085, '#e5e7eb', 'center', true);
        $p->text('SOC', -$pw * 0.4, $py + $ph * 0.08, 0.0065, '#9ca3af', 'left', true);
        foreach (['20', '40', '60', '80', '100'] as $i => $l) {
            $x = -$pw * 0.2 + $i * $pw * 0.1;
            $p->circle($x, $py + $ph * 0.08, 0.0075, '#06090c')->text($l, $x, $py + $ph * 0.08 - 0.017, 0.0036, '#9ca3af');
        }
        $p->text('RUN', $pw * 0.34, $py + $ph * 0.08, 0.0048, '#9ca3af')->text('ALM', $pw * 0.42, $py + $ph * 0.08, 0.0048, '#9ca3af');
        $p->circle($pw * 0.34, $py + $ph * 0.08 - 0.015, 0.0055, '#06090c')->circle($pw * 0.42, $py + $ph * 0.08 - 0.015, 0.0055, '#06090c');
        $p->text('POWER', -$pw * 0.36, $py - $ph * 0.3, 0.0042, '#9ca3af')->circle(-$pw * 0.36, $py - $ph * 0.12, 0.011, '#06090c');
        foreach (['RS485', 'CAN', 'LINK'] as $i => $l) {
            $x = -$pw * 0.1 + $i * $pw * 0.14;
            $p->rect($x, $py - $ph * 0.16, 0.026, 0.017, '#06090c', 0.001)->text($l, $x, $py - $ph * 0.3, 0.0042, '#9ca3af');
        }

        $sy = -$fh / 2 + 0.07;
        $p->rect(-$fw * 0.18, $sy, $fw * 0.5, 0.075, '#ffffff', 0.002);
        $p->text($o['brand'], -$fw * 0.18 - $fw * 0.24, $sy + 0.024, 0.0048, '#111827', 'left', true);
        $p->text('Nominal 51.2VDC  100Ah  5.12kWh', -$fw * 0.18 - $fw * 0.24, $sy + 0.006, 0.0036, '#374151', 'left');
        $p->text('Charge max 100A  Discharge max 100A', -$fw * 0.18 - $fw * 0.24, $sy - 0.006, 0.0036, '#374151', 'left');
        $p->barcode(-$fw * 0.18 - $fw * 0.06, $sy - 0.023, $fw * 0.28, 0.015, $o['spec']);
        $p->text('B+', $fw * 0.28, -$fh / 2 + 0.058, 0.0055, '#dc2626', 'center', true)->text('B-', $fw * 0.38, -$fh / 2 + 0.058, 0.0055, '#111827', 'center', true);

        $png = $p->png();
        $b = self::base($name)
            ->material('body', $o['body'], 0.25, 0.5)
            ->material('accent', $o['accent'], 0.3, 0.4);
        self::front($b, 'front', $png, 0.15, 0.5);

        $z = $d / 2;
        $b->box('body', [$w, $h, $d], radius: 0.014, segments: 4);
        $b->plane('front', $fw, $fh, [0, 0, $z + 0.0006]);

        for ($i = 0; $i < 5; $i++) {
            self::led($b, $i < 4 ? 'led_g' : 'led_y', -$pw * 0.2 + $i * $pw * 0.1, $py + $ph * 0.08, $z, 0.0058);
        }
        self::led($b, 'led_g', $pw * 0.34, $py + $ph * 0.08 - 0.015, $z, 0.0044);
        self::led($b, 'led_r', $pw * 0.42, $py + $ph * 0.08 - 0.015, $z, 0.0044);
        self::button($b, -$pw * 0.36, $py - $ph * 0.12, $z, 0.0085);
        foreach ([0, 1, 2] as $i) {
            self::socket($b, -$pw * 0.1 + $i * $pw * 0.14, $py - $ph * 0.16, $z, 0.024, 0.015);
        }
        foreach ([[0.28, 'red'], [0.38, 'black']] as [$k, $m]) {
            $x = $fw * $k;
            $b->box('rubber', [0.034, 0.034, 0.01], [$x, -$fh / 2 + 0.03, $z + 0.005], radius: 0.004, segments: 1);
            $b->cylinder($m, 0.013, 0.02, [$x, -$fh / 2 + 0.03, $z + 0.016], [90, 0, 0], 20);
            $b->cylinder('steel', 0.006, 0.006, [$x, -$fh / 2 + 0.03, $z + 0.027], [90, 0, 0], 14);
        }

        // carry handle, wall plate and top vents
        $b->box('accent', [$w * 0.2, 0.018, 0.04], [0, $h / 2 + 0.01, 0], radius: 0.006);
        $b->box('steel', [$w * 1.04, 0.03, 0.008], [0, $h * 0.38, -$d / 2 - 0.004]);
        for ($i = -3; $i <= 3; $i++) {
            $b->box('black', [0.004, 0.0012, $d * 0.5], [$i * $w * 0.06, $h / 2 + 0.0004, 0], radius: 0.0005, segments: 1);
        }

        return $b;
    }

    // -- charge controllers ---------------------------------------------------------

    /** @param  array<string, mixed>  $o */
    private static function controller(string $name, array $o): GlbBuilder
    {
        ['w' => $w, 'h' => $h, 'd' => $d] = $o;
        $fw = $w - 0.02;
        $fh = $h - 0.02;
        $victron = $o['victron'] ?? false;
        $pwm = $o['pwm'] ?? false;
        $ink = ($o['light_text'] ?? false) ? '#ffffff' : '#111827';
        $inkSoft = ($o['light_text'] ?? false) ? '#dbeafe' : '#4b5563';

        $p = new Panel($fw, $fh, 1024, $o['body']);
        $p->gradient(Panel::mix($o['body'], '#ffffff', 0.3), Panel::mix($o['body'], '#000000', 0.1));
        $top = $fh / 2;

        if ($victron) {
            $p->text('victron energy', 0, $top - 0.03, 0.011, '#ffffff', 'center', true);
            $p->text('SmartSolar', 0, $top - 0.075, 0.017, '#ffffff', 'center', true);
            $p->text('MPPT 150/35', 0, $top - 0.105, 0.0095, '#ffffff', 'center', true);
            $p->rect(0, 0.0, $fw * 0.82, 0.052, '#ffffff', 0.005);
            $p->text('BULK', -$fw * 0.28, 0.012, 0.0055, '#1d4f8f', 'center', true)
                ->text('ABS', 0, 0.012, 0.0055, '#1d4f8f', 'center', true)
                ->text('FLOAT', $fw * 0.28, 0.012, 0.0055, '#1d4f8f', 'center', true);
            foreach ([-0.28, 0.0, 0.28] as $k) {
                $p->circle($fw * $k, -0.01, 0.0075, '#9db4cf');
            }
            $p->text('VE.Direct', 0, -$fh * 0.28, 0.006, '#dbeafe')->text('BLUETOOTH', 0, -$fh * 0.28 - 0.014, 0.0045, '#dbeafe');
        } else {
            $p->rect(0, $top - 0.012, $fw, 0.024, $o['accent']);
            $p->text($o['brand'], -$fw / 2 + 0.02, $top - 0.012, 0.0085, $o['accent'] === '#e5e7eb' ? '#1d4ed8' : '#ffffff', 'left', true, null, 0.001);
            $p->text($o['model'], $fw / 2 - 0.02, $top - 0.012, 0.0055, $o['accent'] === '#e5e7eb' ? '#1d4ed8' : '#ffffff', 'right');

            $pw = $fw * 0.88;
            $ph = $fh * ($pwm ? 0.5 : 0.46);
            $py = $top - 0.035 - $ph / 2;
            $p->rect(0, $py, $pw + 0.004, $ph + 0.004, Panel::mix($o['panel'], '#ffffff', 0.3), 0.009);
            $p->rect(0, $py, $pw, $ph, $o['panel'], 0.008);

            $lw = $pw * 0.8;
            $lh = $ph * 0.6;
            $ly = $py + $ph * 0.12;
            $p->rect(0, $ly, $lw + 0.005, $lh + 0.005, '#05070a', 0.003);
            $p->rect(0, $ly, $lw, $lh, Panel::mix($o['lcd'], '#000000', 0.88), 0.002, Panel::mix($o['lcd'], '#000000', 0.78));
            $c = $o['lcd'];
            $p->sun(-$lw * 0.3, $ly + $lh * 0.27, $lh * 0.14, $c, true)
                ->battery($lw * 0.3, $ly + $lh * 0.27, $lh * 0.34, $lh * 0.2, 3, $c, true)
                ->line(-$lw * 0.14, $ly + $lh * 0.27, $lw * 0.1, $ly + $lh * 0.27, $c, $lh * 0.014, true)
                ->text('PV 82.4V', -$lw * 0.3, $ly - $lh * 0.02, $lh * 0.085, $c, 'center', false, true)
                ->text('BAT 53.8V', $lw * 0.3, $ly - $lh * 0.02, $lh * 0.085, $c, 'center', false, true)
                ->text('32.5', -$lw * 0.18, $ly - $lh * 0.3, $lh * 0.2, $c, 'center', true, true)
                ->text('A', -$lw * 0.02, $ly - $lh * 0.32, $lh * 0.1, $c, 'center', false, true)
                ->text('1.75kW', $lw * 0.25, $ly - $lh * 0.3, $lh * 0.12, $c, 'center', false, true);

            $by = $py - $ph * 0.33;
            foreach ($pwm ? [-0.18, 0.18] : [-0.3, -0.1, 0.1, 0.3] as $k) {
                $p->circle($pw * $k, $by, 0.0098, '#07090c');
            }
            foreach ($pwm ? ['MENU' => -0.18, 'SET' => 0.18] : ['MENU' => -0.3, 'SET' => -0.1, 'UP' => 0.1, 'DOWN' => 0.3] as $l => $k) {
                $p->text($l, $pw * $k, $by - 0.0165, 0.0038, '#aeb7c4');
            }
            foreach ([-0.2, 0.0, 0.2] as $i => $k) {
                $p->circle($pw * $k, $py + $ph / 2 - 0.007, 0.004, '#07090c');
            }
        }

        // rating label near the terminal block
        $p->rect(0, -$fh / 2 + 0.034, $fw * 0.82, 0.032, '#ffffff', 0.002);
        $p->text($o['brand'].' '.$o['model'], 0, -$fh / 2 + 0.043, 0.0042, '#111827', 'center', true);
        $p->text($o['rating'], 0, -$fh / 2 + 0.028, 0.0034, '#374151');
        $p->text('PV', -$fw * 0.3, -$fh / 2 + 0.01, 0.0036, $inkSoft, 'center', true)
            ->text('BAT', 0, -$fh / 2 + 0.01, 0.0036, $inkSoft, 'center', true)
            ->text('LOAD', $fw * 0.3, -$fh / 2 + 0.01, 0.0036, $inkSoft, 'center', true);

        $png = $p->png();
        $b = self::base($name)
            ->material('body', $o['body'], 0.3, 0.5)
            ->material('accent', $o['accent'], 0.3, 0.4);
        self::front($b, 'front', $png, 0.15, 0.5);

        $z = $d / 2;
        $b->box('body', [$w, $h, $d], radius: 0.012, segments: 4);
        $b->plane('front', $fw, $fh, [0, 0, $z + 0.0006]);

        if ($victron) {
            foreach ([-0.28, 0.0, 0.28] as $i => $k) {
                self::led($b, ['led_g', 'led_y', 'led_y'][$i], $fw * $k, -0.01, $z, 0.0058);
            }
        } else {
            $pw = $fw * 0.88;
            $ph = $fh * ($pwm ? 0.5 : 0.46);
            $py = $top - 0.035 - $ph / 2;
            $by = $py - $ph * 0.33;
            foreach ($pwm ? [-0.18, 0.18] : [-0.3, -0.1, 0.1, 0.3] as $k) {
                self::button($b, $pw * $k, $by, $z, 0.0082);
            }
            foreach (['led_g', 'led_y', 'led_r'] as $i => $m) {
                self::led($b, $m, $pw * [-0.2, 0.0, 0.2][$i], $py + $ph / 2 - 0.007, $z, 0.0036);
            }
        }

        // terminal blocks (PV / battery / load) on top and bottom
        foreach ([1, -1] as $s) {
            $b->box('black', [$w * 0.86, 0.024, $d * 0.7], [0, $s * ($h / 2 + 0.01), 0], radius: 0.004, segments: 2);
            foreach ([-1.5, -0.5, 0.5, 1.5] as $i) {
                $b->cylinder('steel', 0.0072, 0.006, [$i * $w * 0.2, $s * ($h / 2 + 0.0225), 0], segments: 14);
                $b->box('black', [0.007, 0.0008, 0.0016], [$i * $w * 0.2, $s * ($h / 2 + 0.0258), 0], [0, 35, 0]);
            }
            $b->box('white', [$w * 0.7, 0.0012, $d * 0.3], [0, $s * ($h / 2 + 0.0206), $d * 0.24]);
        }

        // mounting ears
        foreach ([-1, 1] as $s) {
            foreach ([1, -1] as $t) {
                $b->box('steel', [0.03, 0.012, $d * 0.9], [$s * ($w / 2 + 0.014), $t * $h * 0.4, 0]);
                $b->cylinder('black', 0.0045, 0.0014, [$s * ($w / 2 + 0.017), $t * $h * 0.4 + 0.0065, 0], [0, 0, 0], 12);
            }
        }

        if ($o['fins'] ?? true) {
            $b->box('steel', [$w * 0.96, $h * 0.96, 0.006], [0, 0, -$d / 2 - 0.003]);
            for ($i = 0; $i < 11; $i++) {
                $b->box('steel', [$w * 0.96, 0.0065, 0.036], [0, -$h * 0.43 + $i * ($h * 0.86 / 10), -$d / 2 - 0.024]);
            }
        }

        return $b;
    }

    // -- breakers -------------------------------------------------------------------

    private const POLE = 0.0175;

    private static function breakerBase(string $name): GlbBuilder
    {
        return self::base($name)
            ->material('mcb', '#eef0f3', 0.05, 0.4)
            ->material('mcb_dc', '#dde6fa', 0.05, 0.4)
            ->material('orange', '#f97316', 0.15, 0.45)
            ->material('rail', '#b6bec8', 0.9, 0.3)
            ->material('mccb', '#1d232b', 0.25, 0.5)
            ->material('handle', '#14171c', 0.2, 0.45);
    }

    /** The label printed on the front of the MCB poles. */
    private static function mcbFace(int $poles, bool $dc, string $rating): array
    {
        $p = self::POLE;
        $fw = $poles * $p - 0.0006;
        $fh = 0.039;
        $bg = $dc ? '#dde6fa' : '#eef0f3';
        $ink = $dc ? '#1e3a8a' : '#111827';

        $f = new Panel($fw, $fh, 1024, $bg);
        $f->gradient(Panel::mix($bg, '#ffffff', 0.5), Panel::mix($bg, '#000000', 0.04));
        for ($i = 1; $i < $poles; $i++) {
            $f->line(-$fw / 2 + $i * $p, $fh / 2, -$fw / 2 + $i * $p, -$fh / 2, '#b9c0c9', 0.0004);
        }
        $f->text('AL-NASR', -$fw / 2 + 0.0015, $fh / 2 - 0.0042, 0.0026, $ink, 'left', true);
        $f->text($dc ? 'DC 500V' : '230/400V~', $fw / 2 - 0.0015, $fh / 2 - 0.0042, 0.0022, $ink, 'right', true);

        $big = ($poles >= 3 ? $poles * $p * 0.5 : $fw / 2);
        $f->text($dc ? str_replace('C', '', $rating).'A' : $rating, -$fw / 2 + $big * ($poles >= 3 ? 1.0 : 0.95), -0.003, 0.0095, $ink, 'center', true);
        $f->text($dc ? 'PV  IEC 60947-2' : 'IEC/EN 60898-1', -$fw / 2 + $big * ($poles >= 3 ? 1.0 : 0.95), -$fh / 2 + 0.0095, 0.0017, '#4b5563');
        $f->text($dc ? '4kA' : '6000', -$fw / 2 + 0.0015, -$fh / 2 + 0.0035, 0.0019, '#4b5563', 'left');
        if ($dc) {
            for ($i = 0; $i < $poles; $i++) {
                $x = -$fw / 2 + ($i + 0.5) * $p;
                $f->text($i % 2 ? '–' : '+', $x, $fh / 2 - 0.0125, 0.0045, '#dc2626', 'center', true);
                $f->text($i % 2 ? '–' : '+', $x, -$fh / 2 + 0.0035, 0.0034, '#dc2626', 'center', true);
            }
        }

        return $f->png();
    }

    /** One MCB pole, centred at x. */
    private static function pole(GlbBuilder $b, float $x, bool $dc = false): void
    {
        $p = self::POLE;
        $b->box($dc ? 'mcb_dc' : 'mcb', [$p, 0.09, 0.06], [$x, 0, 0], radius: 0.0018, segments: 2);
        foreach ([1, -1] as $s) {
            $b->box('black', [$p * 0.7, 0.002, 0.024], [$x, $s * 0.0452, 0.012]);                       // wire entry
            $b->box('steel', [$p * 0.8, 0.008, 0.03], [$x, $s * 0.046, 0.005]);
            $b->cylinder('steel', 0.0047, 0.004, [$x, $s * 0.0505, 0.012], segments: 14);
            $b->box('black', [0.0065, 0.0008, 0.0014], [$x, $s * 0.0528, 0.012], [0, 35, 0]);            // screw slot
        }
        $b->box('red', [$p * 0.5, 0.004, 0.0016], [$x, -0.0045, 0.0302]);                                // ON/OFF window
    }

    private static function mcb(string $name, int $poles, bool $dc = false, bool $shunt = false, string $rating = 'C32'): GlbBuilder
    {
        $b = self::breakerBase($name);
        $p = self::POLE;
        $width = $poles * $p + ($shunt ? $p : 0);
        $x0 = -$width / 2 + $p / 2;
        $fw = $poles * $p - 0.0006;

        self::front($b, 'label', self::mcbFace($poles, $dc, $rating), 0.05, 0.4);

        $b->box('rail', [$width + 0.04, 0.035, 0.008], [0, 0, -0.034]);                                   // DIN rail
        $b->box('black', [$width + 0.04, 0.012, 0.0012], [0, 0, -0.0295]);
        foreach ([-1, 1] as $s) {
            $b->box('black', [0.004, 0.0012, 0.006], [$s * ($width / 2 + 0.014), 0, -0.0295]);
        }

        for ($i = 0; $i < $poles; $i++) {
            self::pole($b, $x0 + $i * $p, $dc);
            $b->box('black', [$p * 0.86, 0.03, 0.004], [$x0 + $i * $p, 0.027, 0.0308], radius: 0.001, segments: 1);   // handle window
            $b->box('handle', [$p * 0.6, 0.022, 0.014], [$x0 + $i * $p, 0.028, 0.036], [-10, 0, 0], radius: 0.002, segments: 2);
            foreach ([0, 1, 2] as $r) {                                                                  // grip ridges
                $b->box('handle', [$p * 0.54, 0.0012, 0.0012], [$x0 + $i * $p, 0.021 + $r * 0.0045, 0.0432 - $r * 0.0008], [-10, 0, 0], radius: 0.0004, segments: 1);
            }
            $b->box('handle', [$p * 0.5, 0.012, 0.004], [$x0 + $i * $p, -0.047, -0.028], radius: 0.001, segments: 1);   // DIN latch
        }
        if ($poles > 1) {
            $b->box('handle', [$poles * $p - 0.004, 0.005, 0.008], [$x0 + ($poles - 1) * $p / 2, 0.036, 0.038]);
        }
        $b->plane('label', $fw, 0.039, [$x0 + ($poles - 1) * $p / 2, -0.0235, 0.0308]);

        if ($shunt) {
            $sx = $x0 + $poles * $p;
            $f = new Panel($p - 0.0006, 0.039, 1024, '#f97316');
            $f->gradient('#fb923c', '#ea6a0c');
            $f->text('SHUNT', 0, 0.0115, 0.0019, '#111827', 'center', true)
                ->text('TRIP', 0, 0.0075, 0.0019, '#111827', 'center', true)
                ->text('230V~', 0, 0.0, 0.0022, '#111827', 'center', true)
                ->text('Y1  Y2', 0, -0.0085, 0.0018, '#111827', 'center', true);
            self::front($b, 'label_shunt', $f->png(), 0.05, 0.4);
            $b->box('orange', [$p, 0.09, 0.06], [$sx, 0, 0], radius: 0.0018, segments: 2);
            $b->plane('label_shunt', $p - 0.0006, 0.039, [$sx, -0.0235, 0.0308]);
            foreach ([1, -1] as $s) {
                $b->box('steel', [$p * 0.8, 0.008, 0.03], [$sx, $s * 0.046, 0.005]);
                $b->cylinder('steel', 0.0047, 0.004, [$sx, $s * 0.0505, 0.012], segments: 14);
            }
            foreach (['red', 'black'] as $k => $c) {
                $b->cylinder($c, 0.0022, 0.07, [$sx + ($k ? 0.004 : -0.004), 0.08, 0.012], [0, 0, 0], 8);
            }
        }

        return $b;
    }

    private static function mccbFace(bool $shunt): array
    {
        $fw = 0.098;
        $fh = 0.134;
        $f = new Panel($fw, $fh, 1024, '#1d232b');
        $f->gradient('#2a313b', '#171c23');

        $f->rect(0, $fh / 2 - 0.0075, $fw, 0.015, '#f97316');
        $f->text('AL-NASR ELECTRIC', -$fw / 2 + 0.006, $fh / 2 - 0.0075, 0.0042, '#111827', 'left', true, null, 0.0004);
        $f->text('MCCB', $fw / 2 - 0.006, $fh / 2 - 0.0075, 0.0048, '#111827', 'right', true);

        foreach (['L1' => -0.031, 'L2' => 0.0, 'L3' => 0.031] as $l => $x) {
            $f->text($l, $x, $fh / 2 - 0.0215, 0.0028, '#9ca3af')->text($l === 'L1' ? 'T1' : ($l === 'L2' ? 'T2' : 'T3'), $x, -$fh / 2 + 0.0035, 0.0028, '#9ca3af');
        }

        // handle window with ON / OFF / TRIP markers
        $f->rect(0, 0.016, 0.062, 0.058, '#0e1217', 0.004);
        $f->text('ON', 0, 0.0415, 0.0034, '#f87171', 'center', true)->text('I', 0.0275, 0.0415, 0.0034, '#f87171', 'center', true);
        $f->text('OFF', 0, -0.0095, 0.0034, '#d1d5db', 'center', true)->text('O', 0.0275, -0.0095, 0.0034, '#d1d5db', 'center', true);

        // trip dials and push-to-trip
        $f->dial(-0.0225, -0.034, 0.0145, 150, '#e5e7eb', '#111827');
        $f->dial(0.0225, -0.034, 0.0145, 100, '#e5e7eb', '#111827');
        $f->text('Ir', -0.0225, -0.0535, 0.0028, '#9ca3af')->text('Im', 0.0225, -0.0535, 0.0028, '#9ca3af');
        $f->circle(0.0395, 0.0415, 0.0058, '#0b0d10')->text('PUSH TO TRIP', 0.0395, 0.031, 0.0019, '#9ca3af');

        // rating plate
        $f->rect(0, -0.0585, 0.088, 0.0125, '#f3f4f6', 0.001);
        $f->text('100A', -0.032, -0.0585, 0.0058, '#111827', 'center', true);
        $f->text('Ue 415V~  Icu 36kA  IEC 60947-2', 0.013, -0.0585, 0.0024, '#374151', 'center', true);

        return $f->png();
    }

    private static function mccb(string $name, bool $shunt = false): GlbBuilder
    {
        $b = self::breakerBase($name);
        $w = 0.105;
        $h = 0.14;
        $d = 0.07;
        self::front($b, 'label', self::mccbFace($shunt), 0.2, 0.5);

        $b->box('mccb', [$w, $h, $d], radius: 0.004, segments: 3);
        $b->plane('label', 0.098, 0.134, [0, 0, $d / 2 + 0.0005]);
        $b->box('black', [$w * 0.94, 0.02, $d * 0.82], [0, $h / 2 - 0.004, 0], radius: 0.003);               // terminal covers
        $b->box('black', [$w * 0.94, 0.02, $d * 0.82], [0, -$h / 2 + 0.004, 0], radius: 0.003);
        foreach ([-1, 0, 1] as $i) {
            foreach ([1, -1] as $s) {
                $b->box('steel', [0.02, 0.006, 0.026], [$i * $w * 0.3, $s * ($h / 2 + 0.005), 0.004]);
                $b->cylinder('steel', 0.0065, 0.008, [$i * $w * 0.3, $s * ($h / 2 + 0.012), 0.004], segments: 14);
                $b->box('black', [0.009, 0.0008, 0.0018], [$i * $w * 0.3, $s * ($h / 2 + 0.0164), 0.004], [0, 35, 0]);
            }
        }

        // toggle handle with its collar, in the window
        $b->box('handle', [0.05, 0.03, 0.012], [0, 0.018, $d / 2 + 0.005], radius: 0.004);
        $b->box('handle', [0.03, 0.046, 0.026], [0, 0.03, $d / 2 + 0.016], [-14, 0, 0], radius: 0.006);
        foreach ([0, 1, 2, 3] as $r) {
            $b->box('handle', [0.026, 0.0014, 0.0016], [0, 0.015 + $r * 0.0075, $d / 2 + 0.0295 - $r * 0.0015], [-14, 0, 0], radius: 0.0004, segments: 1);
        }
        $b->cylinder('red', 0.0052, 0.004, [0.0395, 0.0415, $d / 2 + 0.0027], [90, 0, 0], 16);                  // push-to-trip

        if ($shunt) {
            $sw = 0.032;
            $f = new Panel($sw - 0.004, 0.12, 1024, '#f97316');
            $f->gradient('#fb923c', '#ea6a0c');
            $f->text('SHUNT', 0, 0.03, 0.0045, '#111827', 'center', true)
                ->text('TRIP', 0, 0.02, 0.0045, '#111827', 'center', true)
                ->text('230V~', 0, 0.0, 0.0042, '#111827', 'center', true)
                ->text('Y1', 0, -0.025, 0.0036, '#111827', 'center', true)
                ->text('Y2', 0, -0.036, 0.0036, '#111827', 'center', true);
            self::front($b, 'label_shunt', $f->png(), 0.1, 0.45);
            $b->box('orange', [$sw, $h * 0.96, $d * 0.96], [$w / 2 + $sw / 2, 0, 0], radius: 0.004);
            $b->plane('label_shunt', $sw - 0.004, 0.12, [$w / 2 + $sw / 2, 0, $d * 0.48 + 0.0005]);
            foreach (['red', 'black'] as $k => $c) {
                $b->cylinder($c, 0.0025, 0.07, [$w / 2 + $sw / 2 + ($k ? 0.006 : -0.006), $h / 2 + 0.03, 0], segments: 8);
            }
        }

        return $b;
    }
}
