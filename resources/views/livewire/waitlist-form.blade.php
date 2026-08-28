<div class="w-full max-w-xl mx-auto">
    @if ($submitted)
        <div class="bg-white rounded-3xl p-8 border border-emerald-200/80 shadow-md text-center space-y-5 animate-in fade-in zoom-in-95 duration-200">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-emerald-800 bg-emerald-100/60 rounded-full">
                    VIP Queue Position: #{{ $queuePosition }}
                </span>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight">You're on the Early Access List</h3>
                <p class="text-sm text-slate-600 max-w-md mx-auto">
                    We've registered <strong class="text-slate-900">{{ $email }}</strong> for our developer preview. We'll send your API keys and SDK access as batches open.
                </p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 text-left space-y-2">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Your Priority Access Pass</div>
                <div class="flex items-center justify-between gap-3 bg-white px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-sm font-bold text-slate-800">
                    <span>{{ $referralCode }}</span>
                    <span class="text-xs font-sans text-indigo-600 font-medium">Priority Enabled</span>
                </div>
            </div>

            <button 
                wire:click="resetForm" 
                class="text-xs font-semibold text-slate-500 hover:text-slate-900 underline underline-offset-4 cursor-pointer">
                Register another team member
            </button>
        </div>
    @else
        <form wire:submit.prevent="submit" class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm space-y-6">
            <div class="space-y-2 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold text-slate-700 bg-slate-100 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    Limited Batch Preview
                </div>
                <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Request Early Access & SDK</h3>
                <p class="text-sm text-slate-500">Get zero-trust dual device authentication integrated into your apps in minutes.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="waitlist-email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Work Email <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        id="waitlist-email"
                        type="email" 
                        wire:model="email" 
                        placeholder="you@organization.com"
                        class="w-full px-4 py-3 bg-slate-50/70 border @error('email') border-rose-300 ring-1 ring-rose-200 @else border-slate-200 @enderror rounded-xl text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition"
                    />
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="waitlist-role" class="block text-xs font-semibold text-slate-700 mb-1.5">Your Role</label>
                        <select 
                            id="waitlist-role"
                            wire:model="role" 
                            class="w-full px-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition">
                            <option value="developer">Developer / Engineer</option>
                            <option value="security_engineer">Security Architect / CISO</option>
                            <option value="founder_cto">CTO / Founder</option>
                            <option value="enterprise_it">Enterprise IT Manager</option>
                            <option value="product_manager">Product Manager</option>
                        </select>
                    </div>

                    <div>
                        <label for="waitlist-team" class="block text-xs font-semibold text-slate-700 mb-1.5">Team Size</label>
                        <select 
                            id="waitlist-team"
                            wire:model="teamSize" 
                            class="w-full px-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition">
                            <option value="1-10">1 - 10 employees</option>
                            <option value="11-50">11 - 50 employees</option>
                            <option value="51-200">51 - 200 employees</option>
                            <option value="200+">200+ employees</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="waitlist-notes" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Planned Use Case <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <textarea 
                        id="waitlist-notes"
                        wire:model="useCase" 
                        rows="2"
                        placeholder="e.g. Replacing SMS 2FA in our customer portal, or internal admin dashboard access..."
                        class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition"
                    ></textarea>
                </div>
            </div>

            <button 
                type="submit" 
                class="w-full py-3.5 px-6 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition shadow-sm hover:shadow flex items-center justify-center gap-2 cursor-pointer group">
                <span wire:loading.remove>Join VIP Priority Waitlist</span>
                <span wire:loading class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Reserving your spot...
                </span>
                <svg wire:loading.remove class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>

            <div class="flex items-center justify-center gap-4 text-xs text-slate-400 pt-1">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Zero Spam Guarantee
                </span>
                <span>•</span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Developer SDK Included
                </span>
            </div>
        </form>
    @endif
</div>
