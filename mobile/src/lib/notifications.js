/**
 * notifications.js
 * ----------------
 * Registers the device for Expo Push Notifications and stores the token
 * on the Laravel backend (POST /api/devices) so the server can target it.
 *
 * Call `registerForPushNotificationsAsync()` after every successful login.
 */

import * as Notifications from 'expo-notifications';
import { Platform } from 'react-native';
import API from '../api/client';

// How the app handles notifications while foregrounded
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowBanner: true,
    shouldShowList:   true,
    shouldPlaySound:  true,
    shouldSetBadge:   false,
  }),
});

/**
 * Requests permission, obtains the Expo Push Token, and registers it
 * with the backend so the server can send push notifications to this device.
 *
 * Safe to call multiple times – the backend device table uses upsert logic.
 * On Android a notification channel is created automatically.
 *
 * @returns {Promise<string|null>} The Expo Push Token, or null on failure.
 */
export async function registerForPushNotificationsAsync() {
  // Push notifications don't work on emulators – gracefully skip.
  if (Platform.OS === 'web') {
    return null;
  }

  // Request (or verify existing) permission
  const { status: existingStatus } = await Notifications.getPermissionsAsync();
  let finalStatus = existingStatus;

  if (existingStatus !== 'granted') {
    const { status } = await Notifications.requestPermissionsAsync();
    finalStatus = status;
  }

  if (finalStatus !== 'granted') {
    console.warn('[Notifications] Permission not granted.');
    return null;
  }

  // Android: create a visible notification channel
  if (Platform.OS === 'android') {
    await Notifications.setNotificationChannelAsync('login-approvals', {
      name:        'Login Approvals',
      importance:  Notifications.AndroidImportance.MAX,
      vibrationPattern: [0, 250, 250, 250],
      lightColor:  '#3D5AF1',
    });
  }

  try {
    const tokenResponse = await Notifications.getExpoPushTokenAsync();
    const expoPushToken = tokenResponse.data;

    // Register on the backend (upsert by token)
    await API.post('/devices', {
      expo_push_token: expoPushToken,
      device_name:     `${Platform.OS} device`,
    });

    console.log('[Notifications] Push token registered:', expoPushToken);
    return expoPushToken;
  } catch (error) {
    console.warn('[Notifications] Failed to register push token:', error?.message ?? error);
    return null;
  }
}

/**
 * addLoginApprovalNotificationListener(onApproval)
 * -------------------------------------------------
 * Listens for TAPS on login-approval push notifications (background/killed state).
 * The `onApproval` callback receives the notification data payload:
 *   { event_id, integration_name, type: 'login_approval' }
 *
 * Returns a cleanup function — call it on unmount / logout.
 *
 * @param {(data: object) => void} onApproval
 * @returns {() => void} cleanup
 */
export function addLoginApprovalNotificationListener(onApproval) {
  const subscription = Notifications.addNotificationResponseReceivedListener(
    (response) => {
      const data = response.notification.request.content.data ?? {};
      if (data.type === 'login_approval') {
        onApproval(data);
      }
    },
  );

  return () => subscription.remove();
}

/**
 * addForegroundNotificationListener(onReceived)
 * ----------------------------------------------
 * Listens for push notifications that arrive while the app is FOREGROUNDED.
 * Without this, Expo only shows the banner but the app's state never updates.
 * The `onReceived` callback receives the notification data payload.
 *
 * Returns a cleanup function — call it on unmount / logout.
 *
 * @param {(data: object) => void} onReceived
 * @returns {() => void} cleanup
 */
export function addForegroundNotificationListener(onReceived) {
  const subscription = Notifications.addNotificationReceivedListener(
    (notification) => {
      const data = notification.request.content.data ?? {};
      if (data.type === 'login_approval') {
        onReceived(data);
      }
    },
  );

  return () => subscription.remove();
}
