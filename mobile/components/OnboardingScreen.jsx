import React, { useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Dimensions,
  TouchableOpacity,
  FlatList,
  Animated,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

const { width, height } = Dimensions.get('window');

const SLIDES = [
  {
    id: '1',
    title: 'Welcome to SecureTS',
    description:
      'SecureTS ThreatShield is a cutting-edge Multi-Factor Authentication platform that keeps all your linked accounts protected from modern threats.',
  },
  {
    id: '2',
    title: 'MFA Protection',
    description:
      'Seamlessly verify your identity using secure one-time codes, biometrics, and hardware keys — all in one place.',
  },
  {
    id: '3',
    title: 'Zero Passwords',
    description:
      'No stored passwords, no DB credentials exposed. SecureTS uses token-based authentication so your data stays private.',
  },
  {
    id: '4',
    title: 'Phishing Immune',
    description:
      'Cryptographic keys bound to your device mean phishing attacks can\'t steal your access — ever.',
  },
];

export default function OnboardingScreen({ onFinish }) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const flatListRef = useRef(null);
  const scrollX = useRef(new Animated.Value(0)).current;

  const handleGetStarted = async () => {
    try {
      await AsyncStorage.setItem('onboarding_complete', 'true');
    } catch (_) { /* ignore */ }
    onFinish && onFinish();
  };

  const onViewableItemsChanged = useRef(({ viewableItems }) => {
    if (viewableItems.length > 0) {
      setCurrentIndex(viewableItems[0].index ?? 0);
    }
  }).current;

  const renderItem = ({ item }) => (
    <View style={styles.slide}>
      <Text style={styles.slideTitle}>{item.title}</Text>
      <Text style={styles.slideDesc}>{item.description}</Text>
    </View>
  );

  return (
    <View style={styles.container}>

      {/* Slides */}
      <FlatList
        ref={flatListRef}
        data={SLIDES}
        keyExtractor={(item) => item.id}
        renderItem={renderItem}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        onScroll={Animated.event(
          [{ nativeEvent: { contentOffset: { x: scrollX } } }],
          { useNativeDriver: false }
        )}
        onViewableItemsChanged={onViewableItemsChanged}
        viewabilityConfig={{ itemVisiblePercentThreshold: 50 }}
      />

      {/* Pagination dots */}
      <View style={styles.dotsContainer}>
        {SLIDES.map((_, index) => (
          <View
            key={index}
            style={[
              styles.dot,
              index === currentIndex ? styles.dotActive : styles.dotInactive,
            ]}
          />
        ))}
      </View>

      {/* Get Started button */}
      <TouchableOpacity
        style={styles.getStartedBtn}
        onPress={handleGetStarted}
        activeOpacity={0.85}
        id="getStartedBtn"
      >
        <Text style={styles.getStartedText}>Get Started</Text>
      </TouchableOpacity>

      <View style={styles.bottomPad} />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#3866E6',
    alignItems: 'center',
  },

  // Slides
  slide: {
    width,
    flex: 1,
    alignItems: 'flex-start',
    justifyContent: 'center',
    paddingHorizontal: 36,
    paddingTop: 80,
  },
  slideTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: 'white',
    marginBottom: 16,
    letterSpacing: -0.3,
  },
  slideDesc: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.75)',
    lineHeight: 22,
  },

  // Dots
  dotsContainer: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 24,
  },
  dot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  dotActive: {
    backgroundColor: 'white',
    width: 20,
  },
  dotInactive: {
    backgroundColor: 'rgba(255,255,255,0.35)',
  },

  // Button
  getStartedBtn: {
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.4)',
    borderRadius: 40,
    paddingVertical: 16,
    paddingHorizontal: 48,
    marginBottom: 12,
    minWidth: width * 0.65,
    alignItems: 'center',
  },
  getStartedText: {
    color: 'white',
    fontSize: 16,
    fontWeight: '600',
    letterSpacing: 0.3,
  },
  bottomPad: { height: 32 },
});
