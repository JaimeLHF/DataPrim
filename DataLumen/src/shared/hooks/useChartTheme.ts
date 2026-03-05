import { useSyncExternalStore } from 'react';

const listeners = new Set<() => void>();

const observer = new MutationObserver(() => {
    listeners.forEach(fn => fn());
});

if (typeof document !== 'undefined') {
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
}

function subscribe(cb: () => void) {
    listeners.add(cb);
    return () => listeners.delete(cb);
}

function getCssVar(name: string): string {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
}

function getSnapshot() {
    return document.documentElement.getAttribute('data-theme') ?? 'dark';
}

export function useChartTheme() {
    const theme = useSyncExternalStore(subscribe, getSnapshot);

    return {
        grid: getCssVar('--chart-grid'),
        tick: getCssVar('--chart-tick'),
        tooltipBg: getCssVar('--chart-tooltip-bg'),
        tooltipBorder: getCssVar('--chart-tooltip-border'),
        label: getCssVar('--chart-label'),
        polarGrid: getCssVar('--chart-polar-grid'),
        _theme: theme,
    };
}
