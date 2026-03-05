import { BrowserRouter, Routes, Route } from "react-router-dom";
import { AuthProvider } from "./shared/contexts/AuthContext";
import { CompanyProvider } from "./shared/contexts/CompanyContext";
import { ToastProvider } from "./shared/contexts/ToastContext";
import ProtectedRoute from "./shared/components/ProtectedRoute";
import Sidebar from "./shared/components/Sidebar";
import ToastContainer from "./shared/components/ToastContainer";
import ErpConnectors from "./pages/ErpConnectors";
import Webhooks from "./pages/Webhooks";
import Import from "./pages/Import";
import DocsIntegracao from "./pages/DocsIntegracao";
import dateBridge from './assets/DataBridge-logo.png';

// Processar token recebido via URL do DataCore (cross-origin auth)
// Executa sincronicamente antes do React renderizar, antes do AuthProvider.
(function processIncomingToken() {
  const params = new URLSearchParams(window.location.search);
  const token = params.get("token");
  const timestamp = params.get("t");
  if (!token || !timestamp) return;
  const age = Date.now() - Number(timestamp);
  if (age <= 30000) {
    localStorage.setItem("auth_token", token);
  }
  window.history.replaceState({}, "", "/");
})();

function DataBridgePlaceholder() {
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
      <img src={dateBridge} alt="DataForge Logo" style={{ width: "20%" }} />
      <h1 style={{ fontSize: "2rem", color: "#3b82f6" }}>DataForge</h1>
      <p style={{ color: "#64748b" }}>Em desenvolvimento</p>
    </div>
  );
}

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <ToastProvider>
          <ProtectedRoute>
            <CompanyProvider>
              <div className="layout">
                <Sidebar />
                <main className="main-content">
                  <Routes>
                    <Route path="/*" element={<DataBridgePlaceholder />} />
                  </Routes>
                </main>
                <ToastContainer />
              </div>
            </CompanyProvider>
          </ProtectedRoute>
        </ToastProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}
