import {
    BadgeCheck,
    BatteryCharging,
    BellRing,
    Cctv,
    ClipboardCheck,
    Cpu,
    Factory,
    HardHat,
    Headset,
    ShieldCheck,
    Sun,
    Truck,
    Wrench,
    Zap,
} from 'lucide-vue-next';
import type { Component } from 'vue';

/** Keys match HomePageSectionForm::ICONS on the PHP side. */
export const ICONS: Record<string, Component> = {
    sun: Sun,
    zap: Zap,
    battery: BatteryCharging,
    factory: Factory,
    cpu: Cpu,
    cctv: Cctv,
    bell: BellRing,
    wrench: Wrench,
    shield: ShieldCheck,
    truck: Truck,
    headset: Headset,
    badge: BadgeCheck,
    clipboard: ClipboardCheck,
    hardhat: HardHat,
};

export function iconFor(key?: string | null): Component {
    return (key && ICONS[key]) || Sun;
}
