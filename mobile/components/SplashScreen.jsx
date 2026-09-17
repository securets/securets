import React, { useEffect, useRef, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Animated,
  TouchableOpacity,
  SafeAreaView,
  Dimensions,
  Easing
} from 'react-native';
import Svg, {
  Polygon,
  Circle,
  Path,
  Defs,
  LinearGradient,
  Stop,
  Line,
  Text as SvgText
} from 'react-native-svg';

const { width } = Dimensions.get('window');

export default function SplashScreen({ onFinish }) {
  const [secondsLeft, setSecondsLeft] = useState(5);
  const fadeAnim = useRef(new Animated.Value(1)).current;
  const progressAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    // 5-second progress bar animation
    Animated.timing(progressAnim, {
      toValue: 1,
      duration: 5000,
      easing: Easing.linear,
      useNativeDriver: false,
    }).start();

    // 1-second interval countdown
    const interval = setInterval(() => {
      setSecondsLeft((prev) => {
        if (prev <= 1) {
          clearInterval(interval);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    // Fade out and finish after 5 seconds (5000ms)
    const timeout = setTimeout(() => {
      dismiss();
    }, 5000);

    return () => {
      clearInterval(interval);
      clearTimeout(timeout);
    };
  }, []);

  const dismiss = () => {
    Animated.timing(fadeAnim, {
      toValue: 0,
      duration: 700,
      useNativeDriver: true,
    }).start(() => {
      if (onFinish) onFinish();
    });
  };

  const progressWidth = progressAnim.interpolate({
    inputRange: [0, 1],
    outputRange: ['0%', '100%'],
  });

  return (
    <Animated.View style={[styles.container, { opacity: fadeAnim }]}>
      <SafeAreaView style={styles.safeArea}>
        
        <!-- Top Right Skip Button -->
        <TouchableOpacity style={styles.skipButton} onPress={dismiss} activeOpacity={0.7}>
          <Text style={styles.skipText}>Skip ➔</Text>
        </TouchableOpacity>

        <!-- Centered Branding Group -->
        <View style={styles.brandingCenter}>
          <View style={styles.logoRow}>
            
            <!-- 1. Hexagon Keyhole Logo -->
            <Svg width={70} height={70} viewBox="0 0 100 100" fill="none">
              <!-- Outer Hexagon -->
              <Polygon
                points="50,4 92,26.5 92,73.5 50,96 8,73.5 8,26.5"
                stroke="#0d1527"
                strokeWidth="5"
                fill="none"
                strokeLinejoin="round"
              />
              <!-- Inner Hexagon -->
              <Polygon
                points="50,14 83,32.5 83,67.5 50,86 17,67.5 17,32.5"
                stroke="#0d1527"
                strokeWidth="4.5"
                fill="none"
                strokeLinejoin="round"
              />
              <!-- Keyhole Circle -->
              <Circle cx="50" cy="42" r="9.5" fill="#0d1527" />
              <!-- Keyhole Slot -->
              <Path d="M44.5 45.5 L41.5 66.5 L58.5 66.5 L55.5 45.5 Z" fill="#0d1527" />
            </Svg>

            <!-- 2. Brand Text Group -->
            <View style={styles.textGroup}>
              <View style={styles.titleRow}>
                <Text style={styles.brandTitle}>Secure</Text>
                
                <!-- 3D ThreatShield "TS" Emblem -->
                <Svg width={42} height={48} viewBox="0 0 60 70" fill="none" style={styles.shieldSvg}>
                  <Defs>
                    <LinearGradient id="silverBorder" x1="0" y1="0" x2="60" y2="70" gradientUnits="userSpaceOnUse">
                      <Stop offset="0%" stopColor="#ffffff" />
                      <Stop offset="50%" stopColor="#cbd5e1" />
                      <Stop offset="100%" stopColor="#475569" />
                    </LinearGradient>
                    <LinearGradient id="redGrad" x1="0" y1="0" x2="30" y2="70" gradientUnits="userSpaceOnUse">
                      <Stop offset="0%" stopColor="#ef4444" />
                      <Stop offset="100%" stopColor="#991b1b" />
                    </LinearGradient>
                    <LinearGradient id="blueGrad" x1="30" y1="0" x2="60" y2="70" gradientUnits="userSpaceOnUse">
                      <Stop offset="0%" stopColor="#3b82f6" />
                      <Stop offset="100%" stopColor="#1e3a8a" />
                    </LinearGradient>
                  </Defs>

                  <!-- Outer Silver Frame -->
                  <Path d="M30 4 L54 12 V34 C54 50 30 64 30 64 C30 64 6 50 6 34 V12 L30 4 Z" fill="url(#silverBorder)" />
                  <!-- Left Red -->
                  <Path d="M30 7 L8 14 V34 C8 48 30 60 30 60 V7 Z" fill="url(#redGrad)" />
                  <!-- Right Blue -->
                  <Path d="M30 7 L52 14 V34 C52 48 30 60 30 60 V7 Z" fill="url(#blueGrad)" />
                  <!-- Specular Line -->
                  <Line x1="30" y1="7" x2="30" y2="60" stroke="#ffffff" strokeWidth="1" opacity={0.6} />
                  <!-- "TS" Monogram -->
                  <SvgText
                    x="30"
                    y="41"
                    fontFamily="sans-serif"
                    fontWeight="900"
                    fontSize="21"
                    fontStyle="italic"
                    fill="#ffffff"
                    textAnchor="middle"
                    letterSpacing="-1"
                  >
                    TS
                  </SvgText>
                </Svg>
              </View>

              <Text style={styles.brandSubtitle}>ThreatShield</Text>
            </View>
          </View>
        </View>

        <!-- Bottom Timer & Progress Bar -->
        <View style={styles.bottomTimerContainer}>
          <View style={styles.progressTrack}>
            <Animated.View style={[styles.progressBar, { width: progressWidth }]} />
          </View>
          <Text style={styles.timerText}>
            Loading SecureHub... <Text style={styles.boldTimer}>{secondsLeft}s</Text>
          </Text>
        </View>

      </SafeAreaView>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  container: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: '#3866E6',
    zIndex: 9999,
  },
  safeArea: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  skipButton: {
    position: 'absolute',
    top: 50,
    right: 24,
    backgroundColor: 'rgba(0,0,0,0.2)',
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 20,
  },
  skipText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '600',
  },
  brandingCenter: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  textGroup: {
    marginLeft: 12,
    justifyContent: 'center',
  },
  titleRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  brandTitle: {
    fontSize: 36,
    fontWeight: '800',
    color: '#0d1527',
    letterSpacing: -0.5,
  },
  shieldSvg: {
    marginLeft: 6,
  },
  brandSubtitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#0d1527',
    marginTop: 2,
  },
  bottomTimerContainer: {
    position: 'absolute',
    bottom: 50,
    alignItems: 'center',
  },
  progressTrack: {
    width: width * 0.55,
    height: 6,
    backgroundColor: 'rgba(0, 0, 0, 0.2)',
    borderRadius: 3,
    overflow: 'hidden',
    marginBottom: 8,
  },
  progressBar: {
    height: '100%',
    backgroundColor: '#ffffff',
    borderRadius: 3,
  },
  timerText: {
    color: 'rgba(255, 255, 255, 0.9)',
    fontSize: 12,
    fontWeight: '500',
  },
  boldTimer: {
    fontWeight: '700',
    color: '#ffffff',
  },
});
