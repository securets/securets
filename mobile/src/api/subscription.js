import API from './client';

/**
 * Fetch the authenticated user's current subscription status.
 * Returns: { plan, plan_label, price_label, limit, status }
 */
export async function fetchSubscriptionStatus() {
    const response = await API.get('/subscription/status');
    return response.data;
}

/**
 * Create a Stripe Checkout session for the given plan key.
 * @param {'pro'|'ultra'|'ultra_plus'} plan
 * @param {string} [redirectUrl] Deep link the browser returns to after payment.
 * Returns: { checkout_url }
 */
export async function createCheckoutSession(plan, redirectUrl) {
    const response = await API.post('/subscription/checkout', {
        plan,
        redirect_url: redirectUrl,
    });
    return response.data;
}

/**
 * Create a Stripe-hosted billing portal session (cancel / update card / invoices).
 * Returns: { portal_url }
 */
export async function createBillingPortalSession() {
    const response = await API.post('/subscription/portal');
    return response.data;
}
