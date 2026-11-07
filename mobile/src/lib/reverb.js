/**
 * reverb.js
 * ---------
 * Creates a Pusher client pre-configured for Laravel Reverb.
 * Used by the mobile app to subscribe to private broadcast channels.
 *
 * Env vars (set in mobile/.env):
 *   EXPO_PUBLIC_REVERB_APP_KEY   – must match REVERB_APP_KEY in Laravel .env
 *   EXPO_PUBLIC_REVERB_HOST      – host only, e.g. 192.168.1.x or 127.0.0.1
 *   EXPO_PUBLIC_REVERB_PORT      – default 8080
 *   EXPO_PUBLIC_REVERB_SCHEME    – "http" (local) or "https" (production)
 */

import * as PusherModule from 'pusher-js';
import { getApiBaseUrl, isUsingFallbackEndpoint } from '../api/client';
import { getStoredToken } from '../api/client';

// pusher-js resolves to a different bundle per platform (Metro picks the
// package's "react-native" field). The React Native bundle exposes the
// constructor as a named export (`module.exports.Pusher`), while the
// web/node bundles expose it as the default export. Resolve both shapes so
// `new Pusher(...)` always receives a constructor.
const Pusher = PusherModule.Pusher ?? PusherModule.default;

const APP_KEY   = process.env.EXPO_PUBLIC_REVERB_APP_KEY  ?? 'securets-key';
const HOST      = process.env.EXPO_PUBLIC_REVERB_HOST     ?? '127.0.0.1';
const PORT      = Number(process.env.EXPO_PUBLIC_REVERB_PORT    ?? '8080');
const SCHEME    = process.env.EXPO_PUBLIC_REVERB_SCHEME   ?? 'http';
const USE_TLS   = SCHEME === 'https';

// Reverb runs on the same machine as the API, so once the API client has failed
// over to its backup endpoint the WebSocket must follow it - otherwise the app
// would keep talking to a host it has already proved unreachable.
const resolveHost = () => {
  if (!isUsingFallbackEndpoint()) {
    return HOST;
  }
  try {
    return new URL(getApiBaseUrl()).hostname;
  } catch {
    return HOST;
  }
};

let _pusherInstance = null;

/**
 * createReverbClient(token)
 * Returns a Pusher instance pointing at the Reverb WebSocket server.
 * The `token` is the Sanctum bearer token used to authenticate private channels.
 *
 * @param {string} sanctumToken  – the user's Sanctum personal access token
 * @returns {Pusher}
 */
export function createReverbClient(sanctumToken) {
  if (_pusherInstance) {
    _pusherInstance.disconnect();
  }

  _pusherInstance = new Pusher(APP_KEY, {
    wsHost:           resolveHost(),
    wsPort:           USE_TLS ? 443 : PORT,
    wssPort:          443,
    forceTLS:         USE_TLS,
    enabledTransports: ['ws', 'wss'],
    disableStats:     true,
    cluster:          'mt1',           // Reverb ignores this, but field is required
    authEndpoint:     `${getApiBaseUrl()}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${sanctumToken}`,
        Accept:        'application/json',
      },
    },
  });

  return _pusherInstance;
}

/**
 * disconnectReverb()
 * Cleans up the Pusher connection (call on logout).
 */
export function disconnectReverb() {
  if (_pusherInstance) {
    _pusherInstance.disconnect();
    _pusherInstance = null;
  }
}
