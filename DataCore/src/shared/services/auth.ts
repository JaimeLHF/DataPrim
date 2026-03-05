import api from './api';

const TOKEN_KEY = 'auth_token';

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
}

export interface AuthCompany {
  id: number;
  name: string;
  slug: string | null;
  plan: string;
  role: string;
}

interface LoginResponse {
  token: string;
  user: AuthUser;
  companies: AuthCompany[];
}

interface MeResponse {
  user: AuthUser;
  companies: AuthCompany[];
}

export function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY);
}

export function setToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token);
}

export function clearAuth(): void {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem('current_company_id');
}

export async function login(email: string, password: string): Promise<LoginResponse> {
  const { data } = await api.post<LoginResponse>('/auth/login', { email, password });
  setToken(data.token);
  return data;
}

export async function logout(): Promise<void> {
  try {
    await api.post('/auth/logout');
  } finally {
    clearAuth();
  }
}

export async function getMe(): Promise<MeResponse> {
  const { data } = await api.get<MeResponse>('/auth/me');
  return data;
}
