import axios from 'axios';
import Constants from 'expo-constants';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

const normalizeBaseUrl = (value: string) => value.trim().replace(/\/$/, '');

const getExpoHostUrl = () => {
  if (Platform.OS === 'web' && typeof window !== 'undefined') {
    return window.location.hostname ? `http://${window.location.hostname}:8001` : null;
  }

  const hostUri = Constants.expoConfig?.hostUri
    ?? (Constants as typeof Constants & { manifest2?: { extra?: { expoClient?: { hostUri?: string } } } }).manifest2?.extra?.expoClient?.hostUri;
  const host = hostUri?.split(':')[0];
  return host ? `http://${host}:8001` : null;
};

const resolveBaseUrls = () => {
  const configured = process.env.EXPO_PUBLIC_ADMIN_API_URL?.trim();
  const fallback = process.env.EXPO_PUBLIC_ADMIN_API_URL_FALLBACK?.trim();
  const platformDefault = Platform.OS === 'android' ? 'http://10.0.2.2:8001' : 'http://127.0.0.1:8001';

  return [...new Set([
    configured,
    fallback,
    getExpoHostUrl(),
    platformDefault,
  ].filter(Boolean).map((value) => normalizeBaseUrl(value as string)))];
};

const baseUrls = resolveBaseUrls();
let activeBaseUrlIndex = 0;

// Accept both forms so the same build works with the browser URL
// `http://localhost:8001/admin` and with a host-only mobile URL.
const adminPathFor = (baseUrl: string) => /\/admin$/i.test(baseUrl) ? '' : '/admin';

const API = axios.create({
  baseURL: baseUrls[0],
  // The admin overview reads from the remote PostgreSQL database. Keep enough
  // time for a cold connection/query instead of treating a slow real response
  // as an empty dashboard.
  timeout: 30000,
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
});

export type AdminDevice = {
  id: number;
  name: string;
  owner: string;
  location: string;
  status: string;
  state: string;
};

export type AdminOverview = {
  adminName: string;
  metrics: { users: number; sessions: number; devices: number; health: string };
  devices: AdminDevice[];
  audit: Array<{ time: string; actor: string; action: string; target: string; severity: string }>;
};

export const getAdminApiBaseUrl = () => `${baseUrls[activeBaseUrlIndex]}${adminPathFor(baseUrls[activeBaseUrlIndex])}`;

const request = (path: string, config = {}) => API.request({ ...config, url: path });

// Admin bearer token. Persisted only in expo-secure-store (native); on web,
// where SecureStore is unavailable, it is kept in memory for the session.
const TOKEN_KEY = 'securets_admin_token';
let authToken: string | null = null;

export const loadStoredToken = async () => {
  if (Platform.OS !== 'web') authToken = await SecureStore.getItemAsync(TOKEN_KEY);
  return authToken;
};

const setToken = async (token: string | null) => {
  authToken = token;
  if (Platform.OS === 'web') return;
  if (token) await SecureStore.setItemAsync(TOKEN_KEY, token);
  else await SecureStore.deleteItemAsync(TOKEN_KEY);
};

export const login = async (email: string, password: string) => {
  const response = await request(`${adminPath()}/api/login`, {
    method: 'POST',
    data: { email, password, device_name: `${Platform.OS} admin app` },
  });
  await setToken(response.data.token);
};

export const logout = async () => {
  try {
    if (authToken) await request(`${adminPath()}/api/logout`, { method: 'POST' });
  } finally {
    await setToken(null);
  }
};

export const isUnauthorized = (error: unknown) =>
  axios.isAxiosError(error) && [401, 403].includes(error.response?.status ?? 0);

API.interceptors.request.use((config) => {
  if (authToken) config.headers.Authorization = `Bearer ${authToken}`;
  return config;
});

API.interceptors.request.use((config) => {
  const requestConfig = config as typeof config & { _adminEndpointIndex?: number };
  const index = requestConfig._adminEndpointIndex ?? activeBaseUrlIndex;
  config.baseURL = baseUrls[index] ?? baseUrls[0];
  requestConfig._adminEndpointIndex = index;
  return config;
});

API.interceptors.response.use(
  (response) => {
    const index = (response.config as typeof response.config & { _adminEndpointIndex?: number })._adminEndpointIndex;
    if (typeof index === 'number') activeBaseUrlIndex = index;
    return response;
  },
  (error) => {
    const config = error?.config;
    const triedIndex = (config as (typeof config & { _adminEndpointIndex?: number }) | undefined)?._adminEndpointIndex ?? activeBaseUrlIndex;
    const attempt = (config as (typeof config & { _adminAttempt?: number }) | undefined)?._adminAttempt ?? 0;
    const maxAttempts = baseUrls.length * 2;
    if (!error?.response && attempt + 1 < maxAttempts) {
      const nextIndex = attempt % 2 === 0
        ? triedIndex
        : triedIndex + 1;
      console.warn(`[admin-api] ${baseUrls[triedIndex]} unavailable; retrying via ${baseUrls[nextIndex]}`);
      return API.request({ ...config, _adminEndpointIndex: nextIndex, _adminAttempt: attempt + 1 });
    }
    return Promise.reject(error);
  },
);

const adminPath = () => adminPathFor(baseUrls[activeBaseUrlIndex]);

export const getOverview = () => request(`${adminPath()}/api/overview`);

export default API;
