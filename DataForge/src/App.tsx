import { BrowserRouter, Routes, Route } from "react-router-dom";
import { AuthProvider } from "./shared/contexts/AuthContext";
import dataforge from "./assets/DataForge-logo.png";
import Sidebar from "./shared/components/Sidebar";

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
import { ToastProvider } from "./shared/contexts/ToastContext";
import ProtectedRoute from "./shared/components/ProtectedRoute";
import { CompanyProvider } from "./shared/contexts/CompanyContext";
import ToastContainer from "./shared/components/ToastContainer";

function DataForgePlaceholder() {
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
      <img src={dataforge} alt="DataForge Logo" style={{ width: "20%" }} />
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
            <Routes>
              <Route
                path="/*"
                element={
                  <ProtectedRoute>
                    <CompanyProvider>
                      <div className="layout">
                        <Sidebar />
                        <main className="main-content">
                          <Routes>
                            <Route
                              path="/*"
                              element={<DataForgePlaceholder />}
                            />
                          </Routes>
                        </main>
                        <ToastContainer />
                      </div>
                    </CompanyProvider>
                  </ProtectedRoute>
                }
              />{" "}
            </Routes>
          </ProtectedRoute>
        </ToastProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}
