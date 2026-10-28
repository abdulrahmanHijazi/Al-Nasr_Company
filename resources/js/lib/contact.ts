/** Digits only, as wa.me expects (no +, spaces or dashes). */
export function whatsappLink(phone?: string | null, text?: string): string | null {
    const digits = (phone ?? '').replace(/\D/g, '');
    if (!digits) return null;
    return `https://wa.me/${digits}${text ? `?text=${encodeURIComponent(text)}` : ''}`;
}

export function telLink(phone?: string | null): string | null {
    const value = (phone ?? '').replace(/[^\d+]/g, '');
    return value ? `tel:${value}` : null;
}

/** Keyless OpenStreetMap embed centred on the company location. */
export function osmEmbedUrl(lat: number, lng: number, span = 0.006): string {
    const bbox = [lng - span, lat - span * 0.6, lng + span, lat + span * 0.6].map((n) => n.toFixed(6)).join('%2C');
    return `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${lat}%2C${lng}`;
}

export function directionsUrl(lat?: number | null, lng?: number | null, fallback?: string | null): string | null {
    if (fallback) return fallback;
    if (lat == null || lng == null) return null;
    return `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
}
