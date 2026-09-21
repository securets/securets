import axios from 'axios';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

// NOTE: For local development on physical devices or emulators:
// - Physical device / Expo Go: Replace with your PC LAN IP (e.g., http://192.168.1.50:8000/api)
// - Android Emulator: http://10.0.2.2:8000/api
// - iOS Simulator / Web: http://localhost:8000/api or http://127.0.0.1:8000/api
export const API_BASE_URL = 'http://127.0.0.1:8000/api';

const API = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000,
});

// Helper for cross-platform secure storage (SecureStore for Mobile, localStorage for Web)
export const getStoredToken = async () => {
  try {
    if (Platform.OS === 'web') {
      return localStorage.getItem('user_token');
    }
    return await SecureStore.getItemAsync('user_token');
  } catch (error) {
    console.error('Error reading auth token:', error);
    return null;
  }
};

export const setStoredToken = async (token) => {
  try {
    if (Platform.OS === 'web') {
      if (token) {
        localStorage.setItem('user_token', token);
      } else {
        localStorage.removeItem('user_token');
      }
    } else {
      if (token) {
        await SecureStore.setItemAsync('user_token', token);
      } else {
        await SecureStore.deleteItemAsync('user_token');
      }
    }
  } catch (error) {
    console.error('Error saving auth token:', error);
  }
};

// Interceptor: Automatically attach Bearer token to all outgoing requests
API.interceptors.request.use(async (config) => {
  const token = await getStoredToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => {
  return Promise.reject(error);
});

export default API;
