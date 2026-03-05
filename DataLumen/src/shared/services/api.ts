import axios from "axios";

const STORAGE_KEY = "current_company_id";

const api = axios.create({
  baseURL: "http://localhost:8000/api/v1",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

// Inject auth token + company header on every request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  const companyId = localStorage.getItem(STORAGE_KEY);
  if (companyId) {
    config.headers["X-Company-Id"] = companyId;
  }

  return config;
});

api.interceptors.response.use(
  (res) => res,
  (err) => {
    // If unauthorized, clear auth and redirect to login
    if (err.response?.status === 401) {
      localStorage.removeItem("auth_token");
      localStorage.removeItem(STORAGE_KEY);
      if (window.location.pathname !== "/login") {
        window.location.href = "/login";
      }
    }

    const message =
      err.response?.data?.message ||
      err.response?.data?.error ||
      err.message ||
      "Erro desconhecido";
    return Promise.reject(new Error(message));
  },
);

export default api;
