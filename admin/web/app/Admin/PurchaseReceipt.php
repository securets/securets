<?php

namespace App\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use Throwable;

/**
 * Builds the printable proof of purchase for one subscription.
 * Card details are never stored locally; they are read from Stripe on demand
 * and limited to brand, last 4 digits and expiry.
 */
class PurchaseReceipt
{
    public function __construct(private AdminTables $tables) {}

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $row = DB::table('subscriptions as s')
            ->leftJoin('users', 'users.id', '=', 's.user_id')
            ->where('s.id', $id)
            ->first([
                's.id', 's.user_id', 's.plan', 's.status', 's.created_at', 's.ends_at',
                's.stripe_customer_id', 's.stripe_subscription_id', 'users.name', 'users.email',
            ]);

        if (! $row) {
            return null;
        }

        $timezone = (string) config('app.timezone');
        $priceCents = (int) (config("admin.plans.{$row->plan}.price_cents") ?? 0);
        [$card, $cardNote] = $priceCents > 0
            ? $this->card($row)
            : [null, 'No payment method. This is a free plan.'];

        return [
            'number' => 'STS-'.str_pad((string) $row->id, 6, '0', STR_PAD_LEFT),
            'purchased_at' => $row->created_at
                ? Carbon::parse($row->created_at)->timezone($timezone)->format('F j, Y \a\t g:i:s A T')
                : '—',
            'plan' => $this->tables->planLabel($row->plan),
            'price' => $this->tables->money($priceCents).' / month',
            'status' => Str::headline((string) ($row->status ?: 'active')),
            'ends_at' => $row->ends_at ? Carbon::parse($row->ends_at)->timezone($timezone)->format('F j, Y') : 'No end date',
            'buyer' => [
                'name' => $row->name ?: '—',
                'email' => $row->email ?? 'Deleted user',
                'id' => $row->user_id,
            ],
            'card' => $card,
            'card_note' => $cardNote,
            'generated_at' => now()->timezone($timezone)->format('F j, Y \a\t g:i A T'),
        ];
    }

    /**
     * @return array{0: array{brand: string, last4: string, expiry: string}|null, 1: string|null}
     */
    private function card(object $row): array
    {
        $secret = (string) config('admin.stripe_secret');

        if ($secret === '') {
            return [null, 'Card details unavailable: Stripe is not configured for the admin console.'];
        }

        if (! $row->stripe_customer_id) {
            return [null, 'Card details unavailable: this purchase has no Stripe customer.'];
        }

        try {
            $stripe = new StripeClient($secret);
            $method = null;

            if ($row->stripe_subscription_id) {
                $method = $stripe->subscriptions
                    ->retrieve($row->stripe_subscription_id, ['expand' => ['default_payment_method']])
                    ->default_payment_method;
            }

            if (! $method || ! isset($method->card)) {
                $method = $stripe->paymentMethods
                    ->all(['customer' => $row->stripe_customer_id, 'type' => 'card', 'limit' => 1])
                    ->data[0] ?? null;
            }
        } catch (Throwable $e) {
            Log::warning('Receipt card lookup failed.', ['subscription_id' => $row->id, 'error' => $e::class]);

            return [null, 'Card details unavailable: Stripe could not be reached.'];
        }

        if (! $method || ! isset($method->card)) {
            return [null, 'No card on file in Stripe for this purchase.'];
        }

        return [[
            'brand' => Str::headline((string) $method->card->brand),
            'last4' => (string) $method->card->last4,
            'expiry' => sprintf('%02d/%d', $method->card->exp_month, $method->card->exp_year),
        ], null];
    }
}
