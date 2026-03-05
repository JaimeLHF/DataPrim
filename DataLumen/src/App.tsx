import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './shared/contexts/AuthContext';
import { CompanyProvider } from './shared/contexts/CompanyContext';
import { ToastProvider } from './shared/contexts/ToastContext';
import ProtectedRoute from './shared/components/ProtectedRoute';
import Sidebar from './shared/components/Sidebar';
import ToastContainer from './shared/components/ToastContainer';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import NotasFiscais from './pages/NotasFiscais';
import Fornecedores from './pages/Fornecedores';
import Saving from './pages/Saving';
import CostStructureBenchmark from './pages/CostStructureBenchmark';
import WhatIfSimulator from './pages/WhatIfSimulator';
import Contatos from './pages/Contatos';
import Alertas from './pages/Alertas';

function SemAcesso() {
  return (
    <div style={{ padding: '3rem', textAlign: 'center', color: 'var(--color-text-secondary, #64748b)' }}>
      <h2>Sem acesso</h2>
      <p>Sua conta não está vinculada a nenhuma empresa. Entre em contato com o suporte.</p>
    </div>
  );
}

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <ToastProvider>
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route
              path="/*"
              element={
                <ProtectedRoute>
                  <CompanyProvider>
                    <div className="layout">
                      <Sidebar />
                      <main className="main-content">
                        <Routes>
                          <Route path="/" element={<Navigate to="/dashboard" replace />} />
                          <Route path="/dashboard" element={<Dashboard />} />
                          <Route path="/notas" element={<NotasFiscais />} />
                          <Route path="/fornecedores" element={<Fornecedores />} />
                          <Route path="/saving" element={<Saving />} />
                          <Route path="/benchmark" element={<CostStructureBenchmark />} />
                          <Route path="/simulador" element={<WhatIfSimulator />} />
                          <Route path="/contatos" element={<Contatos />} />
                          <Route path="/alertas" element={<Alertas />} />
                          <Route path="/sem-acesso" element={<SemAcesso />} />
                        </Routes>
                      </main>
                      <ToastContainer />
                    </div>
                  </CompanyProvider>
                </ProtectedRoute>
              }
            />
          </Routes>
        </ToastProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}

