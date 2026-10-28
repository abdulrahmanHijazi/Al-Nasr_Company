/**
 * Colour helpers. The admin can pick any header / primary / secondary / footer
 * colour, so text that sits on top of them has to be chosen from luminance
 * instead of being hard-coded white or black (WCAG relative luminance).
 */

function hexToRgb(hex: string): [number, number, number] | null {
    const match = /^#?([\da-f]{3}|[\da-f]{6})$/i.exec(hex.trim());
    if (!match) return null;
    let value = match[1];
    if (value.length === 3) value = value.split('').map((c) => c + c).join('');
    const n = parseInt(value, 16);
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
}

function luminance([r, g, b]: [number, number, number]): number {
    const channel = (v: number) => {
        const s = v / 255;
        return s <= 0.03928 ? s / 12.92 : ((s + 0.055) / 1.055) ** 2.4;
    };
    return 0.2126 * channel(r) + 0.7152 * channel(g) + 0.0722 * channel(b);
}

/** Returns a dark or light foreground that reads best on `background`. */
export function readableOn(background: string, light = '#ffffff', dark = '#0f172a'): string {
    const rgb = hexToRgb(background);
    if (!rgb) return light;
    const bg = luminance(rgb) + 0.05;
    const lightRgb = hexToRgb(light);
    const darkRgb = hexToRgb(dark);
    const contrastLight = lightRgb ? (luminance(lightRgb) + 0.05) / bg : 1.05 / bg;
    const contrastDark = darkRgb ? bg / (luminance(darkRgb) + 0.05) : bg / 0.05;
    return contrastLight >= contrastDark ? light : dark;
}

export interface ThemeColors {
    primary_color?: string | null;
    secondary_color?: string | null;
    header_color?: string | null;
    footer_color?: string | null;
}

/** Sets --store-* and the matching --store-on-* custom properties on <html>. */
export function applyTheme(colors: ThemeColors, root: HTMLElement = document.documentElement) {
    const pairs: Array<[keyof ThemeColors, string]> = [
        ['primary_color', 'primary'],
        ['secondary_color', 'secondary'],
        ['header_color', 'header'],
        ['footer_color', 'footer'],
    ];

    for (const [key, name] of pairs) {
        const value = colors[key];
        if (!value) continue;
        root.style.setProperty(`--store-${name}`, value);
        root.style.setProperty(`--store-on-${name}`, readableOn(value));
    }
}
