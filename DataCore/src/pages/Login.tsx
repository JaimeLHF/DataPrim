import { useState, useEffect, type FormEvent } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useAuth } from '../shared/contexts/AuthContext';

const REDIRECT_URLS: Record<string, string> = {
  databridge: 'http://localhost:5175',
  dataforge: 'http://localhost:5177',
  datalumen: 'http://localhost:5173',
};

function buildRedirectUrl(base: string): string {
  const token = localStorage.getItem('auth_token') ?? '';
  return `${base}?token=${encodeURIComponent(token)}&t=${Date.now()}`;
}

export default function Login() {
  const { login, logout, isAuthenticated, isAdmin, loading } = useAuth();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (loading) return;
    if (isAuthenticated && isAdmin) {
      const redirect = searchParams.get('redirect');
      const url = redirect ? REDIRECT_URLS[redirect] : null;
      if (url) {
        window.location.href = buildRedirectUrl(url);
      } else {
        navigate('/dashboard', { replace: true });
      }
    } else if (isAuthenticated && !isAdmin) {
      logout();
    }
  }, [loading, isAuthenticated, isAdmin, navigate, logout, searchParams]);

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setError('');
    setSubmitting(true);

    try {
      const { user } = await login(email, password);
      if (!user.is_admin) {
        await logout();
        setError('Acesso restrito ao time interno');
        return;
      }
      const redirect = searchParams.get('redirect');
      const url = redirect ? REDIRECT_URLS[redirect] : null;
      if (url) {
        window.location.href = buildRedirectUrl(url);
      } else {
        navigate('/dashboard', { replace: true });
      }
    } catch (err: unknown) {
      setError(
        err instanceof Error && (err.message.includes('401') || err.message.includes('422') || err.message.toLowerCase().includes('inv\u00e1lid') || err.message.toLowerCase().includes('incorrect'))
          ? 'Email ou senha incorretos'
          : (err instanceof Error ? err.message : 'Erro ao fazer login.'),
      );
    } finally {
      setSubmitting(false);
    }
  }

  if (loading) {
    return <div className="loading-screen">Carregando...</div>;
  }

  return (
    <div
      style={{
        minHeight: '100vh',
        background: 'linear-gradient(135deg, #111827 0%, #1f2937 50%, #111827 100%)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontFamily: 'Inter, sans-serif',
        padding: '1rem',
      }}
    >
      <div
        style={{
          background: '#1f2937',
          border: '1px solid #374151',
          borderRadius: '16px',
          padding: '2.5rem',
          width: '100%',
          maxWidth: '400px',
          boxShadow: '0 25px 50px rgba(0,0,0,0.6)',
        }}
      >
        <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
          <div
            style={{
              width: '48px',
              height: '48px',
              background: '#374151',
              borderRadius: '12px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              margin: '0 auto 1rem',
              fontSize: '1.5rem',
            }}
          >
            🔐
          </div>
          <h1 style={{ color: '#f9fafb', fontSize: '1.1rem', fontWeight: 700, margin: 0 }}>
            Acesso Administrativo
          </h1>
          <p style={{ color: '#9ca3af', fontSize: '0.875rem', margin: '0.25rem 0 0' }}>
            Primidéias
          </p>
        </div>

        {error && (
          <div
            style={{
              background: 'rgba(239,68,68,0.1)',
              border: '1px solid rgba(239,68,68,0.3)',
              borderRadius: '8px',
              color: '#fca5a5',
              padding: '0.75rem 1rem',
              marginBottom: '1.25rem',
              fontSize: '0.875rem',
            }}
          >
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div style={{ marginBottom: '1rem' }}>
            <label style={{ display: 'block', color: '#d1d5db', fontSize: '0.875rem', marginBottom: '0.4rem' }}>
              E-mail
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              autoFocus
              style={{
                width: '100%',
                padding: '0.75rem',
                background: '#111827',
                border: '1px solid #374151',
                borderRadius: '8px',
                color: '#f9fafb',
                fontSize: '1rem',
                outline: 'none',
                boxSizing: 'border-box',
              }}
              placeholder="admin@primideias.com.br"
            />
          </div>

          <div style={{ marginBottom: '1.5rem' }}>
            <label style={{ display: 'block', color: '#d1d5db', fontSize: '0.875rem', marginBottom: '0.4rem' }}>
              Senha
            </label>
            <div style={{ position: 'relative' }}>
              <input
                type={showPassword ? 'text' : 'password'}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                style={{
                  width: '100%',
                  padding: '0.75rem 2.75rem 0.75rem 0.75rem',
                  background: '#111827',
                  border: '1px solid #374151',
                  borderRadius: '8px',
                  color: '#f9fafb',
                  fontSize: '1rem',
                  outline: 'none',
                  boxSizing: 'border-box',
                }}
                placeholder="••••••••"
              />
              <button
                type="button"
                onClick={() => setShowPassword((v) => !v)}
                style={{
                  position: 'absolute',
                  right: '0.75rem',
                  top: '50%',
                  transform: 'translateY(-50%)',
                  background: 'none',
                  border: 'none',
                  cursor: 'pointer',
                  color: '#6b7280',
                  fontSize: '1rem',
                  padding: 0,
                }}
                aria-label={showPassword ? 'Ocultar senha' : 'Mostrar senha'}
              >
                {showPassword ? '🙈' : '👁️'}
              </button>
            </div>
          </div>

          <button
            type="submit"
            disabled={submitting}
            style={{
              width: '100%',
              padding: '0.875rem',
              background: submitting ? '#374151' : '#4b5563',
              border: '1px solid #6b7280',
              borderRadius: '8px',
              color: '#f9fafb',
              fontSize: '1rem',
              fontWeight: 600,
              cursor: submitting ? 'not-allowed' : 'pointer',
              transition: 'background 0.2s',
            }}
          >
            {submitting ? 'Verificando...' : 'Entrar'}
          </button>
        </form>
      </div>
    </div>
  );
}
