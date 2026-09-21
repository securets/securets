import React, { useEffect, useState } from 'react';
import { View, StyleSheet, StatusBar } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

import SplashScreen    from './components/SplashScreen';
import OnboardingScreen from './components/OnboardingScreen';
import DashboardScreen from './components/DashboardScreen';

/**
 * App Navigation Flow:
 *   1. SplashScreen (5 seconds, always shown on launch)
 *   2. OnboardingScreen (shown ONCE for new users via AsyncStorage flag)
 *   3. DashboardScreen (main authenticated view)
 */
export default function App() {
  const [phase, setPhase] = useState('splash'); // 'splash' | 'onboarding' | 'dashboard'

  // After splash, decide where to go
  const handleSplashFinish = async () => {
    try {
      const done = await AsyncStorage.getItem('onboarding_complete');
      setPhase(done === 'true' ? 'dashboard' : 'onboarding');
    } catch (_) {
      setPhase('onboarding');
    }
  };

  const handleOnboardingFinish = () => {
    setPhase('dashboard');
  };

  const handleLogout = async () => {
    try {
      // Clear onboarding flag so new user flow can restart if needed
      await AsyncStorage.removeItem('onboarding_complete');
    } catch (_) { /* ignore */ }
    setPhase('onboarding');
  };

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#0F172A" />

      {/* Phase: Splash (always on top during 5s) */}
      {phase === 'splash' && (
        <SplashScreen onFinish={handleSplashFinish} />
      )}

      {/* Phase: Onboarding (first time only) */}
      {phase === 'onboarding' && (
        <OnboardingScreen onFinish={handleOnboardingFinish} />
      )}

      {/* Phase: Dashboard */}
      {phase === 'dashboard' && (
        <DashboardScreen
          user={null}
          onLogout={handleLogout}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#0A0F1E',
  },
});
