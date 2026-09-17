import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  SafeAreaView,
  ScrollView,
  ActivityIndicator
} from 'react-native';
import Svg, { Polygon, Circle, Path } from 'react-native-svg';

export default function DashboardScreen({ onReplaySplash }) {
  const [apiData, setApiData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Fetch telemetry from Laravel API backend
    fetch('http://localhost:8000/api/status')
      .then((res) => res.json())
      .then((data) => {
        setApiData(data);
        setLoading(false);
      })
      .catch((err) => {
        console.log('API Fetch Error:', err);
        // Fallback default telemetry if offline
        setApiData({
          status: 'Operational',
          protection: 'ThreatShield Mobile v2.4',
          splash_delay: '5.0 Seconds',
          backend: 'PHP Laravel API'
        });
        setLoading(false);
      });
  }, []);

  return (
    <SafeAreaView style={styles.container}>
      <!-- Top Header -->
      <View style={styles.header}>
        <View style={styles.brandRow}>
          <Svg width={32} height={32} viewBox="0 0 100 100" fill="none">
            <Polygon points="50,4 92,26.5 92,73.5 50,96 8,73.5 8,26.5" stroke="#3866E6" strokeWidth="6" fill="none" />
            <Polygon points="50,14 83,32.5 83,67.5 50,86 17,67.5 17,32.5" stroke="#3866E6" strokeWidth="5" fill="none" />
            <Circle cx="50" cy="42" r="9" fill="#3866E6" />
            <Path d="M45 46 L42 66 L58 66 L55 46 Z" fill="#3866E6" />
          </Svg>
          <View style={styles.brandTextGroup}>
            <Text style={styles.headerTitle}>
              Secure<Text style={{ color: '#3866E6' }}>Hub</Text>
            </Text>
            <Text style={styles.headerSubtitle}>ThreatShield Mobile App</Text>
          </View>
        </View>

        <TouchableOpacity style={styles.replayButton} onPress={onReplaySplash} activeOpacity={0.7}>
          <Text style={styles.replayButtonText}>Test Splash (5s)</Text>
        </TouchableOpacity>
      </View>

      <!-- Main Body Scroll -->
      <ScrollView contentContainerStyle={styles.scrollContent}>
        
        <!-- Welcome Banner -->
        <View style={styles.banner}>
          <Text style={styles.badge}>System Active</Text>
          <Text style={styles.bannerTitle}>React Native + Laravel App</Text>
          <Text style={styles.bannerSubtitle}>
            Your mobile app initialized with a 5-second ThreatShield splash delay connected to the PHP Laravel API backend.
          </Text>

          <TouchableOpacity style={styles.bannerButton} onPress={onReplaySplash} activeOpacity={0.8}>
            <Text style={styles.bannerButtonText}>Replay 5s Splash Screen</Text>
          </TouchableOpacity>
        </View>

        <!-- Status Cards -->
        <Text style={styles.sectionTitle}>App Telemetry & Status</Text>

        {loading ? (
          <ActivityIndicator size="large" color="#3866E6" style={{ marginTop: 20 }} />
        ) : (
          <View style={styles.grid}>
            <View style={styles.card}>
              <Text style={styles.cardIcon}>⏱️</Text>
              <View>
                <Text style={styles.cardLabel}>Splash Delay</Text>
                <Text style={styles.cardValue}>{apiData?.splash_delay || '5.0 Seconds'}</Text>
              </View>
            </View>

            <View style={styles.card}>
              <Text style={styles.cardIcon}>🛡️</Text>
              <View>
                <Text style={styles.cardLabel}>Protection</Text>
                <Text style={styles.cardValue}>{apiData?.protection || 'ThreatShield Mobile'}</Text>
              </View>
            </View>

            <View style={styles.card}>
              <Text style={styles.cardIcon}>⚙️</Text>
              <View>
                <Text style={styles.cardLabel}>Backend API</Text>
                <Text style={styles.cardValue}>{apiData?.backend || 'PHP Laravel 12'}</Text>
              </View>
            </View>
          </View>
        )}

      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingVertical: 14,
    backgroundColor: '#ffffff',
    borderBottomWidth: 1,
    borderBottomColor: '#e2e8f0',
  },
  brandRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  brandTextGroup: {
    marginLeft: 10,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: '#0f172a',
  },
  headerSubtitle: {
    fontSize: 11,
    color: '#64748b',
    fontWeight: '500',
  },
  replayButton: {
    backgroundColor: '#3866E6',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
  },
  replayButtonText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '700',
  },
  scrollContent: {
    padding: 20,
  },
  banner: {
    backgroundColor: '#3866E6',
    borderRadius: 16,
    padding: 20,
    marginBottom: 24,
  },
  badge: {
    color: '#ffffff',
    backgroundColor: 'rgba(255,255,255,0.2)',
    alignSelf: 'flex-start',
    fontSize: 10,
    fontWeight: '700',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    marginBottom: 10,
    textTransform: 'uppercase',
  },
  bannerTitle: {
    color: '#ffffff',
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 6,
  },
  bannerSubtitle: {
    color: '#e0e7ff',
    fontSize: 13,
    lineHeight: 18,
    marginBottom: 16,
  },
  bannerButton: {
    backgroundColor: '#ffffff',
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderRadius: 10,
    alignSelf: 'flex-start',
  },
  bannerButtonText: {
    color: '#3866E6',
    fontWeight: '700',
    fontSize: 12,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 14,
  },
  grid: {
    gap: 12,
  },
  card: {
    backgroundColor: '#ffffff',
    padding: 16,
    borderRadius: 12,
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  cardIcon: {
    fontSize: 24,
    marginRight: 14,
  },
  cardLabel: {
    fontSize: 11,
    color: '#94a3b8',
    fontWeight: '600',
    textTransform: 'uppercase',
  },
  cardValue: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0f172a',
    marginTop: 2,
  },
});
