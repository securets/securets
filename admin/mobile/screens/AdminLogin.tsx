import React, { useMemo, useState } from 'react';
import { ActivityIndicator, Pressable, SafeAreaView, StyleSheet, Text, TextInput, View } from 'react-native';
import axios from 'axios';
import { login } from '../api/client';
import { ThemeProvider, ThemeTokens, useTheme } from '../theme/ThemeContext';

function LoginContent({ onSignedIn }: { onSignedIn: () => void }) {
  const theme = useTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const submit = async () => {
    setSubmitting(true);
    setError('');
    try {
      await login(email.trim(), password);
      setPassword('');
      onSignedIn();
    } catch (e) {
      const status = axios.isAxiosError(e) ? e.response?.status : undefined;
      setError(status === 422 ? 'These credentials do not match an administrator account.'
        : status === 429 ? 'Too many attempts. Try again in a minute.'
          : 'Admin server unavailable.');
    } finally {
      setSubmitting(false);
    }
  };

  return <SafeAreaView style={styles.safeArea}><View style={styles.card}>
    <Text style={styles.eyebrow}>SECURETHREATSHIELD / ADMIN</Text>
    <Text style={styles.title}>Sign in</Text>
    <TextInput style={styles.input} value={email} onChangeText={setEmail} placeholder="Email" placeholderTextColor={theme.colors.textMuted} autoCapitalize="none" autoComplete="email" keyboardType="email-address" />
    <TextInput style={styles.input} value={password} onChangeText={setPassword} placeholder="Password" placeholderTextColor={theme.colors.textMuted} secureTextEntry autoComplete="password" onSubmitEditing={submit} />
    {!!error && <Text style={styles.error}>{error}</Text>}
    <Pressable style={styles.button} onPress={submit} disabled={submitting || !email || !password} accessibilityRole="button">
      {submitting ? <ActivityIndicator color={theme.colors.text} /> : <Text style={styles.buttonText}>Sign in</Text>}
    </Pressable>
  </View></SafeAreaView>;
}

export default function AdminLogin(props: { onSignedIn: () => void }) {
  return <ThemeProvider><LoginContent {...props} /></ThemeProvider>;
}

const createStyles = (theme: ThemeTokens) => StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: theme.colors.background, justifyContent: 'center', padding: theme.spacing.lg },
  card: { backgroundColor: theme.colors.surface, borderColor: theme.colors.border, borderWidth: 1, borderRadius: theme.radius.card, padding: theme.spacing.lg, gap: theme.spacing.md },
  eyebrow: { color: theme.colors.textMuted, fontSize: 11, fontWeight: '600', letterSpacing: 1 },
  title: { color: theme.colors.text, fontSize: 22, fontWeight: '600', marginBottom: theme.spacing.xs },
  input: { minHeight: 44, borderColor: theme.colors.border, borderWidth: 1, borderRadius: theme.radius.control, backgroundColor: theme.colors.background, color: theme.colors.text, paddingHorizontal: theme.spacing.md, fontSize: 14 },
  error: { color: theme.colors.text, fontSize: 12 },
  button: { minHeight: 44, borderRadius: theme.radius.control, backgroundColor: theme.colors.primary, alignItems: 'center', justifyContent: 'center' },
  buttonText: { color: theme.colors.text, fontSize: 14, fontWeight: '600' },
});
