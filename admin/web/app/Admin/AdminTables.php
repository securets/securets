<?php

namespace App\Admin;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Server-side data for every admin table: pagination (20 rows per page),
 * a column + value filter, and a free-text search.
 *
 * Each table is a base query that selects plain, aliased columns. That base
 * query is wrapped as a subquery "t", so filtering, searching, counting and
 * paging all work the same way on every table.
 */
class AdminTables
{
    public const TABLES = ['users', 'sessions', 'devices', 'audit', 'purchases'];

    private const MAX_VALUES = 200;

    /**
     * One page of a table.
     *
     * @param  array{page?: mixed, column?: mixed, value?: mixed, search?: mixed}  $params
     */
    public function page(string $table, array $params): array
    {
        $definition = $this->definition($table);
        $perPage = max(1, (int) config('admin.per_page', 20));
        $columns = $this->columnMeta($definition);

        if ($definition['base'] === null) {
            return $this->emptyPage($table, $columns, $perPage);
        }

        $query = $this->filteredQuery($definition, $params);
        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) ($params['page'] ?? 1)), $lastPage);

        foreach ($definition['order'] as [$column, $direction]) {
            $query->orderByRaw("t.{$column} {$direction}");
        }

        $rows = $query->forPage($page, $perPage)->get()->map($definition['row'])->values()->all();

        return [
            'table' => $table,
            'columns' => $columns,
            'rows' => $rows,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'from' => $total === 0 ? 0 : ($page - 1) * $perPage + 1,
            'to' => min($total, $page * $perPage),
        ];
    }

    /**
     * The distinct values of one column, for the second filter dropdown.
     * A column may define its own `values` source (value + total), e.g. to list
     * users who have no rows yet. `$search` narrows long lists on the server.
     */
    public function values(string $table, string $column, string $search = ''): array
    {
        $definition = $this->definition($table);
        $config = $definition['columns'][$column] ?? null;

        if ($config === null || ($config['field'] ?? null) === null) {
            abort(404, 'Unknown filter column.');
        }

        if ($definition['base'] === null) {
            return ['column' => $column, 'values' => [], 'truncated' => false];
        }

        $source = isset($config['values'])
            ? ($config['values'])()
            : $this->valueCounts($definition, $config['field']);
        $query = DB::query()->fromSub($source, 'v');
        $search = trim($search);

        if ($search !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($search)).'%';
            $query->whereRaw('LOWER(CAST(v.value AS TEXT)) LIKE ?', [$like]);
        }

        $values = $query
            ->orderByRaw('v.value '.($config['values_order'] ?? 'asc'))
            ->limit(self::MAX_VALUES + 1)
            ->get();

        $label = $config['label_for'] ?? fn ($value): string => (string) $value;

        return [
            'column' => $column,
            'values' => $values->take(self::MAX_VALUES)->map(fn (object $row): array => [
                'value' => (string) $row->value,
                'label' => $label($row->value),
                'count' => (int) $row->total,
            ])->values()->all(),
            'truncated' => $values->count() > self::MAX_VALUES,
        ];
    }

    /**
     * Number of rows a table has with no filter or search applied.
     */
    public function count(string $table): int
    {
        $definition = $this->definition($table);

        return $definition['base'] === null ? 0 : $this->filteredQuery($definition, [])->count();
    }

    public static function exists(string $table): bool
    {
        return in_array($table, self::TABLES, true);
    }

    private function filteredQuery(array $definition, array $params): Builder
    {
        $query = DB::query()->fromSub($definition['base'], 't')->select('t.*');

        if ($definition['scope']) {
            ($definition['scope'])($query);
        }

        $column = (string) ($params['column'] ?? '');
        $value = $params['value'] ?? null;
        $field = $definition['columns'][$column]['field'] ?? null;

        if ($field !== null && $value !== null && $value !== '') {
            $query->whereRaw("CAST(t.{$field} AS TEXT) = ?", [(string) $value]);
        }

        $search = trim((string) ($params['search'] ?? ''));

        if ($search !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($search)).'%';
            $fields = collect($definition['columns'])->pluck('search')->flatten()->filter()->unique();

            $query->where(function (Builder $where) use ($fields, $like): void {
                foreach ($fields as $searchField) {
                    $where->orWhereRaw("LOWER(CAST(t.{$searchField} AS TEXT)) LIKE ?", [$like]);
                }
            });
        }

        return $query;
    }

    /** Distinct values of one column of a table's base query, with row counts. */
    private function valueCounts(array $definition, string $field): Builder
    {
        $query = DB::query()->fromSub($definition['base'], 't');

        if ($definition['scope']) {
            ($definition['scope'])($query);
        }

        return $query->selectRaw("t.{$field} as value, count(*) as total")->groupBy("t.{$field}");
    }

    private function columnMeta(array $definition): array
    {
        return collect($definition['columns'])->map(fn (array $column, string $key): array => [
            'key' => $key,
            'label' => $column['label'],
            'filterable' => ($column['field'] ?? null) !== null,
        ])->values()->all();
    }

    private function emptyPage(string $table, array $columns, int $perPage): array
    {
        return [
            'table' => $table, 'columns' => $columns, 'rows' => [], 'page' => 1, 'per_page' => $perPage,
            'total' => 0, 'last_page' => 1, 'from' => 0, 'to' => 0,
        ];
    }

    /**
     * @return array{base: ?Builder, scope: ?callable, columns: array<string, array>, order: array<int, array{0: string, 1: string}>, row: callable}
     */
    private function definition(string $table): array
    {
        return match ($table) {
            'users' => $this->usersTable(),
            'sessions' => $this->sessionsTable(),
            'devices' => $this->devicesTable(),
            'audit' => $this->auditTable(),
            'purchases' => $this->purchasesTable(),
            default => abort(404, 'Unknown table.'),
        };
    }

    // ── Tables ──────────────────────────────────────────────────────────────

    private function usersTable(): array
    {
        $activePlan = DB::table('subscriptions as s')
            ->select('s.plan')
            ->whereColumn('s.user_id', 'users.id')
            ->whereIn('s.status', ['active', 'trialing'])
            ->where(fn (Builder $where) => $where->whereNull('s.ends_at')->orWhere('s.ends_at', '>', now()))
            ->orderByDesc('s.created_at')
            ->limit(1);

        $apps = DB::table('integrations')->selectRaw('count(*)')
            ->whereColumn('integrations.user_id', 'users.id')
            ->where('integrations.is_active', true);

        $devices = DB::table('devices')->selectRaw('count(*)')->whereColumn('devices.user_id', 'users.id');

        $base = DB::table('users')
            ->whereNull('users.archived_at')
            ->select(['users.id', 'users.email', 'users.created_at'])
            ->selectRaw("COALESCE(NULLIF(users.name, ''), 'Unnamed user') as name")
            ->selectRaw('COALESCE(('.$activePlan->toSql()."), 'free') as plan", $activePlan->getBindings())
            ->selectSub($apps, 'apps')
            ->selectSub($devices, 'devices')
            ->selectRaw($this->month('users.created_at').' as joined_month');

        return [
            'base' => $base,
            'scope' => null,
            'columns' => [
                'account' => ['label' => 'Account', 'field' => 'email', 'search' => ['name', 'email']],
                'subscription' => ['label' => 'Subscription', 'field' => 'plan', 'search' => ['plan'], 'label_for' => fn ($plan) => $this->planLabel($plan)],
                'apps' => ['label' => 'Connected Apps', 'field' => 'apps'],
                'devices' => ['label' => 'Paired Devices', 'field' => 'devices'],
                'joined' => ['label' => 'Joined', 'field' => 'joined_month', 'search' => ['created_at'], 'values_order' => 'desc', 'label_for' => fn ($month) => $this->monthLabel($month)],
            ],
            'order' => [['created_at', 'desc'], ['id', 'desc']],
            'row' => fn (object $row): array => [
                'id' => $row->id,
                'name' => $row->name,
                'email' => $row->email,
                'plan_key' => $row->plan,
                'plan' => $this->planLabel($row->plan),
                'apps' => (int) $row->apps,
                'devices' => (int) $row->devices,
                'created_at' => $row->created_at ? Carbon::parse($row->created_at)->toDateTimeString() : null,
            ],
        ];
    }

    private function sessionsTable(): array
    {
        $exists = Schema::hasTable('sessions');
        $agent = 'sessions.user_agent';

        $base = $exists ? DB::table('sessions')
            ->leftJoin('users', 'users.id', '=', 'sessions.user_id')
            ->where('sessions.last_activity', '>=', now()->subDay()->timestamp)
            ->select(['sessions.id', 'sessions.last_activity'])
            ->selectRaw("COALESCE(users.email, 'Guest session') as user_label")
            ->selectRaw("COALESCE(sessions.ip_address, 'Not recorded') as ip")
            ->selectRaw("CASE
                WHEN {$agent} IS NULL THEN 'Unknown'
                WHEN {$agent} LIKE '%Windows%' THEN 'Windows'
                WHEN {$agent} LIKE '%iPhone%' OR {$agent} LIKE '%iPad%' THEN 'iOS'
                WHEN {$agent} LIKE '%Android%' THEN 'Android'
                WHEN {$agent} LIKE '%Mac OS X%' THEN 'macOS'
                WHEN {$agent} LIKE '%Linux%' THEN 'Linux'
                ELSE 'Unknown' END as os")
            ->selectRaw("CASE
                WHEN {$agent} IS NULL THEN 'Unknown client'
                WHEN {$agent} LIKE '%Expo%' OR {$agent} LIKE '%okhttp%' THEN 'Mobile app'
                WHEN {$agent} LIKE '%Edg/%' THEN 'Edge'
                WHEN {$agent} LIKE '%Firefox/%' THEN 'Firefox'
                WHEN {$agent} LIKE '%Chrome/%' THEN 'Chrome'
                WHEN {$agent} LIKE '%Safari/%' THEN 'Safari'
                ELSE 'Other client' END as client") : null;

        return [
            'base' => $base,
            'scope' => null,
            'columns' => [
                'user' => ['label' => 'User', 'field' => 'user_label', 'search' => ['user_label']],
                'device' => ['label' => 'OS / Device', 'field' => 'os', 'search' => ['os']],
                'client' => ['label' => 'Browser / Client', 'field' => 'client', 'search' => ['client']],
                'ip' => ['label' => 'IP Address', 'field' => 'ip', 'search' => ['ip']],
                'last_seen' => ['label' => 'Last Seen', 'field' => null],
            ],
            'order' => [['last_activity', 'desc']],
            'row' => fn (object $row): array => [
                'user' => $row->user_label,
                'device' => $row->os,
                'client' => $row->client,
                'ip' => $row->ip,
                'last_seen' => Carbon::createFromTimestamp((int) $row->last_activity)->diffForHumans(),
            ],
        ];
    }

    /**
     * Devices that need security action (anything not active or recovered).
     */
    private function devicesTable(): array
    {
        $hasStates = Schema::hasTable('admin_device_states');
        $lastIp = DB::table('login_events as le')
            ->select('le.ip_address')
            ->whereColumn('le.user_id', 'devices.user_id')
            ->whereNotNull('le.ip_address')
            ->orderByDesc('le.created_at')
            ->limit(1);

        $base = DB::table('devices')
            ->leftJoin('users', 'users.id', '=', 'devices.user_id')
            ->select(['devices.id', 'devices.last_seen_at'])
            ->selectRaw("COALESCE(NULLIF(devices.device_name, ''), 'Unnamed device') as name")
            ->selectRaw("COALESCE(users.email, 'Unknown user') as owner")
            ->selectRaw('COALESCE(('.$lastIp->toSql()."), 'Not recorded') as ip", $lastIp->getBindings());

        $fallbackState = "CASE WHEN devices.last_seen_at > ? THEN 'active' ELSE 'reported_lost' END";

        if ($hasStates) {
            $base->leftJoin('admin_device_states as ads', 'ads.device_id', '=', 'devices.id')
                ->selectRaw("COALESCE(ads.status, {$fallbackState}) as state", [now()->subDay()]);
        } else {
            $base->selectRaw("{$fallbackState} as state", [now()->subDay()]);
        }

        return [
            'base' => $base,
            'scope' => fn (Builder $query) => $query->whereNotIn('t.state', ['active', 'recovered']),
            'columns' => [
                'device' => ['label' => 'Device', 'field' => 'name', 'search' => ['name']],
                'owner' => ['label' => 'Owner', 'field' => 'owner', 'search' => ['owner']],
                'ip' => ['label' => 'Last Known IP', 'field' => 'ip', 'search' => ['ip']],
                'status' => ['label' => 'Status', 'field' => 'state', 'search' => ['state'], 'label_for' => fn ($state) => Str::headline((string) $state)],
            ],
            'order' => [['last_seen_at', 'desc nulls last'], ['id', 'desc']],
            'row' => function (object $row): array {
                $lastSeen = $row->last_seen_at ? Carbon::parse($row->last_seen_at) : null;

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'owner' => $row->owner,
                    'ip' => $row->ip,
                    'location' => $lastSeen ? 'Last seen '.$lastSeen->diffForHumans() : 'Never seen',
                    'status' => Str::headline((string) $row->state),
                    'state' => $row->state,
                ];
            },
        ];
    }

    /**
     * Admin actions plus Target App login events.
     */
    private function auditTable(): array
    {
        $login = DB::table('login_events')
            ->leftJoin('users', 'users.id', '=', 'login_events.user_id')
            ->leftJoin('integrations', 'integrations.id', '=', 'login_events.integration_id')
            ->select(['login_events.created_at as time'])
            ->selectRaw($this->day('login_events.created_at').' as day')
            ->selectRaw("COALESCE(NULLIF(login_events.credential_username, ''), users.email, 'Unknown user') as actor")
            ->selectRaw("CONCAT('Login ', login_events.status) as action")
            ->selectRaw("COALESCE(integrations.name, 'Unknown Target App') as target")
            ->selectRaw("CASE WHEN login_events.status IN ('declined', 'denied', 'expired', 'blocked') THEN 'warning' ELSE 'info' END as severity");

        $base = $login;

        if (Schema::hasTable('admin_audit_events')) {
            $base = DB::table('admin_audit_events')
                ->select(['admin_audit_events.created_at as time'])
                ->selectRaw($this->day('admin_audit_events.created_at').' as day')
                ->selectRaw("COALESCE(admin_audit_events.actor, 'Admin') as actor")
                ->selectRaw('admin_audit_events.action as action')
                ->selectRaw("COALESCE(admin_audit_events.target, '—') as target")
                ->selectRaw("COALESCE(admin_audit_events.severity, 'info') as severity")
                ->unionAll($login);
        }

        return [
            'base' => $base,
            'scope' => null,
            'columns' => [
                'time' => ['label' => 'Timestamp', 'field' => 'day', 'search' => ['time'], 'values_order' => 'desc', 'label_for' => fn ($day) => $this->dayLabel($day)],
                'actor' => ['label' => 'Actor', 'field' => 'actor', 'search' => ['actor']],
                'action' => ['label' => 'Action', 'field' => 'action', 'search' => ['action'], 'label_for' => fn ($action) => $this->actionLabel($action)],
                'target' => ['label' => 'Target Resource', 'field' => 'target', 'search' => ['target']],
                'severity' => ['label' => 'Severity / Status', 'field' => 'severity', 'search' => ['severity'], 'label_for' => fn ($severity) => $severity === 'warning' ? 'Action Required' : Str::headline((string) $severity)],
            ],
            'order' => [['time', 'desc']],
            'row' => fn (object $row): array => [
                'time' => $row->time ? Carbon::parse($row->time)->toDateTimeString() : null,
                'actor' => $row->actor,
                'action' => $this->actionLabel($row->action),
                'target' => $row->target,
                'severity' => $row->severity,
            ],
        ];
    }

    /**
     * Every subscription purchase: month, tier, status and expiration.
     */
    private function purchasesTable(): array
    {
        $base = DB::table('subscriptions as s')
            ->leftJoin('users', 'users.id', '=', 's.user_id')
            ->select(['s.id', 's.user_id', 's.plan', 's.created_at', 's.ends_at'])
            ->selectRaw("COALESCE(users.email, 'Deleted user') as user_email")
            ->selectRaw("COALESCE(users.name, '') as user_name")
            ->selectRaw($this->month('s.created_at').' as month')
            ->selectRaw("LOWER(COALESCE(s.status, 'active')) as status")
            ->selectRaw("CASE WHEN s.ends_at IS NULL THEN 'none' WHEN s.ends_at > ? THEN 'upcoming' ELSE 'expired' END as expiry_state", [now()]);

        $expiryLabels = ['none' => 'No end date', 'upcoming' => 'Not yet expired', 'expired' => 'Expired'];

        return [
            'base' => $base,
            'scope' => null,
            'columns' => [
                'user' => [
                    'label' => 'User', 'field' => 'user_email', 'search' => ['user_email', 'user_name'],
                    // Every user, including those with no purchases yet (count 0).
                    'values' => fn (): Builder => DB::table('users')
                        ->leftJoin('subscriptions as s', 's.user_id', '=', 'users.id')
                        ->selectRaw('users.email as value, count(s.id) as total')
                        ->groupBy('users.email'),
                ],
                'month' => ['label' => 'Month', 'field' => 'month', 'search' => ['month'], 'values_order' => 'desc', 'label_for' => fn ($month) => $this->monthLabel($month)],
                'tier' => ['label' => 'Tier', 'field' => 'plan', 'search' => ['plan'], 'label_for' => fn ($plan) => $this->planLabel($plan)],
                'status' => ['label' => 'Status', 'field' => 'status', 'search' => ['status'], 'label_for' => fn ($status) => Str::headline((string) $status)],
                'expiration' => ['label' => 'Expiration', 'field' => 'expiry_state', 'search' => ['ends_at'], 'label_for' => fn ($state) => $expiryLabels[$state] ?? (string) $state],
            ],
            'order' => [['created_at', 'desc'], ['id', 'desc']],
            'row' => function (object $row): array {
                $plan = config("admin.plans.{$row->plan}");
                $endsAt = $row->ends_at ? Carbon::parse($row->ends_at) : null;
                $active = in_array($row->status, ['active', 'trialing'], true);

                return [
                    'id' => $row->id,
                    'user_id' => $row->user_id,
                    'user_email' => $row->user_email,
                    'user_name' => $row->user_name,
                    'month' => $this->monthLabel($row->month),
                    'plan_key' => $row->plan,
                    'plan' => $this->planLabel($row->plan),
                    'price' => $plan ? $this->money($plan['price_cents']).' / month' : '—',
                    'status' => Str::headline((string) $row->status),
                    'status_key' => $row->status,
                    'started' => $row->created_at ? Carbon::parse($row->created_at)->format('M j, Y') : '—',
                    'expires' => $endsAt?->format('M j, Y') ?? ($active ? 'No end date (renews monthly)' : 'No end date'),
                    'expiry_state' => $row->expiry_state,
                ];
            },
        ];
    }

    // ── Formatting helpers ──────────────────────────────────────────────────

    public function planLabel(?string $plan): string
    {
        return config('admin.plans.'.($plan ?? 'free').'.label') ?? Str::headline((string) $plan);
    }

    public function money(int $cents): string
    {
        return '$'.number_format($cents / 100, $cents % 100 === 0 ? 0 : 2);
    }

    private function monthLabel($month): string
    {
        return is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month)
            ? Carbon::createFromFormat('!Y-m', $month)->format('M Y')
            : (string) $month;
    }

    private function dayLabel($day): string
    {
        return is_string($day) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)
            ? Carbon::createFromFormat('!Y-m-d', $day)->format('M j, Y')
            : (string) $day;
    }

    private function actionLabel(?string $action): string
    {
        $action = (string) $action;

        return str_starts_with($action, 'Login ') ? 'Login '.Str::headline(substr($action, 6)) : $action;
    }

    private function month(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', {$column})",
            'sqlsrv' => "FORMAT({$column}, 'yyyy-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    private function day(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char({$column}, 'YYYY-MM-DD')",
            'sqlite' => "strftime('%Y-%m-%d', {$column})",
            'sqlsrv' => "FORMAT({$column}, 'yyyy-MM-dd')",
            default => "DATE_FORMAT({$column}, '%Y-%m-%d')",
        };
    }
}
