import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  StatusBar,
} from 'react-native';

// ─── Mini Logo Component ────────────────────────────────────────────────────
function LogoHeader() {
  return (
    <View style={styles.logoRow}>
      <View style={styles.hexOuter}>
        <Text style={styles.hexLock}>🔒</Text>
      </View>
      <View style={styles.shield}>
        <View style={styles.stl} />
        <View style={styles.str} />
        <View style={styles.sbl} />
        <View style={styles.sbr} />
        <View style={styles.shieldOverlay}>
          <Text style={styles.shieldCheck}>✓</Text>
        </View>
      </View>
      <View>
        <Text style={styles.brandName}>Secure<Text style={styles.brandTs}>TS</Text></Text>
        <Text style={styles.brandSub}>ThreatShield</Text>
      </View>
    </View>
  );
}

// ─── Notification Pill ──────────────────────────────────────────────────────
function NotificationItem({ title, time, type }) {
  const colors = { threat: '#E11D48', info: '#3866E6', success: '#22C55E' };
  const color  = colors[type] || colors.info;
  return (
    <View style={[styles.notifCard, { borderLeftColor: color }]}>
      <View style={{ flex: 1 }}>
        <Text style={styles.notifTitle}>{title}</Text>
        <Text style={styles.notifTime}>{time}</Text>
      </View>
      <View style={[styles.notifDot, { backgroundColor: color }]} />
    </View>
  );
}

// ─── Main Screen ────────────────────────────────────────────────────────────
export default function DashboardScreen({ user, onLogout }) {
  const [activeTab, setActiveTab] = useState('dashboard');

  const notifications = []; // Empty state matches picture 5

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#0F172A" />

      {/* ── Header ── */}
      <View style={styles.header}>
        <LogoHeader />
      </View>

      {/* ── Welcome Banner ── */}
      <View style={styles.welcomeBanner}>
        <Text style={styles.welcomeTitle}>
          Welcome, {user ? user.name || 'User' : 'User'}!
        </Text>
        <Text style={styles.welcomeSub}>All linked accounts are protected.</Text>
      </View>

      {/* ── Notifications Feed ── */}
      <ScrollView
        style={styles.feed}
        contentContainerStyle={styles.feedContent}
        showsVerticalScrollIndicator={false}
      >
        {notifications.length === 0 ? (
          <View style={styles.emptyState}>
            <Text style={styles.emptyText}>No Notifications.</Text>
          </View>
        ) : (
          notifications.map((n, i) => (
            <NotificationItem key={i} {...n} />
          ))
        )}
      </ScrollView>

      {/* ── Bottom Nav ── */}
      <View style={styles.bottomNav}>
        {/* Dashboard */}
        <TouchableOpacity
          id="navDashboard"
          style={[styles.navItem, activeTab === 'dashboard' && styles.navItemActive]}
          onPress={() => setActiveTab('dashboard')}
        >
          <View style={[styles.navSquare, activeTab === 'dashboard' && styles.navSquareActive]}>
            <Text style={styles.navIcon}>⊞</Text>
          </View>
        </TouchableOpacity>

        {/* Shield / Integrations */}
        <TouchableOpacity
          id="navIntegrations"
          style={[styles.navItem, activeTab === 'integrations' && styles.navItemActive]}
          onPress={() => setActiveTab('integrations')}
        >
          <View style={[styles.navSquare, activeTab === 'integrations' && styles.navSquareActive]}>
            <Text style={styles.navIcon}>🔗</Text>
          </View>
        </TouchableOpacity>

        {/* Settings */}
        <TouchableOpacity
          id="navSettings"
          style={[styles.navItem, activeTab === 'settings' && styles.navItemActive]}
          onPress={() => {
            setActiveTab('settings');
            if (onLogout) onLogout();
          }}
        >
          <View style={[styles.navSquare, activeTab === 'settings' && styles.navSquareActive]}>
            <Text style={styles.navIcon}>⚙️</Text>
          </View>
        </TouchableOpacity>
      </View>

    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#0A0F1E',
  },

  // ── Header ──
  header: {
    backgroundColor: '#0F172A',
    paddingTop: 52,
    paddingBottom: 14,
    paddingHorizontal: 20,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(56, 102, 230, 0.3)',
  },
  logoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  hexOuter: {
    width: 30, height: 30,
    backgroundColor: '#1E293B',
    borderRadius: 6,
    alignItems: 'center', justifyContent: 'center',
  },
  hexLock: { fontSize: 14 },
  shield: {
    width: 26, height: 30,
    borderRadius: 3,
    overflow: 'hidden',
    flexDirection: 'row', flexWrap: 'wrap',
    position: 'relative',
  },
  stl: { width: 13, height: 15, backgroundColor: '#C0392B' },
  str: { width: 13, height: 15, backgroundColor: '#ECF0F1' },
  sbl: { width: 13, height: 15, backgroundColor: '#27AE60' },
  sbr: { width: 13, height: 15, backgroundColor: '#2980B9' },
  shieldOverlay: {
    ...StyleSheet.absoluteFillObject,
    alignItems: 'center', justifyContent: 'center',
  },
  shieldCheck: { color: 'white', fontSize: 12, fontWeight: '700' },
  brandName: { fontSize: 15, fontWeight: '800', color: 'white' },
  brandTs:   { color: '#3866E6' },
  brandSub:  { fontSize: 8, color: '#64748B', fontWeight: '500' },

  // ── Welcome Banner ──
  welcomeBanner: {
    backgroundColor: '#0F172A',
    paddingHorizontal: 20,
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255,255,255,0.06)',
  },
  welcomeTitle: {
    color: 'white',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 4,
  },
  welcomeSub: {
    color: '#3866E6',
    fontSize: 13,
    fontWeight: '500',
  },

  // ── Feed ──
  feed: { flex: 1 },
  feedContent: {
    flexGrow: 1,
    padding: 20,
  },
  emptyState: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: '40%',
  },
  emptyText: {
    color: '#3866E6',
    fontSize: 14,
    fontWeight: '500',
    opacity: 0.7,
  },

  // Notification cards
  notifCard: {
    backgroundColor: '#1E293B',
    borderRadius: 10,
    padding: 14,
    marginBottom: 12,
    flexDirection: 'row',
    alignItems: 'center',
    borderLeftWidth: 3,
  },
  notifTitle: { color: 'white', fontSize: 14, fontWeight: '600', marginBottom: 4 },
  notifTime:  { color: '#64748B', fontSize: 11 },
  notifDot: { width: 8, height: 8, borderRadius: 4, marginLeft: 10 },

  // ── Bottom Nav ──
  bottomNav: {
    flexDirection: 'row',
    backgroundColor: '#0F172A',
    borderTopWidth: 1,
    borderTopColor: 'rgba(56,102,230,0.3)',
    paddingBottom: 24,
    paddingTop: 12,
    justifyContent: 'space-evenly',
    alignItems: 'center',
  },
  navItem: { alignItems: 'center', flex: 1 },
  navItemActive: {},
  navSquare: {
    width: 48, height: 40,
    backgroundColor: 'rgba(255,255,255,0.1)',
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  navSquareActive: {
    backgroundColor: '#3866E6',
  },
  navIcon: { fontSize: 18 },
});
