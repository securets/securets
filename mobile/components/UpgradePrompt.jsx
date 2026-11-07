import React from 'react';
import {
    View,
    Text,
    TouchableOpacity,
    Modal,
    StyleSheet,
    Pressable,
} from 'react-native';
import { useTheme } from '../src/theme/ThemeContext';

/**
 * UpgradePrompt
 * Bottom-sheet modal shown when adding an integration would exceed the plan limit.
 *
 * Props:
 *   visible      {boolean}   – whether to show the modal
 *   currentPlan  {string}    – human-readable plan name, e.g. "Free"
 *   limit        {number}    – the integration limit of the current plan
 *   onDismiss    {function}  – called when the user taps the backdrop or "Maybe Later"
 *   onViewPlans  {function}  – called when the user taps "View Plans"
 */
export default function UpgradePrompt({ visible, currentPlan, limit, onDismiss, onViewPlans }) {
    const theme = useTheme();
    const s     = styles(theme);

    return (
        <Modal
            visible={visible}
            transparent
            animationType="slide"
            onRequestClose={onDismiss}
            statusBarTranslucent
        >
            <Pressable style={s.backdrop} onPress={onDismiss}>
                <Pressable style={s.sheet} onPress={() => {}}>

                    {/* Drag handle */}
                    <View style={s.handle} />

                    {/* Icon */}
                    <View style={s.iconWrap}>
                        <Text style={s.icon}>🔒</Text>
                    </View>

                    <Text style={s.title}>Plan Limit Reached</Text>

                    <Text style={s.body}>
                        Your <Text style={s.accent}>{currentPlan}</Text> plan allows up to{' '}
                        <Text style={s.accent}>{limit}</Text> integration{limit !== 1 ? 's' : ''}.
                        Upgrade to connect more apps and unlock additional security features.
                    </Text>

                    <TouchableOpacity style={s.btnPrimary} onPress={onViewPlans} activeOpacity={0.85}>
                        <Text style={s.btnPrimaryText}>View Plans →</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={s.btnSecondary} onPress={onDismiss} activeOpacity={0.7}>
                        <Text style={s.btnSecondaryText}>Maybe Later</Text>
                    </TouchableOpacity>

                </Pressable>
            </Pressable>
        </Modal>
    );
}

const styles = (theme) => StyleSheet.create({
    backdrop: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.6)',
        justifyContent: 'flex-end',
    },
    sheet: {
        backgroundColor: theme.surface ?? '#1E293B',
        borderTopLeftRadius: 24,
        borderTopRightRadius: 24,
        padding: 28,
        paddingBottom: 40,
        alignItems: 'center',
        borderTopWidth: 1,
        borderColor: 'rgba(255,255,255,0.08)',
    },
    handle: {
        width: 40,
        height: 4,
        backgroundColor: 'rgba(255,255,255,0.15)',
        borderRadius: 2,
        marginBottom: 24,
    },
    iconWrap: {
        width: 64,
        height: 64,
        borderRadius: 32,
        backgroundColor: 'rgba(61,90,241,0.15)',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 16,
    },
    icon: {
        fontSize: 28,
    },
    title: {
        fontSize: 20,
        fontWeight: '800',
        color: theme.text ?? '#F8FAFC',
        marginBottom: 10,
        textAlign: 'center',
    },
    body: {
        fontSize: 14,
        color: theme.muted ?? '#94A3B8',
        lineHeight: 22,
        textAlign: 'center',
        marginBottom: 28,
    },
    accent: {
        color: '#818CF8',
        fontWeight: '700',
    },
    btnPrimary: {
        width: '100%',
        backgroundColor: '#3D5AF1',
        borderRadius: 12,
        paddingVertical: 14,
        alignItems: 'center',
        marginBottom: 10,
    },
    btnPrimaryText: {
        color: '#fff',
        fontSize: 15,
        fontWeight: '700',
    },
    btnSecondary: {
        width: '100%',
        borderRadius: 12,
        paddingVertical: 14,
        alignItems: 'center',
        backgroundColor: 'rgba(255,255,255,0.06)',
    },
    btnSecondaryText: {
        color: theme.muted ?? '#94A3B8',
        fontSize: 14,
        fontWeight: '600',
    },
});
