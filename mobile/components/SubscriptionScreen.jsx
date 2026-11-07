import React, { useEffect, useState, useCallback } from 'react';
import {
    View,
    Text,
    ScrollView,
    TouchableOpacity,
    ActivityIndicator,
    StyleSheet,
    RefreshControl,
} from 'react-native';
import * as WebBrowser from 'expo-web-browser';
import Constants from 'expo-constants';
import { useTheme } from '../src/theme/ThemeContext';
import { fetchSubscriptionStatus, createCheckoutSession, createBillingPortalSession } from '../src/api/subscription';

// Lets the in-app browser close itself when Stripe redirects back to the app.
WebBrowser.maybeCompleteAuthSession();

// ── Plan metadata (mirrors config/stripe.php) ────────────────────────────────
const PLANS = [
    {
        key:         'free',
        label:       'Free',
        priceLabel:  '$0 / month',
        color:       '#64748B',
        limit:       3,
        features:    ['Up to 3 integrations', 'TOTP Authenticator', 'Push Notifications'],
    },
    {
        key:         'pro',
        label:       'Pro',
        priceLabel:  '$5 / month',
        color:       '#3D5AF1',
        limit:       10,
        features:    ['Up to 10 integrations', 'TOTP Authenticator', 'Push Notifications', 'Priority support'],
    },
    {
        key:         'ultra',
        label:       'Ultra',
        priceLabel:  '$10 / month',
        color:       '#8B5CF6',
        limit:       20,
        features:    ['Up to 20 integrations', 'TOTP Authenticator', 'Push Notifications', 'Priority support', 'Advanced analytics'],
    },
    {
        key:         'ultra_plus',
        label:       'Ultra Plus',
        priceLabel:  '$50 / month',
        color:       '#F59E0B',
        limit:       null,
        features:    ['Unlimited integrations', 'TOTP Authenticator', 'Push Notifications', 'Priority support', 'Advanced analytics', 'API access & webhooks', 'Dedicated manager'],
    },
];

