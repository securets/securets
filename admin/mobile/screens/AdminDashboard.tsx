import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { RefreshControl, SafeAreaView, ScrollView, StyleSheet, Text, Pressable, View } from 'react-native';
import { AdminOverview, getAdminApiBaseUrl, getOverview, isUnauthorized, logout } from '../api/client';
import { ThemeProvider, ThemeTokens, useTheme } from '../theme/ThemeContext';

function DashboardContent({ onSignedOut }: { onSignedOut: () => void }) {
  const theme = useTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const [overview, setOverview] = useState<AdminOverview | null>(null);
  const [refreshing, setRefreshing] = useState(false);
  const [message, setMessage] = useState('');
  const loadingRef = useRef<Promise<void> | null>(null);
  const load = useCallback(async () => {
    if (loadingRef.current) return loadingRef.current;

    const request = (async () => {
      try {
        const response = await getOverview();
        setOverview(response.data);
        setMessage('');
      } catch (error) {
        if (isUnauthorized(error)) {
          // Expired, revoked, or non-admin token: return to sign-in.
          await logout().catch(() => undefined);
          onSignedOut();
          return;
        }
        // Keep the last successful payload visible during a transient timeout.
        setMessage(`Admin server unavailable: ${getAdminApiBaseUrl()}`);
      } finally {
        loadingRef.current = null;
      }
    })();

    loadingRef.current = request;
    return request;
  }, [onSignedOut]);
  const signOut = async () => { await logout().catch(() => undefined); onSignedOut(); };
  useEffect(() => { load(); }, [load]);
  const onRefresh = async () => { setRefreshing(true); await load(); setRefreshing(false); };
  const devices = overview?.devices ?? [];
  const compromisedDevices = devices.filter((device) => !['active', 'recovered'].includes(device.state));
  return <SafeAreaView style={styles.safeArea}><ScrollView contentContainerStyle={styles.content} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={theme.colors.accent} />}>
    <View style={styles.header}><View><Text style={styles.eyebrow}>SECURETHREATSHIELD / ADMIN</Text><Text style={styles.title}>Admin Dashboard</Text></View><View style={styles.livePill}><View style={styles.liveDot} /><Text style={styles.liveText}>{(overview?.metrics.health ?? 'CONNECTING').toUpperCase()}</Text></View></View>
    <View style={styles.metricsRow}><View style={styles.metric}><Text style={styles.metricLabel}>USERS</Text><Text style={styles.metricValue}>{overview?.metrics.users ?? '—'}</Text></View><View style={styles.metric}><Text style={styles.metricLabel}>SESSIONS</Text><Text style={styles.metricValue}>{overview?.metrics.sessions ?? '—'}</Text></View><View style={styles.metric}><Text style={styles.metricLabel}>DEVICES</Text><Text style={styles.metricValue}>{overview?.metrics.devices ?? '—'}</Text></View></View>
    <View style={styles.alertSection}><View style={styles.sectionHeading}><View><Text style={styles.sectionTitle}>CURRENT COMPROMISE ALERTS</Text><Text style={styles.sectionHint}>Live device records requiring admin attention</Text></View><Text style={styles.count}>{compromisedDevices.length}</Text></View>{compromisedDevices.length ? compromisedDevices.map((device) => <View key={device.id} style={styles.alertCard}><View style={styles.alertHeader}><Text style={styles.alertDevice}>{device.name}</Text><Text style={styles.alertStatus}>{device.status}</Text></View><Text style={styles.alertOwner}>{device.owner}</Text><Text style={styles.alertMeta}>{device.location}</Text></View>) : <View style={styles.alertEmpty}><Text style={styles.emptyTitle}>No compromised devices reported</Text><Text style={styles.emptyHint}>This status is based on the latest system database response.</Text></View>}</View>{!!message && devices.length > 0 && <Text style={styles.message}>{message}</Text>}
    <Pressable style={styles.signOut} onPress={signOut} accessibilityRole="button"><Text style={styles.signOutText}>Sign out</Text></Pressable>
  </ScrollView></SafeAreaView>;
}

export default function AdminDashboard({ onSignedOut }: { onSignedOut: () => void }) { return <ThemeProvider><DashboardContent onSignedOut={onSignedOut} /></ThemeProvider>; }

