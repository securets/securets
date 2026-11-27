import React, { useEffect, useState } from 'react';
import { StatusBar, StyleSheet, View } from 'react-native';
import { loadStoredToken } from './api/client';
import AdminDashboard from './screens/AdminDashboard';
import AdminLogin from './screens/AdminLogin';
import { THEME } from './theme/ThemeContext';

export default function App() {
  // null = still reading the stored token.
  const [signedIn, setSignedIn] = useState<boolean | null>(null);

  useEffect(() => {
    loadStoredToken().then((token) => setSignedIn(!!token)).catch(() => setSignedIn(false));
  }, []);

  return (
    <View style={styles.root}>
      <StatusBar barStyle="light-content" />
      {signedIn === true && <AdminDashboard onSignedOut={() => setSignedIn(false)} />}
      {signedIn === false && <AdminLogin onSignedIn={() => setSignedIn(true)} />}
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: THEME.colors.background },
});
