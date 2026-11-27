<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Receipt {{ $receipt['number'] }} — SecureTS</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-background text-text font-sans antialiased min-h-screen p-6 print:p-0 print:bg-surface">
    <div class="max-w-2xl mx-auto flex flex-col gap-4">
        <div class="flex justify-end print:hidden">
            <button type="button" onclick="window.print()"
                    class="min-h-11 px-4 py-2 rounded-lg bg-primary text-sm font-medium text-surface transition-colors duration-150 ease-out">
                Print or save as PDF
            </button>
        </div>

        <main class="bg-surface border border-border rounded-xl p-6 flex flex-col gap-6 print:border-0">
            <header class="flex items-start justify-between gap-4 pb-6 border-b border-border">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-primary text-surface flex items-center justify-center font-semibold text-sm tracking-tight">TS</div>
                    <div>
                        <h1 class="text-base font-semibold">Proof of purchase</h1>
                        <p class="text-xs text-text-muted">SecureThreatShield</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-text-muted">Receipt no.</p>
                    <p class="text-sm font-medium font-mono tabular-nums">{{ $receipt['number'] }}</p>
                </div>
            </header>

            <section class="flex flex-col gap-3">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-text-muted">Purchase</h2>
                <dl class="grid grid-cols-[10rem_1fr] gap-y-2 text-sm">
                    <dt class="text-text-muted">Date and time</dt><dd>{{ $receipt['purchased_at'] }}</dd>
                    <dt class="text-text-muted">Plan</dt><dd>{{ $receipt['plan'] }}</dd>
                    <dt class="text-text-muted">Price</dt><dd>{{ $receipt['price'] }}</dd>
                    <dt class="text-text-muted">Status</dt><dd>{{ $receipt['status'] }}</dd>
                    <dt class="text-text-muted">Ends</dt><dd>{{ $receipt['ends_at'] }}</dd>
                </dl>
            </section>

            <section class="flex flex-col gap-3 pt-6 border-t border-border">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-text-muted">Purchased by</h2>
                <dl class="grid grid-cols-[10rem_1fr] gap-y-2 text-sm">
                    <dt class="text-text-muted">Name</dt><dd>{{ $receipt['buyer']['name'] }}</dd>
                    <dt class="text-text-muted">Email</dt><dd>{{ $receipt['buyer']['email'] }}</dd>
                    <dt class="text-text-muted">Customer ID</dt><dd class="font-mono tabular-nums">{{ $receipt['buyer']['id'] ?? '—' }}</dd>
                </dl>
            </section>

            <section class="flex flex-col gap-3 pt-6 border-t border-border">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-text-muted">Payment method</h2>
                @if ($receipt['card'])
                    <dl class="grid grid-cols-[10rem_1fr] gap-y-2 text-sm">
                        <dt class="text-text-muted">Card</dt><dd>{{ $receipt['card']['brand'] }}</dd>
                        <dt class="text-text-muted">Card number</dt><dd class="font-mono tabular-nums">•••• •••• •••• {{ $receipt['card']['last4'] }}</dd>
                        <dt class="text-text-muted">Expires</dt><dd class="font-mono tabular-nums">{{ $receipt['card']['expiry'] }}</dd>
                    </dl>
                @else
                    <p class="text-sm text-text-muted">{{ $receipt['card_note'] }}</p>
                @endif
            </section>

            <footer class="pt-6 border-t border-border text-xs text-text-muted">
                Generated {{ $receipt['generated_at'] }}. Prices are the monthly list price of the plan.
            </footer>
        </main>
    </div>
</body>
</html>
