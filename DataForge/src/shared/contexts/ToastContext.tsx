import { createContext, useContext, useState, useCallback, useRef, type ReactNode } from 'react';

// ── Tipos ─────────────────────────────────────────────────────────────────────

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
    id: number;
    type: ToastType;
    title: string;
    message?: string;
    duration?: number; // ms, 0 = permanente
}

interface ToastContextValue {
    toasts: Toast[];
    toast: (opts: Omit<Toast, 'id'>) => void;
    success: (title: string, message?: string) => void;
    error: (title: string, message?: string) => void;
    warning: (title: string, message?: string) => void;
    info: (title: string, message?: string) => void;
    dismiss: (id: number) => void;
}

// ── Context ───────────────────────────────────────────────────────────────────

const ToastContext = createContext<ToastContextValue | null>(null);

// ── Provider ──────────────────────────────────────────────────────────────────

export function ToastProvider({ children }: { children: ReactNode }) {
    const [toasts, setToasts] = useState<Toast[]>([]);
    const counter = useRef(0);

    const dismiss = useCallback((id: number) => {
        setToasts(prev => prev.filter(t => t.id !== id));
    }, []);

    const toast = useCallback((opts: Omit<Toast, 'id'>) => {
        const id = ++counter.current;
        const duration = opts.duration ?? (opts.type === 'error' ? 6000 : 4000);

        setToasts(prev => [...prev, { ...opts, id, duration }]);

        if (duration > 0) {
            setTimeout(() => dismiss(id), duration);
        }

        return id;
    }, [dismiss]);

    const success = useCallback((title: string, message?: string) =>
        toast({ type: 'success', title, message }), [toast]);

    const error = useCallback((title: string, message?: string) =>
        toast({ type: 'error', title, message }), [toast]);

    const warning = useCallback((title: string, message?: string) =>
        toast({ type: 'warning', title, message }), [toast]);

    const info = useCallback((title: string, message?: string) =>
        toast({ type: 'info', title, message }), [toast]);

    return (
        <ToastContext.Provider value={{ toasts, toast, success, error, warning, info, dismiss }}>
            {children}
        </ToastContext.Provider>
    );
}

// ── Hook ──────────────────────────────────────────────────────────────────────

export function useToast(): ToastContextValue {
    const ctx = useContext(ToastContext);
    if (!ctx) throw new Error('useToast must be used inside <ToastProvider>');
    return ctx;
}
