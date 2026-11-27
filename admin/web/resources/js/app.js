const root = document.documentElement;
const views = [...document.querySelectorAll('[data-view]')];
const navItems = [...document.querySelectorAll('[data-tab]')];
const pageTitle = document.querySelector('#page-title');
const pageDescription = document.querySelector('#page-description');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
const isConsole = Boolean(document.querySelector('.security-console'));

const viewCopy = {
    dashboard: ['Security Overview', 'Monitor users, sessions, recovery requests, subscription sales, and platform health.'],
    users: ['User Management', 'Registered accounts, subscription tiers, and connected applications.'],
    purchases: ['Purchase History', 'Every subscription purchase with its month, tier, and expiration.'],
    sessions: ['Active Sessions', 'Authenticated web sessions and paired mobile instances.'],
    recovery: ['Lost Device Recovery', 'Pending instance token revocations and device security actions.'],
    audit: ['Security Audit Trail', 'Live authentication and administrative verification stream.'],
    status: ['System Status', 'Authentication, database, and real-time transport health.'],
    personalize: ['Theme & Personalization', 'Choose how the admin console looks for your account.'],
};

const escapeHtml = (value = '') => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
}[character]));

const number = (value) => Number(value ?? 0).toLocaleString();

// ── Tabs ─────────────────────────────────────────────────────────────────

function switchTab(tab) {
    if (!viewCopy[tab]) return;
    views.forEach((view) => view.classList.toggle('is-visible', view.id === `view-${tab}`));
    navItems.forEach((item) => item.classList.toggle('is-active', item.dataset.tab === tab));
    pageTitle.textContent = viewCopy[tab][0];
    pageDescription.textContent = viewCopy[tab][1];
    history.replaceState(null, '', tab === 'dashboard' ? window.location.pathname : `#${tab}`);
    document.querySelectorAll(`#view-${tab} [data-server-table]`).forEach((element) => tables.get(element)?.loadOnce());
}

// ── Row renderers for each server table ─────────────────────────────────

function auditBadge(severity) {
    const className = severity === 'warning' || severity === 'critical' ? 'badge-crimson' : severity === 'info' ? 'badge-blue' : 'badge-slate';
    return `<span class="badge ${className}">${escapeHtml(severity === 'warning' ? 'Action Required' : severity)}</span>`;
}

const tierBadge = (key, label) => {
    const className = key === 'free' || !key ? 'badge-slate' : 'badge-blue';
    return `<span class="badge ${className}">${escapeHtml(label ?? '—')}</span>`;
};

const statusBadge = (key, label) => {
    const className = key === 'active' ? 'badge-emerald' : key === 'trialing' ? 'badge-blue' : ['canceled', 'cancelled', 'expired', 'unpaid', 'past_due'].includes(key) ? 'badge-amber' : 'badge-slate';
    return `<span class="badge ${className}">${escapeHtml(label)}</span>`;
};