export default function SubscriptionScreen({ user }) {
    const theme = useTheme();
    const s     = styles(theme);

    const [status,      setStatus]      = useState(null);   // { plan, limit, plan_label }
    const [loading,     setLoading]     = useState(true);
    const [refreshing,  setRefreshing]  = useState(false);
    const [error,       setError]       = useState(null);
    const [processing,  setProcessing]  = useState(null);   // plan key being processed
    const [portalLoading, setPortalLoading] = useState(false);

    // ── Fetch plan status ─────────────────────────────────────────────────────
    const load = useCallback(async (silent = false) => {
        if (!silent) setLoading(true);
        setError(null);
        try {
            const data = await fetchSubscriptionStatus();
            setStatus(data);
        } catch (e) {
            setError('Could not load subscription status. Please try again.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useEffect(() => { load(); }, [load]);

    // ── Handle Stripe Checkout ────────────────────────────────────────────────
    // The app's own deep link: exp://… in Expo Go, securets:// in a build.
    const appRedirectUrl = () => {
        const base = Constants.linkingUri.endsWith('/') ? Constants.linkingUri : `${Constants.linkingUri}/`;
        return `${base}subscription`;
    };

    const handleSubscribe = async (planKey) => {
        setProcessing(planKey);
        try {
            const redirectUrl = appRedirectUrl();
            const data        = await createCheckoutSession(planKey, redirectUrl);
            const checkoutUrl = data.checkout_url;
            if (!checkoutUrl) throw new Error('No checkout URL returned.');

            // In-app browser closes itself when Stripe redirects back to redirectUrl.
            const result = await WebBrowser.openAuthSessionAsync(checkoutUrl, redirectUrl);

            // Re-fetch plan status after user returns (success or cancel)
            if (result.type === 'success' || result.type === 'dismiss') {
                await load(true);
            }
        } catch (e) {
            setError(e.response?.data?.error || e.message || 'Failed to open checkout. Please try again.');
        } finally {
            setProcessing(null);
        }
    };

    // ── Manage billing (Stripe-hosted portal) ─────────────────────────────────
    const handleManageBilling = async () => {
        setPortalLoading(true);
        try {
            const data = await createBillingPortalSession();
            if (data.portal_url) {
                await WebBrowser.openBrowserAsync(data.portal_url);
                await load(true);
            }
        } catch (e) {
            setError(e.response?.data?.error || 'Could not open the billing portal.');
        } finally {
            setPortalLoading(false);
        }
    };

    // ── Guest guard ───────────────────────────────────────────────────────────
    if (!user) {
        return (
            <View style={[s.center, { flex: 1 }]}>
                <Text style={s.emptyIcon}>🔒</Text>
                <Text style={s.emptyTitle}>Sign in Required</Text>
                <Text style={s.emptyBody}>Please sign in to view and manage your subscription.</Text>
            </View>
        );
    }

    if (loading) {
        return (
            <View style={[s.center, { flex: 1 }]}>
                <ActivityIndicator size="large" color="#3D5AF1" />
                <Text style={[s.emptyBody, { marginTop: 12 }]}>Loading your plan…</Text>
            </View>
        );
    }

    const currentPlanKey = status?.plan ?? 'free';

    return (
        <ScrollView
            style={s.root}
            contentContainerStyle={s.content}
            refreshControl={
                <RefreshControl
                    refreshing={refreshing}
                    onRefresh={() => { setRefreshing(true); load(true); }}
                    tintColor="#3D5AF1"
                />
            }
        >
            {/* Header */}
            <View style={s.header}>
                <Text style={s.headerTitle}>Subscription Plans</Text>
                <Text style={s.headerSub}>Choose the plan that fits your security needs.</Text>
                {currentPlanKey !== 'free' && (
                    <TouchableOpacity
                        style={[s.btnManage, portalLoading && s.btnDisabled]}
                        onPress={handleManageBilling}
                        disabled={portalLoading}
                        activeOpacity={0.8}
                    >
                        {portalLoading
                            ? <ActivityIndicator color="#F8FAFC" size="small" />
                            : <Text style={s.btnManageText}>Manage Billing</Text>}
                    </TouchableOpacity>
                )}
            </View>

            {/* Error banner */}
            {error ? (
                <View style={s.errorBanner}>
                    <Text style={s.errorText}>{error}</Text>
                </View>
            ) : null}

            {/* Plan cards */}
            {PLANS.map((plan) => {
                const isCurrent    = currentPlanKey === plan.key;
                const isFree       = plan.key === 'free';
                const isProcessing = processing === plan.key;

                return (
                    <View
                        key={plan.key}
                        style={[
                            s.card,
                            isCurrent && { borderColor: plan.color, borderWidth: 2 },
                        ]}
                    >
                        {/* Accent top bar */}
                        <View style={[s.accentBar, { backgroundColor: plan.color }]} />

                        {/* Current badge */}
                        {isCurrent && (
                            <View style={[s.badge, { borderColor: plan.color }]}>
                                <Text style={[s.badgeText, { color: plan.color }]}>✓ Current Plan</Text>
                            </View>
                        )}

                        {/* Plan name + price */}
                        <Text style={[s.planName, { color: plan.color }]}>{plan.label}</Text>
                        <Text style={s.planPrice}>{plan.priceLabel}</Text>
                        <Text style={s.planLimit}>
                            {plan.limit ? `${plan.limit} integrations` : 'Unlimited integrations'}
                        </Text>

                        {/* Features */}
                        <View style={s.featureList}>
                            {plan.features.map((f, i) => (
                                <View key={i} style={s.featureRow}>
                                    <Text style={[s.featureCheck, { color: plan.color }]}>✓</Text>
                                    <Text style={s.featureText}>{f}</Text>
                                </View>
                            ))}
                        </View>

                        {/* CTA */}
                        {isCurrent || isFree ? (
                            <View style={[s.btnCurrent, isCurrent && { borderColor: plan.color + '55' }]}>
                                <Text style={s.btnCurrentText}>
                                    {isCurrent ? '✓ Active' : 'Free Forever'}
                                </Text>
                            </View>
                        ) : (
                            <TouchableOpacity
                                style={[s.btnSubscribe, { backgroundColor: plan.color }, isProcessing && s.btnDisabled]}
                                activeOpacity={0.85}
                                disabled={!!processing}
                                onPress={() => handleSubscribe(plan.key)}
                            >
                                {isProcessing ? (
                                    <ActivityIndicator color="#fff" size="small" />
                                ) : (
                                    <Text style={s.btnSubscribeText}>Upgrade to {plan.label}</Text>
                                )}
                            </TouchableOpacity>
                        )}
                    </View>
                );
            })}
        </ScrollView>
    );
}

const styles = (theme) => StyleSheet.create({
    root:    { flex: 1, backgroundColor: theme.bg ?? '#0F172A' },
    content: { padding: 20, paddingBottom: 48 },
    center:  { alignItems: 'center', justifyContent: 'center', padding: 40 },

    header:     { marginBottom: 24 },
    headerTitle:{ fontSize: 26, fontWeight: '800', color: theme.text ?? '#F8FAFC', marginBottom: 4 },
    headerSub:  { fontSize: 14, color: theme.muted ?? '#94A3B8' },

    errorBanner: {
        backgroundColor: 'rgba(239,68,68,0.12)', borderRadius: 10,
        padding: 14, marginBottom: 16,
    },
    errorText: { color: '#F87171', fontSize: 13, fontWeight: '500' },

    card: {
        backgroundColor: theme.surface ?? '#1E293B',
        borderRadius: 20,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.08)',
        marginBottom: 16,
        padding: 22,
        overflow: 'hidden',
    },
    accentBar: {
        position: 'absolute', top: 0, left: 0, right: 0, height: 4,
        borderTopLeftRadius: 20, borderTopRightRadius: 20,
    },

    badge: {
        alignSelf: 'flex-start',
        borderWidth: 1, borderRadius: 20,
        paddingHorizontal: 10, paddingVertical: 3,
        marginBottom: 12, marginTop: 4,
        backgroundColor: 'rgba(255,255,255,0.04)',
    },
    badgeText: { fontSize: 11, fontWeight: '700' },

    planName:  { fontSize: 20, fontWeight: '800', marginBottom: 4, marginTop: 8 },
    planPrice: { fontSize: 28, fontWeight: '800', color: theme.text ?? '#F8FAFC', marginBottom: 2 },
    planLimit: { fontSize: 13, color: theme.muted ?? '#94A3B8', marginBottom: 18 },

    featureList: { marginBottom: 22 },
    featureRow:  { flexDirection: 'row', alignItems: 'flex-start', marginBottom: 8, gap: 8 },
    featureCheck:{ fontSize: 12, fontWeight: '800', marginTop: 1 },
    featureText: { fontSize: 13, color: theme.muted ?? '#94A3B8', flex: 1 },

    btnSubscribe: {
        borderRadius: 12, paddingVertical: 14, alignItems: 'center',
    },
    btnDisabled: { opacity: 0.6 },
    btnSubscribeText: { color: '#fff', fontSize: 14, fontWeight: '700' },

    btnCurrent: {
        borderRadius: 12, paddingVertical: 14, alignItems: 'center',
        borderWidth: 1.5, borderColor: 'rgba(255,255,255,0.1)',
    },
    btnCurrentText: { color: theme.muted ?? '#94A3B8', fontSize: 14, fontWeight: '600' },

    btnManage: {
        marginTop: 16, alignSelf: 'flex-start',
        backgroundColor: 'rgba(255,255,255,0.06)',
        borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)',
        borderRadius: 10, paddingHorizontal: 16, paddingVertical: 10,
    },
    btnManageText: { color: theme.text ?? '#F8FAFC', fontSize: 13, fontWeight: '700' },

    emptyIcon:  { fontSize: 40, marginBottom: 12 },
    emptyTitle: { fontSize: 18, fontWeight: '700', color: theme.text ?? '#F8FAFC', marginBottom: 8 },
    emptyBody:  { fontSize: 14, color: theme.muted ?? '#94A3B8', textAlign: 'center' },
});
