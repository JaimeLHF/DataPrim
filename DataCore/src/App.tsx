import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './shared/contexts/AuthContext';
import { CompanyProvider } from './shared/contexts/CompanyContext';
import { ToastProvider } from './shared/contexts/ToastContext';
import ProtectedRoute from './shared/components/ProtectedRoute';
import Sidebar from './shared/components/Sidebar';
import ToastContainer from './shared/components/ToastContainer';
import Login from './pages/Login';
import ApiKeys from './pages/ApiKeys';
import dataCore from './assets/DataCore-logo.png';

function DataCorePlaceholder() {
  return (
    <div
      style={{
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        flexDirection: "column",
        gap: "1rem",
        fontFamily: "Inter, sans-serif",
      }}
    >
      <img src={dataCore} alt="DataCore Logo" style={{ width: "20%" }} />
      <h1 style={{ fontSize: "2rem", color: "#3b82f6" }}>DataCore</h1>
      <p style={{ color: "#64748b" }}>Em desenvolvimento</p>
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
                          <Route path="/*" element={<DataCorePlaceholder />} />
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