const renderers = {
    users: {
        empty: (filtered) => `<tr><td colspan="6" class="text-slate-500">${filtered ? 'No users match this filter.' : 'No active users found.'}</td></tr>`,
        row: (user) => `<tr>
            <td><div class="font-medium text-white">${escapeHtml(user.name)}</div><div class="text-xs text-slate-500">${escapeHtml(user.email)}</div></td>
            <td>${tierBadge(user.plan_key, user.plan)}</td>
            <td>${number(user.apps)}</td>
            <td>${number(user.devices)} device${Number(user.devices ?? 0) === 1 ? '' : 's'}</td>
            <td class="text-slate-500">${escapeHtml(user.created_at ?? '—')}</td>
            <td><div class="action-group"><button type="button" data-purchase-history="${escapeHtml(user.email)}">View history</button></div></td>
        </tr>`,
    },
    purchases: {
        empty: (filtered) => `<tr><td colspan="8" class="text-slate-500">${filtered ? 'No purchases match this filter.' : 'No subscription purchases yet.'}</td></tr>`,
        row: (purchase) => `<tr>
            <td><div class="font-medium text-white">${escapeHtml(purchase.user_name || purchase.user_email)}</div><div class="text-xs text-slate-500">${escapeHtml(purchase.user_email)}</div></td>
            <td>${escapeHtml(purchase.month)}</td>
            <td>${tierBadge(purchase.plan_key, purchase.plan)}</td>
            <td class="text-slate-400">${escapeHtml(purchase.price)}</td>
            <td>${statusBadge(purchase.status_key, purchase.status)}</td>
            <td class="text-slate-400">${escapeHtml(purchase.started)}</td>
            <td>${purchase.expiry_state === 'expired' ? `<span class="text-amber">${escapeHtml(purchase.expires)}</span>` : escapeHtml(purchase.expires)}</td>
            <td><div class="action-group"><a class="table-action" href="/admin/purchases/${encodeURIComponent(purchase.id)}/receipt" target="_blank" rel="noopener">Receipt</a></div></td>
        </tr>`,
    },
    sessions: {
        empty: (filtered) => `<tr><td colspan="5" class="text-slate-500">${filtered ? 'No sessions match this filter.' : 'No active sessions in the last 24 hours.'}</td></tr>`,
        row: (session) => `<tr>
            <td>${escapeHtml(session.user)}</td>
            <td class="text-slate-400">${escapeHtml(session.device ?? 'Unknown')}</td>
            <td class="text-slate-400">${escapeHtml(session.client)}</td>
            <td class="font-mono text-xs">${escapeHtml(session.ip)}</td>
            <td class="text-slate-500">${escapeHtml(session.last_seen)}</td>
        </tr>`,
    },
    devices: {
        empty: (filtered) => `<tr><td colspan="4" class="text-slate-500">${filtered ? 'No devices match this filter.' : 'No devices require action.'}</td></tr>`,
        row: (device) => `<tr data-device-row data-device-id="${escapeHtml(device.id)}">
            <td><div class="font-medium text-white">${escapeHtml(device.name)}</div><div class="text-xs text-slate-500">${escapeHtml(device.owner)}</div></td>
            <td><div class="font-mono text-xs">${escapeHtml(device.ip)}</div><div class="text-xs text-slate-500">${escapeHtml(device.location)}</div></td>
            <td><span class="badge ${device.state === 'reported_lost' ? 'badge-amber' : 'badge-crimson'} device-status">${escapeHtml(device.status)}</span></td>
            <td><div class="action-group"><button type="button" data-device-action="remote_lock">Lock</button><button type="button" data-device-action="revoke_tokens">Revoke</button><button type="button" data-device-action="mark_recovered">Recover</button></div></td>
        </tr>`,
        after: (container) => bindDeviceActions(container),
    },
    recovery: {
        empty: (filtered) => `<div class="recovery-card"><strong>${filtered ? 'No devices match this filter' : 'No pending recovery devices'}</strong><span>${filtered ? 'Try another filter or clear it.' : 'All registered devices are active or recovered.'}</span></div>`,
        row: (device) => `<div class="recovery-card">
            <strong>${escapeHtml(device.name)}</strong>
            <span>${escapeHtml(device.owner)}</span>
            <span class="font-mono">${escapeHtml(device.ip)}</span>
            <span>${escapeHtml(device.status)} · ${escapeHtml(device.location)}</span>
            <button type="button" class="primary-button" data-recovery-device="${escapeHtml(device.id)}">Review device</button>
        </div>`,
    },
    audit: {
        empty: (filtered) => `<tr><td colspan="5" class="text-slate-500">${filtered ? 'No audit events match this filter.' : 'No audit events recorded yet.'}</td></tr>`,
        row: (row) => `<tr>
            <td class="font-mono text-xs text-slate-400">${escapeHtml(row.time || '—')}</td>
            <td>${escapeHtml(row.actor)}</td>
            <td>${escapeHtml(row.action)}</td>
            <td class="font-mono text-xs">${escapeHtml(row.target || '—')}</td>
            <td>${auditBadge(row.severity)}</td>
        </tr>`,
    },
};

// ── Server-side table: 20 rows a page, column + value filter, search ─────

class ServerTable {
    constructor(element) {
        this.element = element;
        this.table = element.dataset.serverTable;
        this.renderer = renderers[element.dataset.renderer ?? this.table];
        this.body = element.querySelector('[data-table-body]');
        this.toolbar = element.querySelector('[data-table-toolbar]');
        this.pager = element.querySelector('[data-table-pager]');
        this.state = { page: 1, column: '', value: '', search: '' };
        this.loaded = false;
        this.controller = null;
        this.searchTimer = null;
    }

