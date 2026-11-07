import axios from 'axios';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

// API endpoints — set in mobile/.env:
//   EXPO_PUBLIC_API_URL           primary endpoint
//   EXPO_PUBLIC_API_URL_FALLBACK  optional backup endpoint
// Examples:
//   Local dev (web/iOS sim):    http://127.0.0.1:8000/api
//   Android emulator:           http://10.0.2.2:8000/api
//   Physical device (Expo Go):  http://192.168.x.x:8000/api
//   Production:                 https://yourdomain.com/api
const resolveEndpoints = () => {
  if (Platform.OS === 'web' && typeof window !== 'undefined') {
    const host = window.location.hostname;
    if (host === 'localhost' || host === '127.0.0.1') {
      return [`http://${host}:8000/api`];
    }
  }

  const primary = process.env.EXPO_PUBLIC_API_URL?.trim();
  const fallback = process.env.EXPO_PUBLIC_API_URL_FALLBACK?.trim();
  const localhost = Platform.OS === 'android' ? 'http://10.0.2.2:8000/api' : 'http://127.0.0.1:8000/api';

  return [primary || localhost, fallback].filter(Boolean);
};

const ENDPOINTS = [...new Set(resolveEndpoints())];

// Index of the endpoint that last answered, so the failover probe runs once per
// session instead of on every request.
let activeEndpoint = 0;

const endpointUrl = (index) => ENDPOINTS[index] ?? ENDPOINTS[0];

/** The endpoint currently in use — the last one known to be reachable. */
export const getApiBaseUrl = () => endpointUrl(activeEndpoint);

/** Every configured endpoint, in the order the client tries them. */
export const getConfiguredEndpoints = () => [...ENDPOINTS];

/** True once the client has failed over to a backup endpoint. */
export const isUsingFallbackEndpoint = () => activeEndpoint > 0;

export const AUTH_SESSION_MINUTES = Number.parseInt(
  process.env.EXPO_PUBLIC_AUTH_SESSION_MINUTES ?? '120',
  10,
);

export const AUTH_SESSION_LABEL = AUTH_SESSION_MINUTES % 60 === 0
  ? `${AUTH_SESSION_MINUTES / 60} hour${AUTH_SESSION_MINUTES === 60 ? '' : 's'}`
  : `${AUTH_SESSION_MINUTES} minutes`;

const TOKEN_KEY = 'user_token';
const TOKEN_META_KEY = 'user_token_meta';


const API = axios.create({
  baseURL: getApiBaseUrl(),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000,
});

const getStorageItem = async (key) => {
  try {
    if (Platform.OS === 'web') {
      return localStorage.getItem(key);
    }
    return await SecureStore.getItemAsync(key);
  } catch (error) {
    console.error(`Error reading ${key}:`, error);
    return null;
  }
};

const setStorageItem = async (key, value) => {
  try {
    if (Platform.OS === 'web') {
      localStorage.setItem(key, value);
    } else {
      await SecureStore.setItemAsync(key, value);
    }
  } catch (error) {
    console.error(`Error saving ${key}:`, error);
  }
};

const removeStorageItem = async (key) => {
  try {
    if (Platform.OS === 'web') {
      localStorage.removeItem(key);
    } else {
      await SecureStore.deleteItemAsync(key);
    }
  } catch (error) {
    console.error(`Error removing ${key}:`, error);
  }
};

export const clearStoredSession = async () => {
  await removeStorageItem(TOKEN_KEY);
  await removeStorageItem(TOKEN_META_KEY);
};

const getStoredTokenMeta = async () => {
  const raw = await getStorageItem(TOKEN_META_KEY);

  if (!raw) {
    return null;
  }

  try {
    return JSON.parse(raw);
  } catch (error) {
    console.error('Error parsing auth token metadata:', error);
    await clearStoredSession();
    return null;
  }
};

export const getStoredToken = async () => {
  const token = await getStorageItem(TOKEN_KEY);

  if (!token) {
    return null;
  }

  const meta = await getStoredTokenMeta();
  const expiresAt = meta?.expiresAt ? Date.parse(meta.expiresAt) : NaN;

  // Allow a 30-second grace window to prevent clock-drift false-expiries.
  const GRACE_MS = 30_000;
  if (!meta || Number.isNaN(expiresAt) || expiresAt <= Date.now() - GRACE_MS) {
    await clearStoredSession();
    return null;
  }

  return token;
};

export const setStoredToken = async (token, { expiresAt } = {}) => {
  if (!token) {
    await clearStoredSession();
    return;
  }

  let finalExpiresAt;
  if (expiresAt) {
    finalExpiresAt = new Date(expiresAt).toISOString();
  } else if (AUTH_SESSION_MINUTES > 0) {
    finalExpiresAt = new Date(Date.now() + (AUTH_SESSION_MINUTES * 60 * 1000)).toISOString();
  } else {
    // Server returned 0 = infinite token; default to 1 year on the client side
    // so we still clear stale storage on yearly basis.
    finalExpiresAt = new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString();
  }

  await setStorageItem(TOKEN_KEY, token);
  await setStorageItem(TOKEN_META_KEY, JSON.stringify({ expiresAt: finalExpiresAt }));
};

// Interceptor: Automatically attach Bearer token to all outgoing requests
API.interceptors.request.use(async (config) => {
  config.baseURL = endpointUrl(config._endpointIndex ?? activeEndpoint);

  const token = await getStoredToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => {
  return Promise.reject(error);
});

API.interceptors.response.use(
  (response) => {
    // Remember the endpoint that answered so later calls go straight to it.
    const index = response.config?._endpointIndex;
    if (typeof index === 'number') {
      activeEndpoint = index;
    }
    return response;
  },
  async (error) => {
    const config = error?.config;
    const triedIndex = config?._endpointIndex ?? activeEndpoint;

    if (error?.response) {
      // A 4xx/5xx is still a real answer from a reachable server — remember it so
      // later calls skip the probe. Rejected responses land here, not in the
      // success handler above.
      activeEndpoint = triedIndex;
    } else if (triedIndex + 1 < ENDPOINTS.length) {
      // No `response` means the request never reached a server: a timeout, a DNS
      // failure or a dead VPN tunnel. Only that case is worth retrying on another
      // endpoint — a 4xx/5xx is a real answer from an API we can already reach.
      const nextIndex = triedIndex + 1;
      console.warn(`[api] ${endpointUrl(triedIndex)} unreachable - retrying via ${endpointUrl(nextIndex)}`);
      return API.request({ ...config, _endpointIndex: nextIndex });
    }

    if (error?.response?.status === 401) {
      await clearStoredSession();
    }

    return Promise.reject(error);
  },
);

export default API;
