import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
    ActivityIndicator, Alert, FlatList, Image, Modal, Platform, RefreshControl,
    ScrollView, StyleSheet, Switch, Text, TextInput, TouchableOpacity,
    View,
} from 'react-native';
import { integrationApi } from '../src/api/integrations';
import { useTheme } from '../src/theme/ThemeContext';
import UpgradePrompt from './UpgradePrompt';

export default function IntegrationScreen({ onTabChange }) {
    const COLORS = useTheme();
    const styles = useMemo(() => createStyles(COLORS), [COLORS]);
    const [apps, setApps]             = useState([]);
    const [loading, setLoading]       = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [modalVisible, setModalVisible] = useState(false);
    const [editingApp, setEditingApp]     = useState(null);
    const [editAppName, setEditAppName]   = useState('');
    const [appToRemove, setAppToRemove]   = useState(null);
    const [appName, setAppName]       = useState('');
    const [appUrl, setAppUrl]         = useState('');
    const [adding, setAdding]         = useState(false);
    const [upgradeData, setUpgradeData] = useState(null); // { currentPlan, limit } | null
    const appsRef = useRef([]);
    const editedNamesRef = useRef({});

    useEffect(() => {
        appsRef.current = apps;
    }, [apps]);

    const fetchApps = useCallback(async () => {
        try {
            const res = await integrationApi.list();
            setApps(prev => {
                const prevMap = new Map(prev.map(a => [a.id, a]));
                return (res.data ?? []).map(app => ({
                    ...app,
                    name: editedNamesRef.current[app.id] ?? app.name,
                    totp: prevMap.get(app.id)?.totp,
                    totpExpiry: prevMap.get(app.id)?.totpExpiry,
                }));
            });
        } catch (e) {
            Alert.alert('Error', 'Could not load integrations.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useEffect(() => {
        fetchApps();
        // Poll for new integrations (sync with web side)
        const interval = setInterval(fetchApps, 5000);
        return () => clearInterval(interval);
    }, [fetchApps]);

    // Poll TOTP codes every second only for active apps
    useEffect(() => {
        if (!apps.length) return;
        let cancelled = false;
        const fetchTotp = async () => {
            const activeApps = appsRef.current.filter(app => app.is_active);
            if (!activeApps.length) return;
            const entries = await Promise.all(
                activeApps.map(async (app) => {
                    try {
                        const r = await integrationApi.totp(app.id);
                        return [app.id, { totp: r.data.code, totpExpiry: r.data.expires_in }];
                    } catch {
                        return [app.id, null];
                    }
                })
            );
            if (cancelled) return;
            const totpMap = Object.fromEntries(entries.filter(([, v]) => v !== null));
            setApps(prev =>
                prev.map(a => (a.is_active && totpMap[a.id] ? { ...a, ...totpMap[a.id] } : a))
            );
        };
        fetchTotp();
        const interval = setInterval(fetchTotp, 1000);
        return () => {
            cancelled = true;
            clearInterval(interval);
        };
    }, [apps.length]);

    const handleToggle = async (app) => {
        const nextActive = !app.is_active;
        // Immediate optimistic state update; halt TOTP when toggled off
        setApps(prev =>
            prev.map(a =>
                a.id === app.id
                    ? {
                        ...a,
                        is_active: nextActive,
                        ...(nextActive ? {} : { totp: null, totpExpiry: 0 }),
                    }
                    : a
            )
        );
        try {
            const res = await integrationApi.toggle(app.id);
            const serverActive = res.data?.is_active ?? nextActive;
            setApps(prev =>
                prev.map(a =>
                    a.id === app.id
                        ? {
                            ...a,
                            is_active: serverActive,
                            ...(serverActive ? {} : { totp: null, totpExpiry: 0 }),
                        }
                        : a
                )
            );
            if (serverActive) {
                try {
                    const r = await integrationApi.totp(app.id);
                    setApps(prev =>
                        prev.map(a =>
                            a.id === app.id && a.is_active
                                ? { ...a, totp: r.data.code, totpExpiry: r.data.expires_in }
                                : a
                        )
                    );
                } catch {}
            }
        } catch {
            // Revert on failure
            setApps(prev => prev.map(a => a.id === app.id ? { ...a, is_active: app.is_active } : a));
            Alert.alert('Error', 'Could not toggle app.');
        }
    };

    const performRemove = async (app) => {
        if (!app) return;
        const snapshot = appsRef.current;
        setAppToRemove(null);
        setApps(prev => prev.filter(a => a.id !== app.id));
        try {
            await integrationApi.remove(app.id);
        } catch {
            setApps(snapshot);
            Alert.alert('Error', 'Could not remove integration.');
        }
    };

    const handleRemove = (app) => {
        setAppToRemove(app);
    };

    const openEditModal = (app) => {
        setEditingApp(app);
        setEditAppName(app.name ?? '');
    };

    const handleSaveEdit = async () => {
        if (!editingApp) return;
        const trimmedName = editAppName.trim();
        if (!trimmedName) {
            Alert.alert('Required', 'Please enter an app name.');
            return;
        }
        const targetId = editingApp.id;
        editedNamesRef.current[targetId] = trimmedName;
        setApps(prev =>
            prev.map(a => (a.id === targetId ? { ...a, name: trimmedName } : a))
        );
        setEditingApp(null);
        setEditAppName('');
        try {
            await integrationApi.update?.(targetId, { name: trimmedName });
        } catch {}
    };

    const handleAdd = async () => {
        if (!appName.trim() || !appUrl.trim()) {
            Alert.alert('Required', 'Please enter both app name and URL.');
            return;
        }
        setAdding(true);
        try {
            const res = await integrationApi.add(appName.trim(), appUrl.trim());
            setApps(prev => [res.data, ...prev]);
            setModalVisible(false);
            setAppName(''); setAppUrl('');
            Alert.alert('Connected', 'This app is on your account. Copy the login link and secret. Approve or decline later logins in Alerts.');
        } catch (e) {
            // Plan limit reached — show the UpgradePrompt bottom sheet instead of a plain alert
            if (e.response?.status === 403 && e.response?.data?.upgrade_required) {
                setModalVisible(false);
                const { plan, limit } = e.response.data;
                setUpgradeData({ currentPlan: plan ?? 'free', limit: limit ?? 3 });
                return;
            }
            Alert.alert('Error', e.response?.data?.message || 'Failed to add app.');
        } finally { setAdding(false); }
    };

    const renderApp = ({ item: app }) => (
        <View style={styles.card}>
            {/* Header row */}
            <View style={styles.cardTop}>
                <View style={styles.favicon}>
                    <Image
                        source={{ uri: app.favicon_url || `https://www.google.com/s2/favicons?domain=${app.url}&sz=64` }}
                        style={styles.faviconImg}
                        defaultSource={require('./fallback-icon.jpg')}
                    />
                </View>
                <View style={styles.appInfo}>
                    <Text style={styles.appName}>{app.name}</Text>
                </View>
                <Switch
                    value={!!app.is_active}
                    onValueChange={() => handleToggle(app)}
                    trackColor={{ false: 'rgba(255,255,255,0.12)', true: COLORS.blue }}
                    thumbColor="#fff"
                />
            </View>

            {/* TOTP row */}
            <View style={[styles.totpRow, !app.is_active && { opacity: 0.5 }]}>
                <View>
                    <Text style={styles.totpLabel}>TOTP CODE</Text>
                    <Text style={styles.totpCode}>
                        {app.is_active ? (app.totp || '──────') : '──────'}
                    </Text>
                </View>
                <View style={styles.totpTimer}>
                    <Text style={styles.totpTimerText}>
                        {app.is_active ? `${app.totpExpiry ?? 30}s` : 'Paused'}
                    </Text>
                    <View style={styles.totpBarBg}>
                        <View
                            style={[
                                styles.totpBar,
                                { width: app.is_active ? `${((app.totpExpiry ?? 30) / 30) * 100}%` : '0%' },
                            ]}
                        />
                    </View>
                </View>
            </View>

            {/* Actions (two primary buttons: Edit Details and Remove) */}
            <View style={styles.cardActions}>
                <TouchableOpacity
                    style={[styles.actionBtn, styles.actionBtnInfo]}
                    onPress={() => openEditModal(app)}
                >
                    <Text style={styles.actionBtnInfoText}>Edit Details</Text>
                </TouchableOpacity>
                <TouchableOpacity
                    style={[styles.actionBtn, styles.actionBtnDanger]}
                    onPress={() => handleRemove(app)}
                >
                    <Text style={styles.actionBtnDangerText}>Remove</Text>
                </TouchableOpacity>
            </View>
        </View>
    );

    return (
        <View style={styles.container}>
            {/* Header */}
            <View style={styles.header}>
                <View>
                    <Text style={styles.headerTitle}>My Apps</Text>
                </View>
                <TouchableOpacity style={styles.addBtn} onPress={() => setModalVisible(true)}>
                    <Text style={styles.addBtnText}>+ Add App</Text>
                </TouchableOpacity>
            </View>

            {loading ? (
                <View style={styles.centered}>
                    <ActivityIndicator color={COLORS.blue} size="large" />
                </View>
            ) : apps.length === 0 ? (
                <View style={styles.empty}>
                    <Text style={styles.emptyTitle}>No integrations yet</Text>
                    <Text style={styles.emptyText}>Tap + Add App to connect your first external application.</Text>
                </View>
            ) : (
                <FlatList
                    data={apps}
                    keyExtractor={a => String(a.id)}
                    renderItem={renderApp}
                    contentContainerStyle={{ padding: 16, paddingBottom: 40 }}
                    refreshControl={
                        <RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); fetchApps(); }} tintColor={COLORS.blue} />
                    }
                />
            )}

            {/* Add App Modal */}
            <Modal visible={modalVisible} transparent animationType="slide" onRequestClose={() => setModalVisible(false)}>
                <TouchableOpacity style={styles.modalBackdrop} activeOpacity={1} onPress={() => setModalVisible(false)}>
                    <TouchableOpacity style={styles.modal} activeOpacity={1} onPress={() => {}}>
                        <Text style={styles.modalTitle}>Add New Integration</Text>
                        <Text style={styles.modalSub}>Paste the app's login URL. You'll get a SecureTS login link and a secret key.</Text>

                        <Text style={styles.fieldLabel}>App Name</Text>
                        <TextInput
                            style={styles.fieldInput}
                            placeholder="e.g. Google Workspace"
                            placeholderTextColor={COLORS.muted}
                            value={appName}
                            onChangeText={setAppName}
                        />
                        <Text style={styles.fieldLabel}>App URL</Text>
                        <TextInput
                            style={styles.fieldInput}
                            placeholder="https://accounts.google.com"
                            placeholderTextColor={COLORS.muted}
                            value={appUrl}
                            onChangeText={setAppUrl}
                            keyboardType="url"
                            autoCapitalize="none"
                        />

                        <View style={styles.modalActions}>
                            <TouchableOpacity style={styles.btnCancel} onPress={() => setModalVisible(false)}>
                                <Text style={styles.btnCancelText}>Cancel</Text>
                            </TouchableOpacity>
                            <TouchableOpacity style={styles.btnPrimary} onPress={handleAdd} disabled={adding}>
                                <Text style={styles.btnPrimaryText}>{adding ? 'Adding...' : 'Add App'}</Text>
                            </TouchableOpacity>
                        </View>
                    </TouchableOpacity>
                </TouchableOpacity>
            </Modal>

            {/* Edit Details Modal */}
            <Modal visible={!!editingApp} transparent animationType="slide" onRequestClose={() => setEditingApp(null)}>
                <TouchableOpacity style={styles.modalBackdrop} activeOpacity={1} onPress={() => setEditingApp(null)}>
                    <TouchableOpacity style={styles.modal} activeOpacity={1} onPress={() => {}}>
                        <Text style={styles.modalTitle}>Edit Details</Text>
                        <Text style={styles.modalSub}>Update the display name for this integration.</Text>

                        <Text style={styles.fieldLabel}>App Name</Text>
                        <TextInput
                            style={styles.fieldInput}
                            placeholder="e.g. Google Workspace"
                            placeholderTextColor={COLORS.muted}
                            value={editAppName}
                            onChangeText={setEditAppName}
                        />
                        <Text style={styles.fieldLabel}>App URL</Text>
                        <TextInput
                            style={[styles.fieldInput, { opacity: 0.6 }]}
                            value={editingApp?.url ?? ''}
                            editable={false}
                            selectTextOnFocus={false}
                        />

                        <View style={styles.modalActions}>
                            <TouchableOpacity style={styles.btnCancel} onPress={() => setEditingApp(null)}>
                                <Text style={styles.btnCancelText}>Cancel</Text>
                            </TouchableOpacity>
                            <TouchableOpacity style={styles.btnPrimary} onPress={handleSaveEdit}>
                                <Text style={styles.btnPrimaryText}>Save</Text>
                            </TouchableOpacity>
                        </View>
                    </TouchableOpacity>
                </TouchableOpacity>
            </Modal>

            {/* Remove App Confirmation Modal */}
            <Modal
                visible={!!appToRemove}
                transparent
                animationType="fade"
                onRequestClose={() => setAppToRemove(null)}
            >
                <TouchableOpacity
                    style={[styles.modalBackdrop, { justifyContent: 'center', padding: 24 }]}
                    activeOpacity={1}
                    onPress={() => setAppToRemove(null)}
                >
                    <TouchableOpacity
                        style={[styles.modal, { borderRadius: 16, paddingBottom: 24 }]}
                        activeOpacity={1}
                        onPress={() => {}}
                    >
                        <Text style={styles.modalTitle}>Remove Integration</Text>
                        <Text style={styles.modalSub}>
                            Are you sure you want to remove "{appToRemove?.name}"? This action cannot be undone.
                        </Text>
                        <View style={styles.modalActions}>
                            <TouchableOpacity style={styles.btnCancel} onPress={() => setAppToRemove(null)}>
                                <Text style={styles.btnCancelText}>Cancel</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={[styles.btnPrimary, { backgroundColor: '#EF4444' }]}
                                onPress={() => performRemove(appToRemove)}
                            >
                                <Text style={styles.btnPrimaryText}>Remove</Text>
                            </TouchableOpacity>
                        </View>
                    </TouchableOpacity>
                </TouchableOpacity>
            </Modal>

            {/* Plan limit reached — upgrade bottom sheet */}
            <UpgradePrompt
                visible={!!upgradeData}
                currentPlan={upgradeData?.currentPlan ?? 'free'}
                limit={upgradeData?.limit ?? 3}
                onDismiss={() => setUpgradeData(null)}
                onViewPlans={() => { setUpgradeData(null); onTabChange?.('subscription'); }}
            />
        </View>
    );
}