    get filtered() {
        return Boolean(this.state.search || (this.state.column && this.state.value));
    }

    loadOnce() {
        if (!this.loaded) this.load();
    }

    async load() {
        this.loaded = true;
        this.controller?.abort();
        this.controller = new AbortController();
        this.element.classList.add('is-loading');

        const params = new URLSearchParams({ page: this.state.page });
        if (this.state.column && this.state.value) {
            params.set('column', this.state.column);
            params.set('value', this.state.value);
        }
        if (this.state.search) params.set('search', this.state.search);

        try {
            const response = await fetch(`/admin/api/tables/${this.table}?${params}`, {
                headers: { Accept: 'application/json' },
                signal: this.controller.signal,
            });
            if (response.status === 401 || response.status === 403) {
                window.location.assign('/admin/login');
                return;
            }
            if (!response.ok) throw new Error(`The ${this.table} table could not be loaded.`);
            const data = await response.json();
            this.state.page = data.page;
            if (!this.toolbar.dataset.ready) this.buildToolbar(data.columns);
            this.render(data);
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error(error);
            this.body.innerHTML = this.body.tagName === 'TBODY'
                ? `<tr><td colspan="9" class="text-slate-500">${escapeHtml(error.message)} <button type="button" class="table-action" data-retry>Retry</button></td></tr>`
                : `<div class="recovery-card"><strong>${escapeHtml(error.message)}</strong><button type="button" class="primary-button" data-retry>Retry</button></div>`;
            this.body.querySelector('[data-retry]')?.addEventListener('click', () => this.load());
        } finally {
            this.element.classList.remove('is-loading');
        }
    }

    render(data) {
        this.body.innerHTML = data.rows.length
            ? data.rows.map((row) => this.renderer.row(row)).join('')
            : this.renderer.empty(this.filtered);
        this.renderer.after?.(this.body);
        this.renderPager(data);
        applyGlobalSearch();
    }

    buildToolbar(columns) {
        const filterable = columns.filter((column) => column.filterable);
        this.toolbar.className = 'table-toolbar';
        this.toolbar.dataset.ready = '1';
        this.toolbar.innerHTML = `
            <label class="sr-only" for="${this.id('column')}">Filter by column</label>
            <select id="${this.id('column')}" class="table-select" data-filter-column>
                <option value="">All</option>
                ${filterable.map((column) => `<option value="${escapeHtml(column.key)}">${escapeHtml(column.label)}</option>`).join('')}
            </select>
            <div class="value-picker" data-filter-value hidden>
                <label class="sr-only" for="${this.id('value')}">Filter value</label>
                <button type="button" id="${this.id('value')}" class="table-select value-picker-toggle" aria-haspopup="listbox" aria-expanded="false" data-value-toggle><span data-value-label>All values</span></button>
                <div class="value-picker-panel" data-value-panel hidden>
                    <input type="search" class="table-search value-picker-search" data-value-search placeholder="Search values…" autocomplete="off" aria-label="Search values">
                    <div class="value-picker-list" role="listbox" data-value-list></div>
                </div>
            </div>
            <label class="sr-only" for="${this.id('search')}">Search this table</label>
            <input id="${this.id('search')}" type="search" class="table-search" data-table-search placeholder="Search this table…" autocomplete="off">
            <button type="button" class="table-clear" data-filter-clear hidden>Clear filters</button>`;

        this.columnSelect = this.toolbar.querySelector('[data-filter-column]');
        this.valuePicker = this.toolbar.querySelector('[data-filter-value]');
        this.valueToggle = this.toolbar.querySelector('[data-value-toggle]');
        this.valueLabel = this.toolbar.querySelector('[data-value-label]');
        this.valuePanel = this.toolbar.querySelector('[data-value-panel]');
        this.valueSearch = this.toolbar.querySelector('[data-value-search]');
        this.valueList = this.toolbar.querySelector('[data-value-list]');
        this.searchInput = this.toolbar.querySelector('[data-table-search]');
        this.clearButton = this.toolbar.querySelector('[data-filter-clear]');
        this.values = [];
        this.valueRequest = 0;

        this.columnSelect.value = this.state.column;
        this.columnSelect.addEventListener('change', () => this.changeColumn(this.columnSelect.value));
        this.valueToggle.addEventListener('click', () => this.togglePicker());
        this.valueSearch.addEventListener('input', () => {
            clearTimeout(this.valueTimer);
            // Short lists are filtered in the browser; long ones ask the server.
            if (this.valuesTruncated) this.valueTimer = setTimeout(() => this.fetchValues(this.valueSearch.value.trim()), 250);
            else this.renderValues();
        });
        this.valueList.addEventListener('click', (event) => {
            const option = event.target.closest('[data-value]');
            if (option) this.selectValue(option.dataset.value);
        });
        this.valuePicker.addEventListener('keydown', (event) => this.pickerKeys(event));
        document.addEventListener('click', (event) => {
            if (!this.valuePicker.contains(event.target)) this.togglePicker(false);
        });
        this.searchInput.addEventListener('input', () => {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => {
                this.state.search = this.searchInput.value.trim();
                this.state.page = 1;
                this.syncClear();
                this.load();
            }, 300);
        });
        this.clearButton.addEventListener('click', () => this.reset());

