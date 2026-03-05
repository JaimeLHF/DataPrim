import { createContext, useContext, useState, useEffect, useCallback, type ReactNode } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  login as apiLogin,
  logout as apiLogout,
  getMe,
  getToken,
  clearAuth,
  type AuthUser,
  type AuthCompany,
} from '../services/auth';

interface AuthContextValue {
  user: AuthUser | null;
  isAuthenticated: boolean;
  isAdmin: boolean;
  loading: boolean;
  login: (email: string, password: string) => Promise<{ user: AuthUser; companies: AuthCompany[] }>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue>({
  user: null,
  isAuthenticated: false,
  isAdmin: false,
  loading: true,
  login: async () => ({ user: {} as AuthUser, companies: [] }),
  logout: async () => {},
});

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    const token = getToken();
    if (!token) {
      setLoading(false);
      return;
    }

    getMe()
      .then((data) => setUser(data.user))
      .catch(() => {
        clearAuth();
      })
      .finally(() => setLoading(false));
  }, []);

  const login = useCallback(
    async (email: string, password: string) => {
      const data = await apiLogin(email, password);
      setUser(data.user);

      // Auto-select first company if none stored
      const stored = localStorage.getItem('current_company_id');
      if (!stored && data.companies.length > 0) {
        localStorage.setItem('current_company_id', String(data.companies[0].id));
      }

      return { user: data.user, companies: data.companies };
    },
    [],
  );

  const logout = useCallback(async () => {
    await apiLogout();
    setUser(null);
    navigate('/login');
  }, [navigate]);

  const isAdmin = user?.is_admin === true;

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated: user !== null,
        isAdmin,
        loading,
        login,
        logout,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
