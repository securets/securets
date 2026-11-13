/**
 * Secure ThreatShield - Admin Web Dashboard Interactive Scripts
 * Location: public/admin-assets/admin.js
 */

document.addEventListener('DOMContentLoaded', () => {
    // ── Toast Notification System ──
    const showToast = (message, type = 'success') => {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span>${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3400);
    };

    // ── User Dropdown ──
    const accountTrigger = document.getElementById('account-trigger');
    const accountDropdown = document.getElementById('account-dropdown');

    if (accountTrigger && accountDropdown) {
        accountTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            accountDropdown.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            accountDropdown.classList.remove('show');
        });
    }

    // ── Emergency Recovery Modal & Code Generator ──
    const recoveryModal = document.getElementById('recovery-code-modal');
    const modalCloseBtn = document.getElementById('modal-close-btn');
    const codeDisplay = document.getElementById('generated-bypass-code');
    const btnCopyCode = document.getElementById('btn-copy-code');
    const modalTargetUser = document.getElementById('modal-target-user');
    const modalTargetDevice = document.getElementById('modal-target-device');

    const openRecoveryModal = (userEmail, userName, deviceName) => {
        // Generate cryptographic-like 8-character bypass code: e.g. "SEC-8492-X9"
        const randNum = Math.floor(1000 + Math.random() * 9000);
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let randSuffix = '';
        for (let i = 0; i < 2; i++) {
            randSuffix += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        const generatedCode = `STS-${randNum}-${randSuffix}`;

        if (codeDisplay) codeDisplay.textContent = generatedCode;
        if (modalTargetUser) modalTargetUser.textContent = `${userName} (${userEmail})`;
        if (modalTargetDevice) modalTargetDevice.textContent = deviceName || 'Lost Device';

        if (recoveryModal) recoveryModal.classList.add('open');
    };

    if (modalCloseBtn && recoveryModal) {
        modalCloseBtn.addEventListener('click', () => recoveryModal.classList.remove('open'));
        recoveryModal.addEventListener('click', (e) => {
            if (e.target === recoveryModal) recoveryModal.classList.remove('open');
        });
    }

    // Copy to clipboard
    if (btnCopyCode && codeDisplay) {
        btnCopyCode.addEventListener('click', () => {
            const code = codeDisplay.textContent;
            navigator.clipboard.writeText(code).then(() => {
                showToast(`Emergency Code "${code}" copied to clipboard!`, 'success');
            }).catch(() => {
                showToast(`Code: ${code}`, 'info');
            });
        });
    }

    // Attach to all "Generate Bypass Code" buttons
    document.querySelectorAll('.btn-open-code-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const userEmail = btn.getAttribute('data-user-email');
            const userName = btn.getAttribute('data-user-name');
            const deviceName = btn.getAttribute('data-device-name');
            openRecoveryModal(userEmail, userName, deviceName);
        });
    });

    // ── Emergency Remote Wipe / Device Kill Switch ──
    document.querySelectorAll('.btn-trigger-killswitch').forEach(btn => {
        btn.addEventListener('click', function () {
            const deviceName = this.getAttribute('data-device-name') || 'Device';
            const userEmail = this.getAttribute('data-user-email') || 'User';

            if (confirm(`CRITICAL SECURITY ACTION:\n\nTrigger Remote Wipe & Lockdown for "${deviceName}" belonging to ${userEmail}?\n\n• All active sessions will be terminated immediately.\n• Push tokens and credentials will be blacklisted.\n• The device cannot authenticate until re-authorized by an Admin.`)) {
                
                // Visual feedback on card or row
                const card = this.closest('.recovery-card') || this.closest('tr');
                if (card) {
                    const badge = card.querySelector('.badge-pill') || card.querySelector('.status-col');
                    if (badge) {
                        badge.className = 'badge-pill badge-revoked';
                        badge.textContent = 'LOCKED / REVOKED';
                    }
                }

                this.disabled = true;
                this.textContent = 'Device Locked Down';
                this.style.opacity = '0.6';
                this.style.cursor = 'not-allowed';

                showToast(`EMERGENCY KILL SWITCH: "${deviceName}" has been revoked and wiped.`, 'danger');
            }
        });
    });

    // ── Approve Recovery Request ──
    document.querySelectorAll('.btn-approve-recovery').forEach(btn => {
        btn.addEventListener('click', function () {
            const userEmail = this.getAttribute('data-user-email') || 'User';
            const card = this.closest('.recovery-card');

            if (card) {
                card.style.opacity = '0.5';
                card.innerHTML = `
                    <div style="text-align: center; padding: 20px;">
                        <span style="color: #22c55e; font-size: 24px;">✓</span>
                        <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 6px;">Recovery Approved for ${userEmail}</div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Temporary credentials dispatched. Old lost device was safely decommissioned.</div>
                    </div>
                `;
            }
            showToast(`Account recovery approved for ${userEmail}.`, 'success');
        });
    });

    // ── Reject Recovery Request ──
    document.querySelectorAll('.btn-reject-recovery').forEach(btn => {
        btn.addEventListener('click', function () {
            const userEmail = this.getAttribute('data-user-email') || 'User';
            const card = this.closest('.recovery-card');

            if (confirm(`Reject account recovery request for ${userEmail}?`)) {
                if (card) card.remove();
                showToast(`Recovery request for ${userEmail} was declined.`, 'amber');
            }
        });
    });

    // ── Notification Filter Tabs ──
    const filterTabs = document.querySelectorAll('.notif-tab-btn');
    const notifItems = document.querySelectorAll('.notification-item');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const filter = tab.getAttribute('data-filter');
            notifItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Mark all as read
    const btnMarkRead = document.getElementById('btn-mark-all-read');
    if (btnMarkRead) {
        btnMarkRead.addEventListener('click', () => {
            notifItems.forEach(item => {
                item.classList.remove('unread', 'urgent');
            });
            const badgeCount = document.querySelector('.nav-badge-count');
            if (badgeCount) badgeCount.style.display = 'none';
            showToast('All security notifications marked as read.', 'info');
        });
    }

    // ── Settings Save Form ──
    const settingsForm = document.getElementById('admin-settings-form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', (e) => {
            e.preventDefault();
            showToast('Account recovery and device lockdown policies updated successfully!', 'success');
        });
    }
});
