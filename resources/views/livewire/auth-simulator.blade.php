<div class="w-full">
    <!-- Simulator Mode Switcher -->
    <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold tracking-wide text-emerald-700 bg-emerald-50 rounded-full border border-emerald-200/60 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Interactive Sandbox Prototype
            </div>
            <h3 class="text-lg font-semibold text-slate-900">Experience Dual-Device Authentication</h3>
            <p class="text-sm text-slate-500">Test how the browser and mobile phone securely communicate in real time.</p>
        </div>

        <div class="inline-flex p-1 bg-slate-100/80 rounded-xl border border-slate-200 text-sm font-medium">
            <button 
                wire:click="setTab('signin')" 
                class="px-3.5 py-1.5 rounded-lg transition-all {{ $activeTab === 'signin' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                1. Dual-Device Login
            </button>
            <button 
                wire:click="setTab('signup')" 
                class="px-3.5 py-1.5 rounded-lg transition-all {{ $activeTab === 'signup' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                2. Enclave Pairing
            </button>
            <button 
                wire:click="setTab('phishing')" 
                class="px-3.5 py-1.5 rounded-lg transition-all {{ $activeTab === 'phishing' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                3. Threat Interception
            </button>
        </div>
    </div>

    <!-- Simulator Body Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 pt-8 items-stretch">
        
        {{-- Left: Desktop Web Interface Simulation (Col 7) --}}
        <div class="lg:col-span-7 flex flex-col">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col flex-1">
                <!-- Browser Window Chrome -->
                <div class="bg-slate-50/90 px-4 py-3 border-b border-slate-200 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-rose-400/80 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400/80 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-400/80 inline-block"></span>
                    </div>
                    <div class="flex-1 max-w-sm mx-auto bg-white border border-slate-200/80 rounded-lg px-3 py-1 text-xs text-slate-600 font-mono flex items-center gap-2 justify-center shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span class="truncate">https://app.securets.io/session/auth</span>
                    </div>
                    <div class="text-[11px] font-mono text-slate-400">DESKTOP</div>
                </div>

                <!-- Desktop Screen Content -->
                <div class="p-6 md:p-8 flex-1 flex flex-col justify-center">
                    @if ($activeTab === 'signin')
                        {{-- SIGN IN FLOW --}}
                        @if ($step === 'idle')
                            <div class="max-w-md mx-auto w-full space-y-5">
                                <div class="text-center space-y-1">
                                    <div class="w-10 h-10 mx-auto rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-base shadow-sm">
                                        S
                                    </div>
                                    <h4 class="text-lg font-bold text-slate-900 pt-2">Sign in to Enterprise Vault</h4>
                                    <p class="text-xs text-slate-500">Passwordless sign-in with phone confirmation</p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Account Identifier / Email</label>
                                        <input 
                                            type="email" 
                                            wire:model="email" 
                                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition"
                                            placeholder="you@company.com"
                                        />
                                    </div>

                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs text-slate-600">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span>Paired Hardware: <strong>iPhone 16 Pro</strong></span>
                                        </div>
                                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                    </div>

                                    <button 
                                        wire:click="initiateSignIn" 
                                        class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                                        <span>Send Confirmation Request</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                        @elseif ($step === 'awaiting_approval')
                            <div class="max-w-md mx-auto w-full text-center space-y-5 py-4">
                                <div class="relative w-14 h-14 mx-auto flex items-center justify-center">
                                    <div class="absolute inset-0 rounded-full border-2 border-slate-200"></div>
                                    <div class="absolute inset-0 rounded-full border-2 border-indigo-600 border-t-transparent animate-spin"></div>
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>

                                <div class="space-y-1">
                                    <h4 class="text-base font-bold text-slate-900">Awaiting Phone Confirmation</h4>
                                    <p class="text-xs text-slate-500">Check your phone on the right to approve this sign-in request.</p>
                                </div>

                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 text-left">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-500">Verification Match Code:</span>
                                        <span class="font-mono font-bold text-indigo-600 text-sm bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">{{ $challengeCode }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>Channel:</span>
                                        <span class="font-mono text-slate-700">Encrypted Out-of-Band Push</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>Target Device:</span>
                                        <span class="text-slate-700">Alex's iPhone (Enclave Verified)</span>
                                    </div>
                                </div>

                                <button wire:click="resetFlows" class="text-xs text-slate-400 hover:text-slate-700 underline underline-offset-4">
                                    Cancel request
                                </button>
                            </div>

                        @elseif ($step === 'approved')
                            <div class="max-w-md mx-auto w-full text-center space-y-4 py-4">
                                <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center border border-emerald-200">
                                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>

                                <div class="space-y-1">
                                    <h4 class="text-lg font-bold text-slate-900">Dual-Device Authentication Verified</h4>
                                    <p class="text-xs text-slate-500">Hardware token signed and verified in {{ $authLatencyMs }}ms.</p>
                                </div>

                                <div class="bg-emerald-50/80 border border-emerald-200 rounded-xl p-4 text-left text-xs font-mono text-emerald-900 space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-emerald-700">Session Status:</span>
                                        <span class="font-bold">200 OK • TOKEN_ISSUED</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-emerald-700">Attestation Method:</span>
                                        <span>Apple Secure Enclave (P-256)</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-emerald-700">Account:</span>
                                        <span class="truncate">{{ $email }}</span>
                                    </div>
                                </div>

                                <button wire:click="resetFlows" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition cursor-pointer">
                                    Test Another Login
                                </button>
                            </div>

                        @elseif ($step === 'rejected')
                            <div class="max-w-md mx-auto w-full text-center space-y-4 py-4">
                                <div class="w-14 h-14 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center border border-rose-200">
                                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>

                                <div class="space-y-1">
                                    <h4 class="text-lg font-bold text-slate-900">Sign-in Request Denied</h4>
                                    <p class="text-xs text-slate-500">The mobile confirmation was declined by the user.</p>
                                </div>

                                <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 text-xs text-rose-800 text-left">
                                    <strong>Zero Trust Guard:</strong> This session was rejected out-of-band. No credentials or session cookies were compromised.
                                </div>

                                <button wire:click="resetFlows" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition cursor-pointer">
                                    Try Again
                                </button>
                            </div>
                        @endif

                    @elseif ($activeTab === 'signup')
                        {{-- SIGN UP / PAIRING FLOW --}}
                        <div class="max-w-md mx-auto w-full text-center space-y-5">
                            @if ($pairingStep === 'qr')
                                <div class="space-y-1">
                                    <h4 class="text-base font-bold text-slate-900">Pair New Mobile Confirmation Device</h4>
                                    <p class="text-xs text-slate-500">Scan this cryptographically signed QR code from your mobile device.</p>
                                </div>

                                <!-- QR Mockup -->
                                <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl inline-block shadow-2xs">
                                    <div class="w-36 h-36 bg-white p-2 border border-slate-200 rounded-xl flex items-center justify-center relative group">
                                        <svg class="w-full h-full text-slate-900" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14 0h2v2h-2v-2zm-4 0h2v4h-2v-4zm4 4h4v2h-4v-2zm0-2h2v2h-2v-2zm-4 4h2v2h-2v-2zm2 0h2v2h-2v-2zm4-6h2v2h-2v-2zm-6-2h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
                                        </svg>
                                        <div class="absolute inset-0 bg-slate-900/5 backdrop-blur-2xs rounded-xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                            <span class="text-[10px] font-mono font-bold bg-white px-2 py-1 rounded shadow">ECDSA P-256</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-xs font-mono font-semibold text-slate-600">
                                        Nonce: <span class="text-indigo-600">{{ $pairingNonce }}</span>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-500">Click <strong>"Scan & Bind Enclave"</strong> on the phone mockup to complete pairing.</p>

                            @else
                                <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center border border-emerald-200">
                                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-lg font-bold text-slate-900">Device Hardware Enclave Linked</h4>
                                    <p class="text-xs text-slate-500">Public key registered. This phone will now receive all login confirmations.</p>
                                </div>
                                <button wire:click="resetFlows" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition cursor-pointer">
                                    Reset Simulator
                                </button>
                            @endif
                        </div>

                    @elseif ($activeTab === 'phishing')
                        {{-- PHISHING INTERCEPTION --}}
                        <div class="max-w-md mx-auto w-full space-y-4">
                            @if ($threatStep === 'idle')
                                <div class="space-y-1 text-center">
                                    <div class="w-10 h-10 mx-auto rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-900">Simulate Phishing / Stolen Credential Attack</h4>
                                    <p class="text-xs text-slate-500">See what happens when an adversary tries to log into your account from an unknown proxy.</p>
                                </div>

                                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-xs">
                                    <div class="flex justify-between text-slate-600">
                                        <span>Target Email:</span>
                                        <span class="font-semibold text-slate-900">alex.chen@enterprise.dev</span>
                                    </div>
                                    <div class="flex justify-between text-slate-600">
                                        <span>Attacker Location:</span>
                                        <span class="font-mono text-rose-600">{{ $threatLocation }}</span>
                                    </div>
                                    <div class="flex justify-between text-slate-600">
                                        <span>Attacker IP:</span>
                                        <span class="font-mono text-slate-700">{{ $threatIp }}</span>
                                    </div>
                                </div>

                                <button 
                                    wire:click="triggerPhishingAttack" 
                                    class="w-full py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                                    <span>Trigger Rogue Sign-In Attempt</span>
                                </button>

                            @elseif ($threatStep === 'alerting')
                                <div class="text-center space-y-4 py-3">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-amber-100 text-amber-600 flex items-center justify-center animate-bounce">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="text-base font-bold text-slate-900">Adversary Blocked at Gateway</h4>
                                        <p class="text-xs text-slate-500">The adversary cannot proceed because they lack your physical phone. Look at your phone mockup on the right!</p>
                                    </div>
                                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 font-medium">
                                        Pending authorization check. User notified instantly via push.
                                    </div>
                                </div>

                            @elseif ($threatStep === 'blocked')
                                <div class="text-center space-y-4 py-3">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-900 text-emerald-400 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="text-base font-bold text-slate-900">Attack Completely Neutralized</h4>
                                        <p class="text-xs text-slate-500">Adversary IP rate-limited and session quarantined automatically.</p>
                                    </div>
                                    <button wire:click="resetFlows" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold cursor-pointer">
                                        Reset Simulation
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Footer Status Bar -->
                <div class="bg-slate-50 px-6 py-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-mono">
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        SecureTS Engine: ONLINE
                    </span>
                    <span>v1.0.4-livewire</span>
                </div>
            </div>
        </div>

        {{-- Right: Mobile Phone Device Simulation (Col 5) --}}
        <div class="lg:col-span-5 flex flex-col items-center justify-center">
            <div class="w-full max-w-[310px] bg-slate-900 p-3 rounded-[38px] shadow-xl border-4 border-slate-800 relative">
                <!-- Phone Top Speaker / Camera Notch -->
                <div class="absolute top-5 left-1/2 -translate-x-1/2 w-24 h-4 bg-black rounded-full z-20 flex items-center justify-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-slate-800"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-900 border border-slate-800"></span>
                </div>

                <!-- Phone Inner Screen -->
                <div class="bg-slate-950 text-slate-100 rounded-[28px] p-4 pt-10 min-h-[460px] flex flex-col justify-between overflow-hidden relative border border-slate-800">
                    <!-- Status bar -->
                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium px-1">
                        <span>9:41</span>
                        <div class="flex items-center gap-1">
                            <span class="text-[9px]">5G</span>
                            <div class="w-4 h-2 border border-slate-400 rounded-2xs p-0.5 flex items-center">
                                <div class="w-full h-full bg-slate-400 rounded-3xs"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Phone Content Area based on Simulator State -->
                    <div class="flex-1 flex flex-col justify-center py-4">
                        @if ($activeTab === 'signin')
                            @if ($step === 'idle')
                                <div class="text-center space-y-3 py-6">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-xs font-semibold text-slate-300">Phone Ready</p>
                                        <p class="text-[11px] text-slate-500 px-3">Standing by for incoming desktop authorization requests.</p>
                                    </div>
                                </div>

                            @elseif ($step === 'awaiting_approval')
                                <!-- Real-time Push Notification Card on Phone -->
                                <div class="bg-slate-900/90 border border-indigo-500/40 rounded-2xl p-4 shadow-lg space-y-3 animate-in fade-in zoom-in-95 duration-200">
                                    <div class="flex items-center gap-2 border-b border-slate-800/80 pb-2">
                                        <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                                            S
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-[11px] font-bold text-white truncate">SecureTS Auth Request</div>
                                            <div class="text-[9px] text-slate-400">Just now • Dual-Device Handshake</div>
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-[11px]">
                                        <p class="text-slate-300">Sign-in attempt for <strong>{{ $email }}</strong> from:</p>
                                        <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800 text-[10px] space-y-1 text-slate-400 font-mono">
                                            <div class="text-slate-200">🖥️ {{ $clientLocation }}</div>
                                            <div>IP: {{ $clientIp }}</div>
                                            <div class="pt-1 flex items-center justify-between text-indigo-400 font-bold">
                                                <span>Match Code:</span>
                                                <span class="text-xs bg-indigo-950/80 px-1.5 py-0.5 rounded border border-indigo-800">{{ $challengeCode }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Phone Actions -->
                                    <div class="grid grid-cols-2 gap-2 pt-1">
                                        <button 
                                            wire:click="rejectOnPhone" 
                                            class="py-2 px-2 bg-slate-800 hover:bg-slate-700 text-rose-400 rounded-xl text-xs font-semibold transition border border-rose-500/20 cursor-pointer">
                                            Deny
                                        </button>
                                        <button 
                                            wire:click="approveOnPhone" 
                                            class="py-2 px-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Approve (FaceID)</span>
                                        </button>
                                    </div>
                                </div>

                            @elseif ($step === 'approved')
                                <div class="text-center space-y-3 py-6">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-emerald-400">Confirmation Sent</p>
                                    <p class="text-[11px] text-slate-400">Biometric signature validated and delivered to web terminal.</p>
                                </div>

                            @elseif ($step === 'rejected')
                                <div class="text-center space-y-3 py-6">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/40 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-rose-400">Request Denied</p>
                                    <p class="text-[11px] text-slate-400">The login attempt on desktop was immediately rejected.</p>
                                </div>
                            @endif

                        @elseif ($activeTab === 'signup')
                            @if ($pairingStep === 'qr')
                                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 text-center space-y-3">
                                    <div class="w-10 h-10 mx-auto rounded-xl bg-slate-800 text-indigo-400 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-white">Device Registration</div>
                                        <div class="text-[11px] text-slate-400">Point mobile camera at the desktop QR code to bind hardware enclave.</div>
                                    </div>
                                    <button 
                                        wire:click="scanPairingQr" 
                                        class="w-full py-2.5 px-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition cursor-pointer">
                                        Scan & Bind Enclave Key
                                    </button>
                                </div>
                            @else
                                <div class="text-center space-y-3 py-6">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-emerald-400">Secure Enclave Registered</p>
                                    <p class="text-[11px] text-slate-400">Your iPhone is now the primary confirmation device.</p>
                                </div>
                            @endif

                        @elseif ($activeTab === 'phishing')
                            @if ($threatStep === 'idle')
                                <div class="text-center space-y-3 py-6">
                                    <div class="w-10 h-10 mx-auto rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <p class="text-xs text-slate-400">Zero-Trust Shield Active</p>
                                    <p class="text-[11px] text-slate-500">Trigger the attack on the left to test threat interception.</p>
                                </div>
                            @elseif ($threatStep === 'alerting')
                                <div class="bg-rose-950/70 border-2 border-rose-500 rounded-2xl p-4 text-center space-y-3 animate-pulse">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-rose-500 text-white flex items-center justify-center font-bold">
                                        !
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-rose-200">SUSPICIOUS SIGN-IN</div>
                                        <div class="text-[10px] text-rose-300">Unauthorized login detected from {{ $threatLocation }}</div>
                                    </div>
                                    <button 
                                        wire:click="blockThreatOnPhone" 
                                        class="w-full py-2.5 px-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow">
                                        Block & Quarantine IP
                                    </button>
                                </div>
                            @elseif ($threatStep === 'blocked')
                                <div class="text-center space-y-3 py-6">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-emerald-400">Threat Neutralized</p>
                                    <p class="text-[11px] text-slate-400">Adversary rejected. No compromised credentials.</p>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Phone Bottom Home Bar Indicator -->
                    <div class="w-24 h-1 bg-slate-700 rounded-full mx-auto"></div>
                </div>
            </div>
        </div>

    </div>
</div>