const createStyles = (COLORS) => StyleSheet.create({
    container: { flex: 1, backgroundColor: COLORS.bg },
    header: {
        flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
        paddingHorizontal: 20, paddingTop: 56, paddingBottom: 16,
        backgroundColor: COLORS.surface,
        borderBottomWidth: 1, borderBottomColor: COLORS.border,
    },
    headerTitle: { fontSize: 22, fontWeight: '800', color: COLORS.text },
    headerSub:   { fontSize: 13, color: COLORS.muted, marginTop: 2 },
    addBtn: {
        backgroundColor: COLORS.blue, paddingHorizontal: 16, paddingVertical: 9,
        borderRadius: 10,
    },
    addBtnText: { color: '#fff', fontWeight: '700', fontSize: 14 },
    centered: { flex: 1, alignItems: 'center', justifyContent: 'center' },
    empty: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: 40 },
    emptyTitle: { color: COLORS.text, fontSize: 18, fontWeight: '700', marginBottom: 8 },
    emptyText: { color: COLORS.muted, fontSize: 14, textAlign: 'center' },

    card: {
        backgroundColor: COLORS.card, borderRadius: 16,
        borderWidth: 1, borderColor: COLORS.border,
        padding: 16, marginBottom: 14,
    },
    cardTop: { flexDirection: 'row', alignItems: 'center', marginBottom: 12 },
    favicon: {
        width: 44, height: 44, borderRadius: 10,
        backgroundColor: COLORS.subtle,
        alignItems: 'center', justifyContent: 'center', marginRight: 12,
    },
    faviconImg: { width: 30, height: 30 },
    appInfo:   { flex: 1 },
    appName:   { color: COLORS.text, fontSize: 15, fontWeight: '600' },
    appUrl:    { color: COLORS.muted, fontSize: 12, marginTop: 2 },

    totpRow: {
        flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
        backgroundColor: 'rgba(61,90,241,0.08)',
        borderWidth: 1, borderColor: 'rgba(61,90,241,0.2)',
        borderRadius: 10, padding: 12, marginBottom: 12,
    },
    totpLabel:     { color: COLORS.blueLight, fontSize: 10, fontWeight: '700', letterSpacing: 1 },
    totpCode:      { color: COLORS.blueLight, fontSize: 26, fontWeight: '700', letterSpacing: 6, fontVariant: ['tabular-nums'] },
    totpTimer:     { alignItems: 'flex-end' },
    totpTimerText: { color: COLORS.muted, fontSize: 12, marginBottom: 4 },
    totpBarBg:     { width: 48, height: 3, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 2 },
    totpBar:       { height: 3, backgroundColor: COLORS.blue, borderRadius: 2 },

    cardActions: { flexDirection: 'row', gap: 8 },
    actionBtn: { flex: 1, paddingVertical: 9, borderRadius: 8, alignItems: 'center' },
    actionBtnInfo: { backgroundColor: 'rgba(61,90,241,0.12)' },
    actionBtnInfoText: { color: COLORS.blueLight, fontSize: 13, fontWeight: '600' },
    actionBtnDanger: { backgroundColor: 'rgba(239,68,68,0.12)' },
    actionBtnDangerText: { color: '#F87171', fontSize: 13, fontWeight: '600' },

    modalBackdrop: {
        flex: 1, backgroundColor: 'rgba(0,0,0,0.7)',
        justifyContent: 'flex-end',
    },
    modal: {
        backgroundColor: COLORS.card, borderTopLeftRadius: 24, borderTopRightRadius: 24,
        padding: 28, paddingBottom: 48,
    },
    modalTitle: { color: COLORS.text, fontSize: 18, fontWeight: '700', marginBottom: 6 },
    modalSub:   { color: COLORS.muted, fontSize: 13, marginBottom: 24 },
    fieldLabel: { color: COLORS.muted, fontSize: 12, fontWeight: '500', marginBottom: 6 },
    fieldInput: {
        backgroundColor: COLORS.inputBg,
        borderWidth: 1.5, borderColor: COLORS.border,
        borderRadius: 8, padding: 12, fontSize: 14, color: COLORS.text,
        marginBottom: 16,
    },
    modalActions: { flexDirection: 'row', gap: 10, marginTop: 8 },
    btnPrimary: { flex: 1, backgroundColor: COLORS.blue, borderRadius: 10, padding: 14, alignItems: 'center' },
    btnPrimaryText: { color: '#fff', fontWeight: '700', fontSize: 14 },
    btnCancel: { flex: 1, backgroundColor: COLORS.subtle, borderRadius: 10, padding: 14, alignItems: 'center' },
    btnCancelText: { color: COLORS.muted, fontWeight: '600', fontSize: 14 },
});