        if (this.state.column) this.loadValues(this.state.column, this.state.value);
        this.syncClear();
    }

    async changeColumn(column, preselect = '') {
        this.state.column = column;
        this.state.value = preselect;
        this.state.page = 1;
        this.syncClear();

        if (!column) {
            this.hidePicker();
            this.load();
            return;
        }

        await this.loadValues(column);
        this.load();
    }

    async loadValues(column) {
        this.valuePicker.hidden = false;
        this.valueColumn = column;
        this.valueSearch.value = '';
        this.selectedLabel = null;
        this.valueLabel.textContent = 'Loading values…';
        this.valueToggle.disabled = true;
        await this.fetchValues('');
        this.valueToggle.disabled = false;
        this.syncValueLabel();
    }

    async fetchValues(query) {
        const request = ++this.valueRequest;
        const params = new URLSearchParams({ column: this.valueColumn });
        if (query) params.set('q', query);

        try {
            const response = await fetch(`/admin/api/tables/${this.table}/values?${params}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Values unavailable');
            const data = await response.json();
            if (request !== this.valueRequest) return;
            this.values = [...data.values];
            this.listTruncated = data.truncated;
            if (!query) {
                this.valuesTruncated = data.truncated;
                const current = this.state.value;
                if (current && !this.values.some((item) => item.value === current)) this.values.unshift({ value: current, label: current, count: null });
            }
            this.renderValues();
        } catch (error) {
            console.error(error);
            this.values = [];
            this.valueList.innerHTML = '<p class="value-picker-note">Values unavailable</p>';
        }
    }

    renderValues() {
        const term = this.valuesTruncated ? '' : this.valueSearch.value.trim().toLowerCase();
        const items = term ? this.values.filter((item) => `${item.label} ${item.value}`.toLowerCase().includes(term)) : this.values;
        const option = (value, label, count) => `<button type="button" role="option" class="value-picker-option" data-value="${escapeHtml(value)}" aria-selected="${value === this.state.value}">
            <span>${escapeHtml(label)}</span>${count !== null ? `<span class="value-picker-count">${number(count)}</span>` : ''}</button>`;

        this.valueList.innerHTML = (term ? '' : option('', 'All values', null))
            + items.map((item) => option(item.value, item.label, item.count)).join('')
            + (items.length ? '' : '<p class="value-picker-note">No matching values</p>')
            + (this.listTruncated ? '<p class="value-picker-note">Showing the first 200. Type to search all.</p>' : '');
    }

    selectValue(value) {
        this.selectedLabel = this.values.find((item) => item.value === value)?.label ?? null;
        this.state.value = value;
        this.state.page = 1;
        this.syncValueLabel();
        this.togglePicker(false);
        this.valueToggle.focus();
        this.syncClear();
        this.load();
    }

    syncValueLabel() {
        this.valueLabel.textContent = this.state.value
            ? (this.selectedLabel ?? this.values.find((item) => item.value === this.state.value)?.label ?? this.state.value)
            : 'All values';
    }

    togglePicker(open = this.valuePanel.hidden) {
        if (!this.valuePanel || this.valuePanel.hidden === !open) return;
        this.valuePanel.hidden = !open;
        this.valueToggle.setAttribute('aria-expanded', String(open));
        if (open) {
            this.renderValues();
            this.valueSearch.focus();
        }
    }

    hidePicker() {
        this.togglePicker(false);
        this.valuePicker.hidden = true;
        this.values = [];
        this.valueRequest++;
    }

    pickerKeys(event) {
        if (event.key === 'Escape' && !this.valuePanel.hidden) {
            event.preventDefault();
            this.togglePicker(false);
            this.valueToggle.focus();
            return;
        }
        if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') return;
        event.preventDefault();
        if (this.valuePanel.hidden) {
            this.togglePicker(true);
            return;
        }
        const options = [...this.valueList.querySelectorAll('[data-value]')];
        const index = options.indexOf(document.activeElement);
        if (event.key === 'ArrowUp' && index <= 0) {
            this.valueSearch.focus();
            return;
        }
        options[event.key === 'ArrowDown' ? Math.min(options.length - 1, index + 1) : index - 1]?.focus();
    }

    /** Open the table already filtered to one column value (e.g. a user's purchases). */
    async filterBy(column, value) {
        this.state.search = '';
        if (this.searchInput) this.searchInput.value = '';
        if (!this.toolbar.dataset.ready) {
            this.state = { page: 1, column, value, search: '' };
            await this.load();
            return;
        }
        this.columnSelect.value = column;
        await this.changeColumn(column, value);
    }

    reset() {
        this.state = { page: 1, column: '', value: '', search: '' };
        this.columnSelect.value = '';
        this.hidePicker();
        this.searchInput.value = '';
        this.syncClear();
        this.load();
    }

    syncClear() {
        if (this.clearButton) this.clearButton.hidden = !this.filtered && !this.state.column;
    }

    renderPager(data) {
        const { page, last_page: lastPage, total, from, to } = data;
        const pages = pageWindow(page, lastPage);
        this.pager.className = 'table-pager';
        this.pager.innerHTML = `
            <span>${total ? `Showing ${number(from)}–${number(to)} of ${number(total)}` : 'No results'}</span>
            <nav class="pager-buttons" aria-label="Pagination">
                <button type="button" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''} aria-label="Previous page">‹ Prev</button>
                ${pages.map((item) => item === '…'
                    ? '<span class="pager-gap">…</span>'
                    : `<button type="button" data-page="${item}" ${item === page ? 'aria-current="page" disabled' : ''}>${item}</button>`).join('')}
                <button type="button" data-page="${page + 1}" ${page >= lastPage ? 'disabled' : ''} aria-label="Next page">Next ›</button>
            </nav>`;
        this.pager.querySelectorAll('[data-page]').forEach((button) => button.addEventListener('click', () => {
            this.state.page = Number(button.dataset.page);
            this.load();
            this.element.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }));
    }

    id(part) {
        if (!this.uid) this.uid = `${this.table}-${Math.random().toString(36).slice(2, 8)}`;
        return `${this.uid}-${part}`;
    }
}

/** Page numbers to show: first, last, and two either side of the current page. */
function pageWindow(current, last) {
    const pages = [];
    for (let page = 1; page <= last; page += 1) {
        if (page === 1 || page === last || Math.abs(page - current) <= 2) pages.push(page);
        else if (pages[pages.length - 1] !== '…') pages.push('…');
    }
    return pages;
}

const tables = new Map();
document.querySelectorAll('[data-server-table]').forEach((element) => tables.set(element, new ServerTable(element)));
const reloadTables = (name) => tables.forEach((table) => { if (table.table === name && table.loaded) table.load(); });

// ── Device actions ───────────────────────────────────────────────────────

function bindDeviceActions(container) {
    container.querySelectorAll('[data-device-action]').forEach((button) => {
        button.addEventListener('click', async () => {
            const row = button.closest('[data-device-row]');
            const buttons = row.querySelectorAll('[data-device-action]');
            buttons.forEach((actionButton) => { actionButton.disabled = true; });
            button.textContent = 'Working…';

            try {
                const response = await fetch(`/admin/api/devices/${row.dataset.deviceId}/action`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ action: button.dataset.deviceAction }),
                });
                if (!response.ok) throw new Error('The device action could not be completed.');
                const result = await response.json();
                const status = row.querySelector('.device-status');
                status.textContent = result.status.replaceAll('_', ' ');
                status.className = 'badge badge-blue device-status';
                button.textContent = 'Done';
                reloadTables('devices');
                reloadTables('audit');
                loadSummary();
            } catch (error) {
                buttons.forEach((actionButton) => { actionButton.disabled = false; });
                button.textContent = 'Retry';
                window.alert(error.message);
            }
        });
    });
}

// ── Purchase history shortcut from User Management ───────────────────────

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-purchase-history]');
    if (!button) return;
    const purchases = tables.get(document.querySelector('#purchases-table'));
    switchTab('purchases');
    purchases?.filterBy('user', button.dataset.purchaseHistory);
});

// ── Summary: KPIs, badges, subscription sales, status ───────────────────

function applyMetrics(metrics = {}) {
    const set = (selector, text) => { const element = document.querySelector(selector); if (element) element.textContent = text; };
    set('#metric-users', number(metrics.users));
    set('#metric-sessions', number(metrics.sessions));
    set('#metric-devices', number(metrics.devices));
    set('#metric-health', metrics.health ?? '—');
    set('#sessions-count', number(metrics.sessions));
    set('#sessions-summary', `${number(metrics.sessions)} active`);
    set('#recovery-count', number(metrics.devices));
    set('#recovery-summary', `${number(metrics.devices)} pending`);
}

function renderSales(sales) {
    const tiersBody = document.querySelector('#sales-tiers');
    if (!sales || !tiersBody) return;

    tiersBody.innerHTML = sales.tiers.map((tier) => `<tr>
        <td><span class="badge badge-slate"><i class="tier-dot" style="background:${escapeHtml(tier.color)}"></i>${escapeHtml(tier.label)}</span></td>
        <td class="text-slate-400">${escapeHtml(tier.price)}</td>
        <td class="num">${number(tier.subscribers)}</td>
        <td><span class="share-bar"><span style="width:${Math.min(100, Number(tier.share))}%;background:${escapeHtml(tier.color)}"></span></span>${Number(tier.share).toFixed(1)}%</td>
        <td class="num text-slate-400">${tier.key === 'free' ? '—' : number(tier.trialing)}</td>
        <td class="num">${escapeHtml(tier.revenue)}</td>
    </tr>`).join('');

    document.querySelector('#sales-total').innerHTML = `<tr>
        <td>Total</td><td></td>
        <td class="num">${number(sales.totals.subscribers)}</td>
        <td class="text-slate-400">${number(sales.totals.paid_subscribers)} paying</td>
        <td></td>
        <td class="num">${escapeHtml(sales.totals.revenue)}</td>
    </tr>`;
    document.querySelector('#sales-mrr').textContent = sales.totals.revenue;

    document.querySelector('#sales-monthly-head').innerHTML = `<tr><th>Month</th>${sales.paid_tiers.map((tier) => `<th class="num">${escapeHtml(tier.label)}</th>`).join('')}<th class="num">New Subscriptions</th><th class="num">Est. Revenue</th></tr>`;
    document.querySelector('#sales-monthly').innerHTML = sales.monthly.map((month) => `<tr>
        <td>${escapeHtml(month.month)}</td>
        ${sales.paid_tiers.map((tier) => `<td class="num">${number(month.tiers[tier.key])}</td>`).join('')}
        <td class="num">${number(month.new)}</td>
        <td class="num">${escapeHtml(month.revenue)}</td>
    </tr>`).join('');
    document.querySelector('#sales-note').textContent = sales.note ?? '';
}

function applyStatus(system = {}, apiOk = true, apiLatency = null) {
    const db = system.database ?? {};
    const set = (selector, text) => { const element = document.querySelector(selector); if (element) element.textContent = text; };
    set('#status-api', apiOk ? 'Operational' : 'Unavailable');
    set('#status-api-meta', apiLatency !== null ? `/admin/api/summary · ${apiLatency} ms` : '/admin/api/summary');
    set('#status-db', db.status ?? 'Unavailable');
    set('#status-db-meta', db.latency_ms != null ? `${String(db.driver ?? '').toUpperCase()} · ${db.latency_ms} ms` : 'No response');
    set('#sidebar-health', apiOk && db.status === 'Operational' ? 'System Operational' : 'System Degraded');
}

async function loadSummary() {
    const startedAt = performance.now();
    try {
        const response = await fetch('/admin/api/summary', { headers: { Accept: 'application/json' } });
        if (response.status === 401 || response.status === 403) {
            window.location.assign('/admin/login');
            return;
        }
        if (!response.ok) throw new Error('Summary unavailable');
        const data = await response.json();
        applyStatus(data.system, true, Math.round(performance.now() - startedAt));
        applyMetrics(data.metrics);
        renderSales(data.subscriptions);
    } catch (error) {
        applyStatus({}, false);
        console.error(error);
    }
}

// ── Theme & personalization ─────────────────────────────────────────────

const preferenceDefaults = { theme: 'dark', accent: 'blue', density: 'comfortable', start_page: 'dashboard' };
const preferencesForm = document.querySelector('#preferences-form');
const preferencesStatus = document.querySelector('#prefs-status');
let savedPreferences = {
    theme: root.dataset.theme, accent: root.dataset.accent, density: root.dataset.density, start_page: root.dataset.startPage,
};

const readPreferences = () => Object.fromEntries(new FormData(preferencesForm).entries());

function applyPreferences(preferences) {
    root.dataset.theme = preferences.theme;
    root.dataset.accent = preferences.accent;
    root.dataset.density = preferences.density;
}

function setPreferencesStatus(text, className = 'badge-slate') {
    if (!preferencesStatus) return;
    preferencesStatus.textContent = text;
    preferencesStatus.className = `badge ${className}`;
}

function fillPreferencesForm(preferences) {
    Object.entries(preferences).forEach(([name, value]) => {
        const field = preferencesForm.elements.namedItem(name);
        if (field) field.value = value;
    });
}

preferencesForm?.addEventListener('change', () => {
    const current = readPreferences();
    applyPreferences(current);
    const changed = Object.keys(savedPreferences).some((key) => savedPreferences[key] !== current[key]);
    setPreferencesStatus(changed ? 'Unsaved changes' : 'Saved', changed ? 'badge-amber' : 'badge-slate');
});

preferencesForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const submit = preferencesForm.querySelector('[type="submit"]');
    submit.disabled = true;
    setPreferencesStatus('Saving…', 'badge-blue');

    try {
        const response = await fetch('/admin/api/preferences', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(readPreferences()),
        });
        if (!response.ok) throw new Error('Preferences could not be saved.');
        const result = await response.json();
        savedPreferences = result.preferences;
        root.dataset.startPage = savedPreferences.start_page;
        applyPreferences(savedPreferences);
        setPreferencesStatus('Saved', 'badge-emerald');
    } catch (error) {
        console.error(error);
        setPreferencesStatus('Not saved — try again', 'badge-crimson');
    } finally {
        submit.disabled = false;
    }
});

document.querySelector('#prefs-reset')?.addEventListener('click', () => {
    fillPreferencesForm(preferenceDefaults);
    preferencesForm.dispatchEvent(new Event('change'));
});

// ── Global search (filters the rows currently shown on screen) ──────────

const search = document.querySelector('#global-search');

function applyGlobalSearch() {
    const query = search?.value.trim().toLowerCase() ?? '';
    document.querySelectorAll('.admin-view.is-visible .data-table tbody tr').forEach((row) => {
        row.hidden = Boolean(query) && !row.textContent.toLowerCase().includes(query);
    });
}

search?.addEventListener('input', applyGlobalSearch);
document.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        search?.focus();
    }
});

// ── Start ────────────────────────────────────────────────────────────────

if (isConsole) {
    navItems.forEach((item) => item.addEventListener('click', () => switchTab(item.dataset.tab)));
    window.addEventListener('hashchange', () => switchTab(window.location.hash.slice(1) || 'dashboard'));

    const hashTab = window.location.hash.slice(1);
    const startTab = viewCopy[hashTab] ? hashTab : (viewCopy[root.dataset.startPage] ? root.dataset.startPage : 'dashboard');
    switchTab(startTab);
    if (startTab !== 'dashboard') document.querySelectorAll('#view-dashboard [data-server-table]').forEach((element) => tables.get(element)?.loadOnce());

    loadSummary();
    setInterval(loadSummary, 60000);
}
