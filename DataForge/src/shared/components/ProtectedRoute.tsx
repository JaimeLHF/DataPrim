import { useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import type { ReactNode } from 'react';

export default function ProtectedRoute({ children }: { children: ReactNode }) {
  const { isAuthenticated, isAdmin, loading } = useAuth();

  useEffect(() => {
    if (!loading && !isAuthenticated) {
      window.location.href = 'http://localhost:5176/login?redirect=dataforge';
    }
  }, [loading, isAuthenticated]);

  if (loading) {
    return <div className="loading-screen">Carregando...</div>;
  }

  if (!isAuthenticated) {
    return <div className="loading-screen">Redirecionando...</div>;
  }

  if (!isAdmin) {
    return (
      <div
        style={{
          minHeight: '100vh',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontFamily: 'Inter, sans-serif',
          background: '#0f172a',
          color: '#94a3b8',
          flexDirection: 'column',
          gap: '0.5rem',
        }}
      >
        <div style={{ fontSize: '2rem' }}>🔒</div>
        <p style={{ margin: 0, fontSize: '1rem' }}>
          Acesso restrito. Faça login no painel administrativo.
        </p>
        <a
          href="http://localhost:5176/login?redirect=dataforge"
          style={{ color: '#3b82f6', fontSize: '0.875rem', marginTop: '0.5rem' }}
        >
          Ir para o login admin
        </a>
      </div>
    );
  }

  return <>{children}</>;
}

