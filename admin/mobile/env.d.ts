// Expo inlines EXPO_PUBLIC_* variables at build time.
declare const process: {
  env: {
    EXPO_PUBLIC_ADMIN_API_URL?: string;
    EXPO_PUBLIC_ADMIN_API_URL_FALLBACK?: string;
  };
};
