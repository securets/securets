import React, { useState } from 'react';
import { View, StyleSheet, StatusBar } from 'react-native';
import SplashScreen from './components/SplashScreen';
import DashboardScreen from './components/DashboardScreen';

export default function App() {
  const [showSplash, setShowSplash] = useState(true);

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#3866E6" />
      
      <!-- Main Dashboard Mobile Screen -->
      <DashboardScreen onReplaySplash={() => setShowSplash(true)} />

      <!-- 5-Second Splash Screen Overlay -->
      {showSplash && (
        <SplashScreen onFinish={() => setShowSplash(false)} />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#ffffff',
  },
});
