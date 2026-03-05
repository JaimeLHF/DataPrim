interface Props {
    label: string;
    value: string | number;
    sub?: string;
    variant?: 'default' | 'positive' | 'negative' | 'accent';
    icon?: string;
}

export default function KpiCard({ label, value, sub, variant = 'default', icon }: Props) {
    const valueClass = `kpi-value${variant !== 'default' ? ` ${variant}` : ''}`;

    return (
        <div className="kpi-card">
            <div className="kpi-label">
                {icon && <span style={{ marginRight: 6 }}>{icon}</span>}
                {label}
            </div>
            <div className={valueClass}>{value}</div>
            {sub && <div className="kpi-sub">{sub}</div>}
        </div>
    );
}