const createStyles = (theme: ThemeTokens) => StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: theme.colors.background }, content: { padding: theme.spacing.lg, paddingBottom: 32 }, header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingBottom: theme.spacing.lg, borderBottomWidth: 1, borderBottomColor: theme.colors.border }, eyebrow: { color: theme.colors.textMuted, fontSize: 11, fontWeight: '600', letterSpacing: 1 }, title: { color: theme.colors.text, fontSize: 25, fontWeight: '700', marginTop: 5 }, livePill: { flexDirection: 'row', alignItems: 'center', gap: 6, borderColor: theme.colors.border, borderWidth: 1, borderRadius: 20, paddingHorizontal: 10, paddingVertical: 8 }, liveDot: { width: 7, height: 7, borderRadius: 4, backgroundColor: theme.colors.accent }, liveText: { color: theme.colors.text, fontSize: 10, fontWeight: '700', letterSpacing: .7 }, metricsRow: { flexDirection: 'row', gap: 8, marginVertical: 18 }, metric: { flex: 1, backgroundColor: theme.colors.surface, borderColor: theme.colors.border, borderWidth: 1, borderRadius: theme.radius.card, padding: 13 }, metricLabel: { color: theme.colors.textMuted, fontSize: 10, fontWeight: '600', letterSpacing: .8 }, metricValue: { color: theme.colors.text, fontSize: 22, fontWeight: '700', marginTop: 7 }, sectionHeading: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 10 }, sectionTitle: { color: theme.colors.text, fontSize: 12, fontWeight: '700', letterSpacing: .9 }, sectionHint: { color: theme.colors.textMuted, fontSize: 12, marginTop: 4 }, count: { color: theme.colors.accent, fontSize: 13, fontWeight: '700' }, deviceCard: { backgroundColor: theme.colors.surface, borderColor: theme.colors.border, borderWidth: 1, borderRadius: 13, padding: 14 }, deviceHeading: { flexDirection: 'row', alignItems: 'center' }, deviceIcon: { width: 38, height: 38, borderRadius: 10, backgroundColor: '#273454', alignItems: 'center', justifyContent: 'center' }, deviceIconText: { color: theme.colors.accent, fontSize: 19 }, deviceCopy: { flex: 1, marginLeft: 10 }, deviceName: { color: theme.colors.text, fontSize: 14, fontWeight: '700' }, deviceOwner: { color: theme.colors.textMuted, fontSize: 12, marginTop: 3 }, status: { fontSize: 10, fontWeight: '700', textTransform: 'uppercase' }, statusGood: { color: '#54d58b' }, statusWarn: { color: '#ffb454' }, deviceMeta: { color: theme.colors.textMuted, fontSize: 11, marginTop: 12, marginBottom: 12 }, deviceActions: { flexDirection: 'row', gap: 7 }, empty: { borderColor: theme.colors.border, borderWidth: 1, borderRadius: 12, padding: 22, alignItems: 'center' }, emptyTitle: { color: theme.colors.text, fontSize: 14, fontWeight: '600' }, emptyHint: { color: theme.colors.textMuted, fontSize: 12, marginTop: 6, textAlign: 'center' }, alertSection: { marginTop: 22 }, alertCard: { backgroundColor: '#2b1d27', borderColor: '#8b4b61', borderWidth: 1, borderRadius: 12, padding: 14, marginBottom: 8 }, alertHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, alertDevice: { color: theme.colors.text, fontSize: 14, fontWeight: '700', flex: 1 }, alertStatus: { color: '#ffb454', fontSize: 10, fontWeight: '800', textTransform: 'uppercase' }, alertOwner: { color: theme.colors.textMuted, fontSize: 12, marginTop: 5 }, alertMeta: { color: theme.colors.textMuted, fontSize: 11, marginTop: 4 }, alertEmpty: { borderColor: theme.colors.border, borderWidth: 1, borderRadius: 12, padding: 18, alignItems: 'center' }, message: { color: theme.colors.textMuted, fontSize: 12, textAlign: 'center', marginTop: 12 }, signOut: { minHeight: 44, marginTop: 24, borderColor: theme.colors.border, borderWidth: 1, borderRadius: theme.radius.control, alignItems: 'center', justifyContent: 'center' }, signOutText: { color: theme.colors.textMuted, fontSize: 13, fontWeight: '500' },
});
