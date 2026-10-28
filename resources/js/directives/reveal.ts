import type { Directive } from 'vue';

/**
 * v-reveal — fades an element up once it scrolls into view.
 * Optional binding value is a delay in ms (handy for staggering grids).
 * Falls back to "visible" when IntersectionObserver is unavailable.
 */
let observer: IntersectionObserver | null = null;

function getObserver(): IntersectionObserver | null {
    if (typeof IntersectionObserver === 'undefined') return null;
    observer ??= new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer?.unobserve(entry.target);
                }
            }
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
    );
    return observer;
}

export const reveal: Directive<HTMLElement, number | undefined> = {
    mounted(el, binding) {
        const io = getObserver();
        el.classList.add('reveal');
        if (binding.value) el.style.setProperty('--reveal-delay', `${binding.value}ms`);
        if (!io) {
            el.classList.add('is-visible');
            return;
        }
        io.observe(el);
    },
    unmounted(el) {
        observer?.unobserve(el);
    },
};
