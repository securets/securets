import React, { useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Animated,
  Dimensions,
} from 'react-native';

const { width } = Dimensions.get('window');

// ─── Inline SVG-like logo using View shapes ───
function HexLock() {
  return (
    <View style={styles.hexOuter}>
      <Text style={styles.hexLock}>🔒</Text>
    </View>
  );
}

function ShieldIcon() {
  return (
    <View style={styles.shield}>
      <View style={styles.shieldTopLeft} />
      <View style={styles.shieldTopRight} />
      <View style={styles.shieldBotLeft} />
      <View style={styles.shieldBotRight} />
      <View style={styles.shieldOverlay}>
        <Text style={styles.shieldCheck}>✓</Text>
      </View>
    </View>
  );
}

export default function SplashScreen({ onFinish }) {
  const scale   = useRef(new Animated.Value(0.7)).current;
  const opacity = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    // Animate in
    Animated.parallel([
      Animated.spring(scale, {
        toValue: 1,
        friction: 5,
        tension: 80,
        useNativeDriver: true,
      }),
      Animated.timing(opacity, {
        toValue: 1,
        duration: 600,
        useNativeDriver: true,
      }),
    ]).start();

    // Dismiss after 5 seconds
    const timer = setTimeout(() => {
      Animated.timing(opacity, {
        toValue: 0,
        duration: 500,
        useNativeDriver: true,
      }).start(() => onFinish && onFinish());
    }, 5000);

    return () => clearTimeout(timer);
  }, []);  // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <Animated.View style={[styles.container, { opacity }]}>
      <Animated.View style={[styles.logoWrap, { transform: [{ scale }] }]}>
        <View style={styles.logoRow}>
          <HexLock />
          <View style={styles.brandTextWrap}>
            <Text style={styles.brandName}>
              Secure<Text style={styles.brandTs}>TS</Text>
            </Text>
          </View>
          <ShieldIcon />
        </View>
        <Text style={styles.tagline}>ThreatShield</Text>
      </Animated.View>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  container: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: '#3866E6',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 9999,
  },
  logoWrap: {
    alignItems: 'center',
    gap: 6,
  },
  logoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },

  // Hexagon lock
  hexOuter: {
    width: 38,
    height: 38,
    backgroundColor: '#0F172A',
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    // approximate hexagon with high border radius
    transform: [{ rotate: '0deg' }],
  },
  hexLock: { fontSize: 18 },

  // Shield
  shield: {
    width: 34,
    height: 38,
    borderRadius: 4,
    overflow: 'hidden',
    flexDirection: 'row',
    flexWrap: 'wrap',
    position: 'relative',
  },
  shieldTopLeft:  { width: 17, height: 19, backgroundColor: '#C0392B' },
  shieldTopRight: { width: 17, height: 19, backgroundColor: '#ECF0F1' },
  shieldBotLeft:  { width: 17, height: 19, backgroundColor: '#27AE60' },
  shieldBotRight: { width: 17, height: 19, backgroundColor: '#2980B9' },
  shieldOverlay: {
    ...StyleSheet.absoluteFillObject,
    alignItems: 'center',
    justifyContent: 'center',
  },
  shieldCheck: { color: 'white', fontSize: 16, fontWeight: '700' },

  // Brand text
  brandTextWrap: { alignItems: 'flex-start' },
  brandName: {
    fontSize: 22,
    fontWeight: '800',
    color: '#0F172A',
    letterSpacing: -0.3,
  },
  brandTs: { color: '#1E3A8A' },
  tagline: {
    fontSize: 11,
    fontWeight: '600',
    color: '#0F172A',
    letterSpacing: 1.5,
    textTransform: 'uppercase',
    marginTop: 2,
  },
});
