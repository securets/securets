<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class SubscriptionController extends Controller
{
    private function stripe(): StripeClient
    {
        return new StripeClient(config('stripe.secret'));
    }

    // ── Web: Subscription page ────────────────────────────────────────────────

    public function show(Request $request)
    {
        $user         = $request->user();
        $subscription = $user->subscription ?? null;
        $currentPlan  = $user->currentPlan();
        $plans        = config('stripe.plans');

        return view('subscription', compact('user', 'subscription', 'currentPlan', 'plans'));
    }

    public function success(Request $request)
    {
        // Fallback for a missing or slow webhook: confirm the session with Stripe directly.
        $this->verifyAndActivateSession($request->query('session_id'));

        $user        = $request->user();
        $currentPlan = $user->currentPlan();
        $plans       = config('stripe.plans');

        return view('subscription', compact('user', 'currentPlan', 'plans'))
            ->with('flash_success', 'Payment successful! Your plan has been upgraded.');
    }

    public function cancel(Request $request)
    {
        $user        = $request->user();
        $currentPlan = $user->currentPlan();
        $plans       = config('stripe.plans');

        return view('subscription', compact('user', 'currentPlan', 'plans'))
            ->with('flash_error', 'Payment was cancelled. Your current plan is unchanged.');
    }

    // ── Shared: create Stripe Checkout Session (web + mobile) ─────────────────

    public function createCheckoutSession(Request $request)
    {
        $data = $request->validate([
            'plan'         => 'required|in:pro,ultra,ultra_plus',
            'redirect_url' => 'nullable|string|max:2048',
        ]);

        $planKey  = $data['plan'];
        $priceId  = config("stripe.prices.{$planKey}");

        if (empty($priceId)) {
            return response()->json([
                'error' => 'This plan is not yet configured. Please contact support.',
            ], 422);
        }

        $user   = $request->user();
        $stripe = $this->stripe();

        // Retrieve or create a Stripe customer
        $customerId = optional($user->subscription)->stripe_customer_id;

        if (! $customerId) {
            $customer   = $stripe->customers->create([
                'email' => $user->email,
                'name'  => $user->name,
                'metadata' => ['user_id' => $user->id],
            ]);
            $customerId = $customer->id;
        }

        // Determine return URLs
        if ($request->wantsJson() || $request->is('api/*')) {
            // Mobile: bounce through a web page that re-opens the app via deep link.
            $redirect   = $this->safeRedirect($data['redirect_url'] ?? null);
            $successUrl = url('/subscription/mobile-success') . '?session_id={CHECKOUT_SESSION_ID}'
                . ($redirect ? '&redirect=' . urlencode($redirect) : '');
            $cancelUrl  = url('/subscription/mobile-cancel')
                . ($redirect ? '?redirect=' . urlencode($redirect) : '');
        } else {
            $successUrl = route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl  = route('subscription.cancel');
        }

        try {
            $session = $stripe->checkout->sessions->create([
                'customer'   => $customerId,
                'line_items' => [[
                    'price'    => $priceId,
                    'quantity' => 1,
                ]],
                'mode'        => 'subscription',
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,
                'metadata'    => [
                    'user_id' => $user->id,
                    'plan'    => $planKey,
                ],
            ]);

            // Pre-store customer ID so we don't create duplicates on retry
            $user->subscription()->updateOrCreate(
                ['user_id' => $user->id],
                ['stripe_customer_id' => $customerId, 'plan' => $user->currentPlan()],
            );

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['checkout_url' => $session->url]);
            }

            return redirect($session->url);
        } catch (\Throwable $e) {
            Log::error('Stripe checkout error: ' . $e->getMessage());

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Failed to create checkout session.'], 500);
            }

            return back()->with('flash_error', 'Failed to start checkout. Please try again.');
        }
    }

    // ── API: current subscription status for mobile ───────────────────────────

    public function status(Request $request)
    {
        $user = $request->user();
        $plan = $user->currentPlan();
        return response()->json([
            'plan'        => $plan,
            'plan_label'  => config("stripe.plans.{$plan}.label"),
            'price_label' => config("stripe.plans.{$plan}.price_label"),
            'limit'       => $user->planLimit(),
            'status'      => optional($user->subscription)->status ?? 'active',
        ]);
    }

    // ── Mobile: return pages (public — opened by the in-app browser after payment) ──

    public function mobileSuccess(Request $request)
    {
        $activated = $this->verifyAndActivateSession($request->query('session_id'));

        return view('subscription-mobile-return', [
            'success'  => $activated,
            'icon'     => $activated ? '✅' : '↩️',
            'title'    => $activated ? 'Payment successful' : 'Payment received',
            'message'  => $activated
                ? 'Your plan has been upgraded. Return to the app to see it.'
                : 'Return to the app to check your plan.',
            'redirect' => $this->safeRedirect($request->query('redirect')),
        ]);
    }

    public function mobileCancel(Request $request)
    {
        return view('subscription-mobile-return', [
            'success'  => false,
            'icon'     => '↩️',
            'title'    => 'Payment cancelled',
            'message'  => 'Your plan is unchanged. Return to the app to continue.',
            'redirect' => $this->safeRedirect($request->query('redirect')),
        ]);
    }

    // ── Billing: Stripe-hosted customer portal (cancel, update card, invoices) ──

    public function billingPortal(Request $request)
    {
        $user       = $request->user();
        $customerId = optional($user->subscription)->stripe_customer_id;
        $isApi      = $request->wantsJson() || $request->is('api/*');

        if (! $customerId) {
            $message = 'No billing account yet. Please subscribe to a plan first.';

            return $isApi
                ? response()->json(['error' => $message], 422)
                : back()->with('flash_error', $message);
        }

        try {
            $session = $this->stripe()->billingPortal->sessions->create([
                'customer'   => $customerId,
                'return_url' => $isApi ? url('/') : route('subscription'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe billing portal error: ' . $e->getMessage());

            return $isApi
                ? response()->json(['error' => 'Failed to open billing portal.'], 500)
                : back()->with('flash_error', 'Failed to open the billing portal. Please try again.');
        }

        return $isApi
            ? response()->json(['portal_url' => $session->url])
            : redirect($session->url);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Only allow app deep links (Expo Go `exp://` or the `securets://` scheme)
     * as post-checkout redirect targets, to avoid an open-redirect.
     */
    private function safeRedirect(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array($scheme, ['exp', 'securets'], true) ? $url : null;
    }

    /**
     * Ask Stripe whether a Checkout Session really completed, then activate the
     * plan. Used as a fallback when the webhook is missing or delayed.
     */
    private function verifyAndActivateSession(?string $sessionId): bool
    {
        if (! $sessionId) {
            return false;
        }

        try {
            $session = $this->stripe()->checkout->sessions->retrieve($sessionId, []);
        } catch (\Throwable $e) {
            Log::warning('Stripe session verification failed: ' . $e->getMessage());

            return false;
        }

        $data = $session->toArray();

        if (($data['mode'] ?? null) !== 'subscription' || ($data['payment_status'] ?? null) !== 'paid') {
            return false;
        }

        $this->handleCheckoutComplete($data);

        return true;
    }

    // ── Stripe Webhook ────────────────────────────────────────────────────────

    public function webhook(Request $request)
    {
        $payload       = $request->getContent();
        $sigHeader     = $request->header('Stripe-Signature');
        $webhookSecret = config('stripe.webhook_secret');

        // If no webhook secret is set (e.g. during development), skip verification.
        if (! empty($webhookSecret)) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } catch (SignatureVerificationException $e) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        } else {
            $event = json_decode($payload, false);
        }

        $type = is_object($event) ? $event->type : ($event['type'] ?? null);
        $obj  = is_object($event) ? $event->data->object : ($event['data']['object'] ?? []);

        match ($type) {
            'checkout.session.completed'  => $this->handleCheckoutComplete($obj),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($obj),
            'invoice.payment_failed'      => $this->handlePaymentFailed($obj),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function handleCheckoutComplete(mixed $session): void
    {
        $userId  = data_get($session, 'metadata.user_id');
        $planKey = data_get($session, 'metadata.plan');

        if (! $userId || ! $planKey || ! in_array($planKey, Subscription::PLANS, true)) {
            return;
        }

        $stripeSubscriptionId = data_get($session, 'subscription');
        $customerId           = data_get($session, 'customer');
        $priceId              = config("stripe.prices.{$planKey}");

        Subscription::updateOrCreate(
            ['user_id' => $userId],
            [
                'plan'                    => $planKey,
                'stripe_customer_id'      => $customerId,
                'stripe_subscription_id'  => $stripeSubscriptionId,
                'stripe_price_id'         => $priceId,
                'status'                  => 'active',
                'ends_at'                 => null,
            ]
        );
    }

    private function handleSubscriptionDeleted(mixed $subscription): void
    {
        $stripeSubId = data_get($subscription, 'id');
        if (! $stripeSubId) return;

        Subscription::where('stripe_subscription_id', $stripeSubId)
            ->update([
                'plan'    => 'free',
                'status'  => 'canceled',
                'ends_at' => now(),
            ]);
    }

    private function handlePaymentFailed(mixed $invoice): void
    {
        $stripeSubId = data_get($invoice, 'subscription');
        if (! $stripeSubId) return;

        Subscription::where('stripe_subscription_id', $stripeSubId)
            ->update(['status' => 'past_due']);
    }
}
