import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
  Alert,
  Platform,
} from 'react-native';
import Svg, { Path, Circle, Rect } from 'react-native-svg';
import API, { setStoredToken } from '../src/api/client';

export default function LoginScreen({ onLoginSuccess }) {
  const [email, setEmail] = useState('admin@securets.com');
  const [password, setPassword] = useState('password123');
  const [loading, setLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  const handleLogin = async () => {
    if (!email || !password) {
      setErrorMessage('Please enter both email and password.');
      return;
    }

    setLoading(true);
    setErrorMessage('');

    try {
      const response = await API.post('/login', {
        email: email.trim(),
        password,
        device_name: `${Platform.OS}_mobile_app`,
      });

      if (response.data && response.data.token) {
        await setStoredToken(response.data.token);
        if (onLoginSuccess) {
          onLoginSuccess(response.data.user);
        }
      }
    } catch (error) {
      console.error('Login error:', error);
      const msg = error.response?.data?.message || 'Failed to connect to SecureTS backend.';
      setErrorMessage(msg);
      if (Platform.OS !== 'web') {
        Alert.alert('Authentication Failed', msg);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.card}>
        {/* Shield Logo Header */}
        <View style={styles.logoContainer}>
          <Svg width="64" height="64" viewBox="0 0 64 64" fill="none">
            <Path
              d="M32 6L10 16V30C10 44.4 19.4 57.6 32 62C44.6 57.6 54 44.4 54 30V16L32 6Z"
              fill="#3866E6"
            />
            <Path
              d="M32 14L16 22V30C16 41 22.8 51.5 32 55C41.2 51.5 48 41 48 30V22L32 14Z"
              fill="#254EDB"
            />
            <Circle cx="32" cy="32" r="8" fill="#FFFFFF" opacity="0.9" />
          </Svg>
          <Text style={styles.title}>SecureTS</Text>
          <Text style={styles.subtitle}>Mobile ThreatShield Gateway</Text>
        </View>

        {/* Input Form */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>EMAIL ADDRESS</Text>
          <TextInput
            style={styles.input}
            placeholder="admin@securets.com"
            placeholderTextColor="#94A3B8"
            value={email}
            onChangeText={setEmail}
            autoCapitalize="none"
            keyboardType="email-address"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>PASSWORD</Text>
          <TextInput
            style={styles.input}
            placeholder="••••••••"
            placeholderTextColor="#94A3B8"
            value={password}
            onChangeText={setPassword}
            secureTextEntry
          />
        </View>

        {errorMessage ? (
          <Text style={styles.errorText}>{errorMessage}</Text>
        ) : null}

        {/* Submit Button */}
        <TouchableOpacity
          style={[styles.button, loading && styles.buttonDisabled]}
          onPress={handleLogin}
          disabled={loading}
          activeOpacity={0.8}
        >
          {loading ? (
            <ActivityIndicator color="#FFFFFF" size="small" />
          ) : (
            <Text style={styles.buttonText}>Sign In with Sanctum</Text>
          )}
        </TouchableOpacity>

        <View style={styles.footerNote}>
          <Text style={styles.footerText}>
            Protected by Laravel Sanctum Bearer Token Auth
          </Text>
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#0F172A',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  card: {
    width: '100%',
    maxWidth: 400,
    backgroundColor: '#1E293B',
    borderRadius: 20,
    padding: 28,
    borderWidth: 1,
    borderColor: '#334155',
    shadowColor: '#3866E6',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.25,
    shadowRadius: 16,
    elevation: 10,
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: 28,
  },
  title: {
    color: '#F8FAFC',
    fontSize: 24,
    fontWeight: 'bold',
    marginTop: 12,
    letterSpacing: 0.5,
  },
  subtitle: {
    color: '#94A3B8',
    fontSize: 13,
    marginTop: 4,
  },
  formGroup: {
    marginBottom: 18,
  },
  label: {
    color: '#CBD5E1',
    fontSize: 11,
    fontWeight: '700',
    marginBottom: 8,
    letterSpacing: 1,
  },
  input: {
    backgroundColor: '#0F172A',
    borderWidth: 1,
    borderColor: '#334155',
    borderRadius: 10,
    paddingHorizontal: 14,
    paddingVertical: 12,
    color: '#F8FAFC',
    fontSize: 14,
  },
  errorText: {
    color: '#EF4444',
    fontSize: 12,
    marginBottom: 16,
    textAlign: 'center',
  },
  button: {
    backgroundColor: '#3866E6',
    borderRadius: 10,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 8,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  buttonText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: 'bold',
  },
  footerNote: {
    marginTop: 20,
    alignItems: 'center',
  },
  footerText: {
    color: '#64748B',
    fontSize: 11,
  },
});
