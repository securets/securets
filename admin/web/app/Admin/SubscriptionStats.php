<?php

namespace App\Admin;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Subscription sales for the dashboard. Revenue is estimated from each tier's
 * monthly price (config/admin.php "plans"), because the subscriptions table
 * does not store the amount actually paid.
 */
class SubscriptionStats
{
    /** Statuses that count as a live subscription. */
    private const LIVE = ['active', 'trialing'];

    /** Statuses that never became a real subscription. */
    private const NEVER_STARTED = ['incomplete', 'incomplete_expired'];

    public function __construct(private AdminTables $tables) {}

    public function summary(int $months = 6): array
    {
        $now = now();
        $plans = collect(config('admin.plans'));
        $subscriptions = DB::table('subscriptions')
            ->get(['user_id', 'plan', 'status', 'created_at', 'updated_at', 'ends_at'])
            ->map(fn (object $row): object => $this->normalise($row));

        $live = $subscriptions->filter(fn (object $row): bool => $this->isLiveAt($row, $now));

        // Unknown plan keys (not in config) still show up, priced at $0.
        $paidKeys = $plans->keys()->reject(fn (string $key) => $key === 'free')
            ->merge($subscriptions->pluck('plan')->reject(fn ($key) => $key === 'free'))
            ->unique()->values();

        $activeUsers = DB::table('users')->whereNull('archived_at')->count();
        $paidUsers = $live->where('plan', '!=', 'free')->pluck('user_id')->unique()->count();

        $tiers = collect(['free'])->merge($paidKeys)->map(function (string $key) use ($plans, $live, $now, $activeUsers, $paidUsers): array {
            $price = (int) ($plans->get($key)['price_cents'] ?? 0);

            if ($key === 'free') {
                $subscribers = max(0, $activeUsers - $paidUsers);
                $paying = $subscribers;
            } else {
                $tierRows = $live->where('plan', $key);
                $subscribers = $tierRows->pluck('user_id')->unique()->count();
                $paying = $tierRows->reject(fn (object $row) => $this->isTrialAt($row, $now))->pluck('user_id')->unique()->count();
            }

            return [
                'key' => $key,
                'label' => $plans->get($key)['label'] ?? Str::headline($key),
                'color' => $plans->get($key)['color'] ?? '#64748B',
                'price_cents' => $price,
                'price' => $this->tables->money($price).' / month',
                'subscribers' => $subscribers,
                'trialing' => $subscribers - $paying,
                'revenue_cents' => $paying * $price,
                'revenue' => $this->tables->money($paying * $price),
            ];
        });

        $totalSubscribers = max(1, $tiers->sum('subscribers'));
        $tiers = $tiers->map(fn (array $tier): array => $tier + [
            'share' => round($tier['subscribers'] / $totalSubscribers * 100, 1),
        ]);

        $monthly = $this->monthly($subscriptions, $paidKeys, $plans, $months);
        $mrr = $tiers->sum('revenue_cents');

        return [
            'currency' => config('admin.currency', 'USD'),
            'tiers' => $tiers->values()->all(),
            'totals' => [
                'subscribers' => $tiers->sum('subscribers'),
                'paid_subscribers' => $paidUsers,
                'revenue_cents' => $mrr,
                'revenue' => $this->tables->money($mrr),
            ],
            'monthly' => $monthly,
            'paid_tiers' => $paidKeys->map(fn (string $key) => [
                'key' => $key,
                'label' => $plans->get($key)['label'] ?? Str::headline($key),
            ])->values()->all(),
            'note' => 'Revenue is estimated from tier prices. Trials are not counted as revenue.',
        ];
    }

    /**
     * Live paid subscriptions per tier for each of the last N months.
     */
    private function monthly(Collection $subscriptions, Collection $paidKeys, Collection $plans, int $months): array
    {
        $rows = [];

        for ($offset = $months - 1; $offset >= 0; $offset--) {
            $start = now()->startOfMonth()->subMonthsNoOverflow($offset);
            $end = $start->copy()->endOfMonth();
            $checkAt = $end->isFuture() ? now() : $end;
            $tiers = [];
            $revenue = 0;

            foreach ($paidKeys as $key) {
                $inMonth = $subscriptions->where('plan', $key)
                    ->filter(fn (object $row): bool => $this->overlaps($row, $start, $end));
                $paying = $inMonth->reject(fn (object $row) => $this->isTrialAt($row, $checkAt))->count();
                $price = (int) ($plans->get($key)['price_cents'] ?? 0);
                $tiers[$key] = $inMonth->count();
                $revenue += $paying * $price;
            }

            $rows[] = [
                'month' => $start->format('M Y'),
                'tiers' => $tiers,
                'new' => $subscriptions->where('plan', '!=', 'free')
                    ->filter(fn (object $row) => $row->created_at && $row->created_at->between($start, $end))->count(),
                'revenue_cents' => $revenue,
                'revenue' => $this->tables->money($revenue),
            ];
        }

        return array_reverse($rows);
    }

    private function normalise(object $row): object
    {
        $status = strtolower((string) ($row->status ?: 'active'));
        $endsAt = $row->ends_at ? Carbon::parse($row->ends_at) : null;

        // A cancelled subscription without an end date ended when it was last updated.
        if (! $endsAt && in_array($status, ['canceled', 'cancelled', 'ended', 'expired'], true) && $row->updated_at) {
            $endsAt = Carbon::parse($row->updated_at);
        }

        return (object) [
            'user_id' => $row->user_id,
            'plan' => (string) $row->plan,
            'status' => $status,
            'created_at' => $row->created_at ? Carbon::parse($row->created_at) : null,
            'ends_at' => $endsAt,
        ];
    }

    private function isLiveAt(object $row, Carbon $at): bool
    {
        return in_array($row->status, self::LIVE, true)
            && (! $row->ends_at || $row->ends_at->gt($at))
            && (! $row->created_at || $row->created_at->lte($at));
    }

    private function isTrialAt(object $row, Carbon $at): bool
    {
        return $row->status === 'trialing';
    }

    private function overlaps(object $row, Carbon $start, Carbon $end): bool
    {
        if (in_array($row->status, self::NEVER_STARTED, true) || ! $row->created_at) {
            return false;
        }

        return $row->created_at->lte($end) && (! $row->ends_at || $row->ends_at->gte($start));
    }
}
