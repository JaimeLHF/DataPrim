import { useToast, type Toast, type ToastType } from '../contexts/ToastContext';

// ── Configuração visual por tipo ───────────────────────────────────────────────

const CONFIG: Record<ToastType, { icon: string; color: string; bg: string; border: string }> = {
    success: { icon: '✅', color: '#22c55e', bg: 'rgba(34,197,94,0.1)', border: 'rgba(34,197,94,0.3)' },
    error: { icon: '❌', color: '#ef4444', bg: 'rgba(239,68,68,0.1)', border: 'rgba(239,68,68,0.3)' },
    warning: { icon: '⚠️', color: '#f59e0b', bg: 'rgba(245,158,11,0.1)', border: 'rgba(245,158,11,0.3)' },
    info: { icon: 'ℹ️', color: '#3b82f6', bg: 'rgba(59,130,246,0.1)', border: 'rgba(59,130,246,0.3)' },
};

// ── Toast Individual ──────────────────────────────────────────────────────────

function ToastItem({ toast }: { toast: Toast }) {
    const { dismiss } = useToast();
    const cfg = CONFIG[toast.type];

    return (
        <div
            className="toast-item"
            role="alert"
            aria-live="polite"
            style={{
                background: cfg.bg,
                border: `1px solid ${cfg.border}`,
                borderLeft: `4px solid ${cfg.color}`,
                borderRadius: 10,
                padding: '12px 14px',
                display: 'flex',
                gap: 12,
                alignItems: 'flex-start',
                minWidth: 300,
                maxWidth: 400,
                boxShadow: '0 4px 20px rgba(0,0,0,0.3)',
                backdropFilter: 'blur(8px)',
                animation: 'toast-enter 0.25s ease-out',
            }}
        >
            {/* Icon */}
            <span style={{ fontSize: 18, flexShrink: 0, lineHeight: 1.2 }}>{cfg.icon}</span>

            {/* Content */}
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{
                    fontWeight: 600,
                    fontSize: 13.5,
                    color: cfg.color,
                    lineHeight: 1.3,
                    marginBottom: toast.message ? 4 : 0,
                }}>
                    {toast.title}
                </div>
                {toast.message && (
                    <div style={{
                        fontSize: 12.5,
                        color: 'var(--color-text-muted)',
                        lineHeight: 1.5,
                        wordBreak: 'break-word',
                    }}>
                        {toast.message}
                    </div>
                )}
            </div>

            {/* Dismiss */}
            <button
                onClick={() => dismiss(toast.id)}
                aria-label="Fechar notificação"
                style={{
                    background: 'none',
                    border: 'none',
                    color: 'var(--color-text-muted)',
                    cursor: 'pointer',
                    fontSize: 16,
                    lineHeight: 1,
                    flexShrink: 0,
                    padding: '2px 4px',
                    borderRadius: 4,
                    transition: 'color 0.15s',
                }}
                onMouseEnter={e => (e.currentTarget.style.color = cfg.color)}
                onMouseLeave={e => (e.currentTarget.style.color = '')}
            >
                ✕
            </button>
        </div>
    );
}

// ── Toast Container (renderizado no canto da tela) ─────────────────────────────

export default function ToastContainer() {
    const { toasts } = useToast();

    if (toasts.length === 0) return null;

    return (
        <div
            aria-label="Notificações"
            style={{
                position: 'fixed',
                bottom: 24,
                right: 24,
                zIndex: 9999,
                display: 'flex',
                flexDirection: 'column',
                gap: 10,
                pointerEvents: 'none',
            }}
        >
            {toasts.map(t => (
                <div key={t.id} style={{ pointerEvents: 'auto' }}>
                    <ToastItem toast={t} />
                </div>
            ))}
        </div>
    );
}
