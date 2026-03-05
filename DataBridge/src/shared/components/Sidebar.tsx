import { NavLink } from 'react-router-dom';
import { useTheme } from '../hooks/useTheme';
import { useAuth } from '../contexts/AuthContext';
import CompanySelector from './CompanySelector';

const NAV = [
    { to: '/', icon: '🏠', label: 'Dashboard' },
    { to: '/notas', icon: '📄', label: 'Notas Fiscais' },
    { to: '/fornecedores', icon: '🏆', label: 'Fornecedores' },
    { to: '/alertas', icon: '🔔', label: 'Alertas' },
    { to: '/saving', icon: '💰', label: 'Saving & Custos' },
    { to: '/benchmark', icon: '📊', label: 'Benchmark Custo' },
    { to: '/simulador', icon: '🎛️', label: 'Simulador de Cenários' },
    { to: '/contatos', icon: '📋', label: 'Contatos' },
    { to: '/import', icon: '📥', label: 'Importar NF-e' },
    { to: '/api-keys', icon: '🔑', label: 'Chaves de API' },
    { to: '/webhooks', icon: '🔗', label: 'Webhooks' },
    { to: '/conectores', icon: '🔌', label: 'Conectores ERP' },
    { to: '/docs/integracao', icon: '📖', label: 'Docs Integração' },
];

export default function Sidebar() {
    const { theme, toggle } = useTheme();
    const { user, logout } = useAuth();

    return (
        <aside className="sidebar">
            <div className="sidebar-brand">
                <span className="sidebar-brand-icon">🛒</span>
                <div>
                    <div className="sidebar-brand-name">Inteligência</div>
                    <div className="sidebar-brand-sub">de Compras</div>
                </div>
            </div>

            <CompanySelector />

            <nav className="sidebar-nav">
                {NAV.map(({ to, icon, label }) => (
                    <NavLink
                        key={to}
                        to={to}
                        end={to === '/'}
                        className={({ isActive }) => `sidebar-link${isActive ? ' active' : ''}`}
                    >
                        <span className="sidebar-link-icon">{icon}</span>
                        <span>{label}</span>
                    </NavLink>
                ))}
            </nav>

            <div className="sidebar-footer">
                {user && <div className="sidebar-user">{user.name}</div>}
                <button className="theme-toggle" onClick={toggle}>
                    <span className="theme-toggle-icon">{theme === 'dark' ? '☀️' : '🌙'}</span>
                    <span>{theme === 'dark' ? 'Modo Claro' : 'Modo Escuro'}</span>
                </button>
                <button className="theme-toggle" onClick={logout}>
                    <span className="theme-toggle-icon">🚪</span>
                    <span>Sair</span>
                </button>
            </div>
        </aside>
    );
}
