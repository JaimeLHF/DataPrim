import { createContext, useContext, useState, useEffect, useCallback, type ReactNode } from 'react';
import api from '../services/api';

export interface Company {
  id: number;
  name: string;
  slug: string | null;
  plan: string;
  role: string;
}

interface CompanyContextValue {
  companies: Company[];
  current: Company | null;
  loading: boolean;
  switchCompany: (id: number) => void;
}

const STORAGE_KEY = 'current_company_id';

const CompanyContext = createContext<CompanyContextValue>({
  companies: [],
  current: null,
  loading: true,
  switchCompany: () => {},
});

export function CompanyProvider({ children }: { children: ReactNode }) {
  const [companies, setCompanies] = useState<Company[]>([]);
  const [current, setCurrent] = useState<Company | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api
      .get<{ data: Company[] }>('/companies')
      .then(({ data }) => {
        const list = data.data;
        setCompanies(list);

        // Restore previous selection or default to first
        const storedId = localStorage.getItem(STORAGE_KEY);
        const restored = storedId ? list.find((c) => c.id === Number(storedId)) : null;
        const selected = restored ?? list[0] ?? null;
        setCurrent(selected);

        if (selected) {
          localStorage.setItem(STORAGE_KEY, String(selected.id));
        }
      })
      .catch(() => {
        // Fallback: use company_id=1 if API not available yet
        setCurrent({ id: 1, name: 'Empresa', slug: null, plan: 'trial', role: 'viewer' });
      })
      .finally(() => setLoading(false));
  }, []);

  const switchCompany = useCallback(
    (id: number) => {
      const company = companies.find((c) => c.id === id);
      if (company) {
        setCurrent(company);
        localStorage.setItem(STORAGE_KEY, String(id));
      }
    },
    [companies],
  );

  return (
    <CompanyContext.Provider value={{ companies, current, loading, switchCompany }}>
      {children}
    </CompanyContext.Provider>
  );
}

export function useCompany() {
  return useContext(CompanyContext);
}
