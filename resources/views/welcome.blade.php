<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SecureTS — Zero-Trust Dual-Device Authentication Protocol</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    
    @fonts

    <!-- Vite Styles and Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased selection:bg-indigo-100 selection:text-indigo-900 min-h-screen flex flex-col justify-between">

    <!-- Top Announcement Bar -->
    <div class="bg-slate-900 text-slate-200 text-xs py-2 px-4 text-center border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span><strong>SecureTS Protocol v1.0 Developer Preview</strong> is now live for Laravel & Web applications.</span>
            <a href="#waitlist" class="underline underline-offset-2 hover:text-white font-medium ml-1">Get Early Access &rarr;</a>
        </div>
    </div>

    <!-- Header / Navigation -->
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-sm shadow-xs group-hover:bg-indigo-600 transition">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-base font-bold tracking-tight text-slate-900">Secure<span class="text-indigo-600">TS</span></span>
                    <span class="text-[10px] font-mono -mt-1 text-slate-400 uppercase tracking-widest">Dual-Device Auth</span>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                <a href="#demo" class="hover:text-slate-900 transition">Live Demo</a>
                <a href="#protocol" class="hover:text-slate-900 transition">Protocol</a>
                <a href="#features" class="hover:text-slate-900 transition">Features</a>
                <a href="#comparison" class="hover:text-slate-900 transition">Security Matrix</a>
                <a href="#code" class="hover:text-slate-900 transition">Developer SDK</a>
                <a href="#faq" class="hover:text-slate-900 transition">FAQ</a>
            </nav>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <a href="#demo" class="hidden sm:inline-flex px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200/80 rounded-lg transition">
                    Try Simulation
                </a>
                <a href="#waitlist" class="px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition shadow-xs">
                    Get Early Access
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        
        <!-- HERO SECTION -->
        <section class="relative pt-16 pb-20 md:pt-24 md:pb-28 overflow-hidden">
            <!-- Subtle background grid pattern -->
            <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:24px_24px] opacity-40 -z-10 pointer-events-none"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-semibold text-slate-800 bg-white border border-slate-200/80 shadow-2xs">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                    </span>
                    Zero-Trust Dual-Device Sign-In Protocol
                </div>

                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight max-w-4xl mx-auto leading-[1.15]">
                    Initiate on Web. <br class="hidden sm:inline" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-slate-900">
                        Confirmed on Phone.
                    </span> 
                    Zero Passwords.
                </h1>

                <!-- Subtitle -->
                <p class="text-lg sm:text-xl text-slate-600 max-w-2xl mx-auto leading-relaxed font-normal">
                    Eliminate phishing, credential stuffing, and SMS fatigue. Every desktop login is verified out-of-band via biometric tap on your physical phone in milliseconds.
                </p>

                <!-- Hero CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                    <a href="#demo" class="w-full sm:w-auto px-7 py-3.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm rounded-xl transition shadow-sm hover:shadow flex items-center justify-center gap-2 group">
                        <span>Try Interactive Sandbox</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </a>
                    <a href="#waitlist" class="w-full sm:w-auto px-7 py-3.5 bg-white hover:bg-slate-50 text-slate-900 font-semibold text-sm rounded-xl border border-slate-200 transition shadow-2xs flex items-center justify-center gap-2">
                        <span>Request Developer SDK</span>
                    </a>
                </div>

                <!-- Key Metrics Strip -->
                <div class="pt-10 max-w-4xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-white/70 backdrop-blur rounded-2xl border border-slate-200/80 text-center shadow-2xs">
                        <div class="text-2xl font-bold text-slate-900 tracking-tight">&lt; 450ms</div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Auth Latency</div>
                    </div>
                    <div class="p-4 bg-white/70 backdrop-blur rounded-2xl border border-slate-200/80 text-center shadow-2xs">
                        <div class="text-2xl font-bold text-slate-900 tracking-tight">0 Passwords</div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Zero DB Credentials</div>
                    </div>
                    <div class="p-4 bg-white/70 backdrop-blur rounded-2xl border border-slate-200/80 text-center shadow-2xs">
                        <div class="text-2xl font-bold text-indigo-600 tracking-tight">100%</div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Phishing Immune</div>
                    </div>
                    <div class="p-4 bg-white/70 backdrop-blur rounded-2xl border border-slate-200/80 text-center shadow-2xs">
                        <div class="text-2xl font-bold text-emerald-600 tracking-tight">ECDSA</div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Hardware Enclave</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- INTERACTIVE LIVEWIRE SIMULATION SHOWCASE SECTION -->
        <section id="demo" class="py-16 md:py-24 bg-white border-y border-slate-200/80 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center space-y-3 max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">Real-Time Simulation</span>
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Test the Dual-Device Protocol Live</h2>
                    <p class="text-sm text-slate-600">
                        Interact with both the desktop browser and the mobile phone below to see how easy and secure out-of-band confirmation is.
                    </p>
                </div>

                <!-- Livewire Auth Simulator Component -->
                <div class="p-6 md:p-8 bg-slate-50/80 rounded-3xl border border-slate-200 shadow-sm">
                    <livewire:auth-simulator />
                </div>

            </div>
        </section>

        <!-- HOW THE PROTOCOL WORKS SECTION (3-Step Flow) -->
        <section id="protocol" class="py-20 md:py-28 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">Architecture & Handshake</span>
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight">How the Dual-Device Protocol Operates</h2>
                    <p class="text-sm text-slate-600">
                        Cryptographically binds user sessions to verified physical hardware with no human-readable passwords.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                    <!-- Step 1 -->
                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-2xs hover:shadow-sm transition flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-base">
                                01
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">1. Desktop Initiates Session</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                The user enters their email or scans a session QR on the web browser. The server generates a single-use ephemeral cryptographic challenge nonce.
                            </p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-[11px] font-mono text-slate-600">
                            <code>POST /auth/challenge &bull; Nonce Issued</code>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-2xs hover:shadow-sm transition flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-base">
                                02
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">2. Out-of-Band Push Notification</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                An encrypted push payload is dispatched directly to the user's paired mobile device, including origin domain, IP, geolocation, and verification match code.
                            </p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-[11px] font-mono text-slate-600">
                            <code>APNs / FCM &bull; Encrypted Payload</code>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-2xs hover:shadow-sm transition flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-base">
                                03
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">3. Hardware Biometric Attestation</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                User taps Face ID or Touch ID. The phone's Secure Enclave signs the challenge using the private hardware key. The web session unlocks instantly.
                            </p>
                        </div>
                        <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 text-[11px] font-mono text-emerald-800">
                            <code>ECDSA Signature &bull; 200 OK Token</code>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- FEATURES & CAPABILITIES GRID (6 Core Pillars) -->
        <section id="features" class="py-20 md:py-28 bg-white border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">Enterprise Grade Security</span>
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Engineered for Uncompromising Defense</h2>
                    <p class="text-sm text-slate-600">
                        Built for security-conscious teams requiring frictionless user logins and impenetrable account security.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    
                    <!-- Feature 1 -->
                    <div class="p-6 bg-slate-50/60 rounded-3xl border border-slate-200/80 space-y-3 hover:border-slate-300 transition">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-indigo-600 flex items-center justify-center shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Phishing Immunity</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Fake clone websites cannot intercept logins because confirmations are cryptographically signed to the genuine origin server.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-6 bg-slate-50/60 rounded-3xl border border-slate-200/80 space-y-3 hover:border-slate-300 transition">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-emerald-600 flex items-center justify-center shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Hardware Secure Enclave</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Private keys never leave the phone's hardware security chip (Apple Secure Enclave / Android Keymaster/TEE).
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-6 bg-slate-50/60 rounded-3xl border border-slate-200/80 space-y-3 hover:border-slate-300 transition">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-indigo-600 flex items-center justify-center shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">10-Second QR Onboarding</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Pairing a new phone takes a single camera scan. No complicated 16-character backup codes or SMS delivery delays.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-6 bg-slate-50/60 rounded-3xl border border-slate-200/80 space-y-3 hover:border-slate-300 transition">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-rose-600 flex items-center justify-center shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Zero Password Vulnerability</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            No plaintext, hashed, or salted passwords stored in your database. SQL injections yield zero credential leaks.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="p-6 bg-slate-50/60 rounded-3xl border border-slate-200/80 space-y-3 hover:border-slate-300 transition">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-amber-600 flex items-center justify-center shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Real-Time Threat Alerts</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Users get instantaneous rich notifications showing browser type, IP, and location before tapping to confirm.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="p-6 bg-slate-50/60 rounded-3xl border border-slate-200/80 space-y-3 hover:border-slate-300 transition">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-900 flex items-center justify-center shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Blade & Livewire Ready</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Pre-built components and middleware for Laravel. Drop a single Blade tag into your auth views to activate.
                        </p>
                    </div>

                </div>

            </div>
        </section>

        <!-- PROTOCOL COMPARISON MATRIX -->
        <section id="comparison" class="py-20 md:py-28 bg-slate-50 border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">Security Benchmark</span>
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight">How SecureTS Compares</h2>
                    <p class="text-sm text-slate-600">
                        See why dual-device cryptographic confirmation surpasses legacy 2FA methods in both security and UX.
                    </p>
                </div>

                <div class="overflow-x-auto bg-white rounded-3xl border border-slate-200 shadow-sm">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/80 border-b border-slate-200 text-xs font-bold text-slate-700 uppercase tracking-wider">
                            <tr>
                                <th class="p-5">Security Vector</th>
                                <th class="p-5 text-slate-500 font-semibold">Passwords</th>
                                <th class="p-5 text-slate-500 font-semibold">SMS 2FA</th>
                                <th class="p-5 text-slate-500 font-semibold">TOTP App (6-digit)</th>
                                <th class="p-5 bg-indigo-50/50 text-indigo-900 font-bold border-x border-indigo-100">SecureTS Dual-Device</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr>
                                <td class="p-5 font-semibold text-slate-900">Phishing Protection</td>
                                <td class="p-5 text-rose-600 font-medium">❌ Vulnerable</td>
                                <td class="p-5 text-rose-600 font-medium">❌ Vulnerable (Reverse Proxy)</td>
                                <td class="p-5 text-rose-600 font-medium">❌ Vulnerable to relay</td>
                                <td class="p-5 bg-indigo-50/20 text-emerald-700 font-bold border-x border-indigo-100">✅ 100% Phishing Immune</td>
                            </tr>
                            <tr>
                                <td class="p-5 font-semibold text-slate-900">SIM Swap Resistance</td>
                                <td class="p-5 text-slate-500">N/A</td>
                                <td class="p-5 text-rose-600 font-medium">❌ High SIM swap risk</td>
                                <td class="p-5 text-emerald-700 font-medium">✅ Resistant</td>
                                <td class="p-5 bg-indigo-50/20 text-emerald-700 font-bold border-x border-indigo-100">✅ Hardware Chip Bound</td>
                            </tr>
                            <tr>
                                <td class="p-5 font-semibold text-slate-900">MFA Push Fatigue Resistance</td>
                                <td class="p-5 text-slate-500">N/A</td>
                                <td class="p-5 text-slate-500">N/A</td>
                                <td class="p-5 text-amber-600 font-medium">⚠️ Manual entry required</td>
                                <td class="p-5 bg-indigo-50/20 text-emerald-700 font-bold border-x border-indigo-100">✅ Match Code Validation</td>
                            </tr>
                            <tr>
                                <td class="p-5 font-semibold text-slate-900">Password Database Leaks</td>
                                <td class="p-5 text-rose-600 font-medium">❌ Catastrophic Risk</td>
                                <td class="p-5 text-rose-600 font-medium">❌ Passwords still exist</td>
                                <td class="p-5 text-rose-600 font-medium">❌ Passwords still exist</td>
                                <td class="p-5 bg-indigo-50/20 text-emerald-700 font-bold border-x border-indigo-100">✅ 0 Passwords in DB</td>
                            </tr>
                            <tr>
                                <td class="p-5 font-semibold text-slate-900">User Login Speed</td>
                                <td class="p-5 text-slate-600">8 - 15 seconds</td>
                                <td class="p-5 text-slate-600">15 - 30 seconds</td>
                                <td class="p-5 text-slate-600">10 - 20 seconds</td>
                                <td class="p-5 bg-indigo-50/20 text-indigo-900 font-bold border-x border-indigo-100">⚡ &lt; 2.0s Total Tap Time</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>

        <!-- DEVELOPER INTEGRATION & CODE SNIPPET -->
        <section id="code" class="py-20 md:py-28 bg-white border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    <div class="lg:col-span-5 space-y-6">
                        <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">Developer First SDK</span>
                        <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Drop-in Blade & Livewire Integration</h2>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Integrating dual-device security into your Laravel project requires zero cryptographic boilerplate. Add our Livewire component or middleware in just 3 lines of code.
                        </p>

                        <div class="space-y-3 text-xs text-slate-700 font-medium">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✓</span>
                                <span>Native Livewire 4 & Blade component support</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✓</span>
                                <span>Automatic WebSocket / Push notification fallbacks</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✓</span>
                                <span>Built-in device revocation & rate-limiting guards</span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a href="#waitlist" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-700">
                                <span>Explore Developer Documentation</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <div class="bg-slate-900 rounded-3xl border border-slate-800 shadow-xl overflow-hidden text-slate-200">
                            <!-- Code Header -->
                            <div class="bg-slate-950 px-5 py-3 border-b border-slate-800 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-rose-500/80"></span>
                                    <span class="w-3 h-3 rounded-full bg-amber-500/80"></span>
                                    <span class="w-3 h-3 rounded-full bg-emerald-500/80"></span>
                                    <span class="text-xs font-mono text-slate-400 ml-2">resources/views/auth/login.blade.php</span>
                                </div>
                                <span class="text-[10px] font-mono text-indigo-400 bg-indigo-950 px-2 py-0.5 rounded border border-indigo-800">Blade</span>
                            </div>

                            <!-- Code Body -->
                            <div class="p-6 font-mono text-xs leading-relaxed space-y-4 overflow-x-auto">
                                <div>
                                    <span class="text-slate-500">{{-- 1. In your Blade login view --}}</span><br>
                                    <span class="text-indigo-400">&lt;x-guest-layout&gt;</span><br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">&lt;livewire:securets.dual-device-auth</span> <br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-amber-300">redirect-to</span>=<span class="text-sky-300">"route('dashboard')"</span> <br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-amber-300">enforce-enclave</span>=<span class="text-sky-300">"true"</span> <br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">/&gt;</span><br>
                                    <span class="text-indigo-400">&lt;/x-guest-layout&gt;</span>
                                </div>

                                <div class="border-t border-slate-800 pt-4">
                                    <span class="text-slate-500">{{-- 2. In your routes/web.php --}}</span><br>
                                    <span class="text-sky-400">Route</span>::<span class="text-yellow-400">middleware</span>([<span class="text-emerald-300">'auth'</span>, <span class="text-emerald-300">'securets.verified'</span>])-&gt;<span class="text-yellow-400">group</span>(<span class="text-sky-300">function</span> () {<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-sky-400">Route</span>::<span class="text-yellow-400">get</span>(<span class="text-emerald-300">'/vault'</span>, [<span class="text-purple-400">VaultController</span>::<span class="text-sky-300">class</span>, <span class="text-emerald-300">'index'</span>]);<br>
                                    });
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- FAQ SECTION -->
        <section id="faq" class="py-20 md:py-28 bg-slate-50 border-t border-slate-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                
                <div class="text-center space-y-3">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">Frequently Asked Questions</span>
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Got Questions? We Have Answers.</h2>
                    <p class="text-sm text-slate-600">
                        Everything you need to know about dual-device authentication and recovery.
                    </p>
                </div>

                <div class="space-y-4" x-data="{ active: null }">
                    
                    <!-- FAQ 1 -->
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                        <button 
                            @click="active = active === 1 ? null : 1" 
                            class="w-full p-5 text-left font-bold text-sm text-slate-900 flex items-center justify-between cursor-pointer">
                            <span>What happens if a user loses their confirmation phone?</span>
                            <span class="text-slate-400 font-mono text-base" x-text="active === 1 ? '−' : '+'">+</span>
                        </button>
                        <div x-show="active === 1" x-collapse class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                            Users can configure encrypted multi-device recovery (such as a backup tablet or hardware security key) or use an enterprise zero-knowledge emergency recovery recovery kit signed during initial onboarding.
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                        <button 
                            @click="active = active === 2 ? null : 2" 
                            class="w-full p-5 text-left font-bold text-sm text-slate-900 flex items-center justify-between cursor-pointer">
                            <span>How does this differ from Google Authenticator or 6-digit TOTP?</span>
                            <span class="text-slate-400 font-mono text-base" x-text="active === 2 ? '−' : '+'">+</span>
                        </button>
                        <div x-show="active === 2" x-collapse class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                            Standard TOTP codes are vulnerable to modern reverse-proxy phishing kits (like Evilginx) where attackers steal the 6-digit code in real time. SecureTS is cryptographically bound to the server origin domain and hardware enclave, making relay attacks mathematically impossible.
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                        <button 
                            @click="active = active === 3 ? null : 3" 
                            class="w-full p-5 text-left font-bold text-sm text-slate-900 flex items-center justify-between cursor-pointer">
                            <span>Can a user pair multiple confirmation devices?</span>
                            <span class="text-slate-400 font-mono text-base" x-text="active === 3 ? '−' : '+'">+</span>
                        </button>
                        <div x-show="active === 3" x-collapse class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                            Yes. Users can bind their primary smartphone, a secondary work phone, or a dedicated tablet. Each device has an independent ECDSA key pair and can be revoked individually at any time from the account dashboard.
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                        <button 
                            @click="active = active === 4 ? null : 4" 
                            class="w-full p-5 text-left font-bold text-sm text-slate-900 flex items-center justify-between cursor-pointer">
                            <span>Is this compatible with Laravel 11/12/13 and Livewire?</span>
                            <span class="text-slate-400 font-mono text-base" x-text="active === 4 ? '−' : '+'">+</span>
                        </button>
                        <div x-show="active === 4" x-collapse class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                            Yes! Our SDK is built specifically for Laravel, providing ready-made Blade components, Livewire 4 reactive widgets, and middleware guards with full TypeScript & PHP 8.3+ typing.
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- WAITLIST / EARLY ACCESS SECTION -->
        <section id="waitlist" class="py-20 md:py-28 bg-white border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center space-y-3 max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">VIP Developer Access</span>
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Reserve Your Early Access Spot</h2>
                    <p class="text-sm text-slate-600">
                        Join developers, founders, and security teams building phishing-proof authentication.
                    </p>
                </div>

                <!-- Livewire Waitlist Form -->
                <livewire:waitlist-form />

            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-400 text-xs border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-10 border-b border-slate-800">
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                            S
                        </div>
                        <span class="text-sm font-bold text-white tracking-tight">SecureTS</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Next-generation zero-trust dual-device authentication protocol. Eliminate passwords with hardware-backed mobile confirmation.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="text-slate-200 font-semibold uppercase tracking-wider text-[11px]">Product</div>
                    <ul class="space-y-1.5">
                        <li><a href="#demo" class="hover:text-white transition">Interactive Sandbox</a></li>
                        <li><a href="#protocol" class="hover:text-white transition">Zero-Trust Protocol</a></li>
                        <li><a href="#comparison" class="hover:text-white transition">Security Benchmark</a></li>
                        <li><a href="#waitlist" class="hover:text-white transition">Early Access</a></li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <div class="text-slate-200 font-semibold uppercase tracking-wider text-[11px]">Developers</div>
                    <ul class="space-y-1.5">
                        <li><a href="#code" class="hover:text-white transition">Laravel & Blade SDK</a></li>
                        <li><a href="#code" class="hover:text-white transition">Livewire Components</a></li>
                        <li><a href="#faq" class="hover:text-white transition">Hardware Enclave Guide</a></li>
                        <li><a href="https://github.com" target="_blank" class="hover:text-white transition">GitHub Repository</a></li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <div class="text-slate-200 font-semibold uppercase tracking-wider text-[11px]">System Status</div>
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 space-y-1.5 font-mono text-[11px]">
                        <div class="flex items-center gap-2 text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>All Systems Operational</span>
                        </div>
                        <div class="text-slate-500 text-[10px]">Auth Gateway: 99.99% SLA</div>
                    </div>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <div>&copy; {{ date('Y') }} SecureTS Protocol. All rights reserved. Designed for clean, passwordless authentication.</div>
                <div class="flex gap-6">
                    <a href="#faq" class="hover:text-slate-300">Privacy Policy</a>
                    <a href="#faq" class="hover:text-slate-300">Security Whitepaper</a>
                    <a href="#faq" class="hover:text-slate-300">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
