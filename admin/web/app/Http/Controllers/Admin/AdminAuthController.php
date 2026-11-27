<?php

namespace App\Http\Controllers\Admin;

use App\Admin\AdminOtp;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAdmin;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Throwable;

class AdminAuthController extends Controller
{
    /** Session key holding the admin who passed the password step but not the code yet. */
    private const PENDING_KEY = 'admin_otp_pending';

    public function showLogin(): View|RedirectResponse
    {
        $user = Auth::guard('web')->user();

        return $user && EnsureAdmin::isAdmin($user->email)
            ? redirect()->route('admin.dashboard')
            : view('admin.login', ['allowlistEmpty' => config('admin.emails') === []]);
    }

    /**
     * Step 1 of the admin web sign-in: check the password, then email a
     * one-time code. The session is only created after the code is verified.
     */
    public function login(Request $request, AdminOtp $otp): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! EnsureAdmin::isAdmin($credentials['email']) || ! Auth::guard('web')->validate($credentials)) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match an administrator account.']);
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [strtolower($credentials['email'])])->firstOrFail();

        $request->session()->regenerate();

        return $this->sendCode($request, $otp, $user);
    }

    /**
     * Step 2: the code entry screen.
     */
    public function showOtp(Request $request, AdminOtp $otp): View|RedirectResponse
    {
        $pending = $request->session()->get(self::PENDING_KEY);

        if (! $pending) {
            return redirect()->route('admin.login');
        }

        return view('admin.otp', [
            'maskedEmail' => $this->maskEmail($pending['email']),
            'expiresAt' => $otp->expiresAt($pending['otp_id']),
            'resendWait' => $otp->resendWaitSeconds($pending['user_id']),
            'ttlMinutes' => (int) config('admin.otp.ttl_minutes'),
        ]);
    }

    public function verifyOtp(Request $request, AdminOtp $otp): RedirectResponse
    {
        $pending = $request->session()->get(self::PENDING_KEY);

        if (! $pending) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Your sign-in session ended. Please sign in again.']);
        }

        $data = $request->validate(['code' => ['required', 'string']]);
        $code = preg_replace('/\D/', '', $data['code']);

        if (strlen($code) !== 6) {
            return back()->withErrors(['code' => 'Enter the 6-digit code from the email.']);
        }

        [$result, $attemptsLeft] = $otp->verify($pending['otp_id'], $pending['user_id'], $code);

        if ($result === AdminOtp::INVALID) {
            return back()->withErrors(['code' => "That code is incorrect. {$attemptsLeft} ".($attemptsLeft === 1 ? 'attempt' : 'attempts').' left.']);
        }

        if ($result !== AdminOtp::VALID) {
            if ($result === AdminOtp::LOCKED) {
                $request->session()->forget(self::PENDING_KEY);

                return redirect()->route('admin.login')->withErrors(['email' => 'Too many incorrect codes. Sign in again to get a new code.']);
            }

            return back()->withErrors(['code' => $result === AdminOtp::EXPIRED
                ? 'That code has expired. Send a new code to continue.'
                : 'That code is no longer valid. Send a new code to continue.']);
        }

        $request->session()->forget(self::PENDING_KEY);
        $user = User::query()->find($pending['user_id']);

        if (! $user || ! EnsureAdmin::isAdmin($user->email)) {
            return redirect()->route('admin.login')->withErrors(['email' => 'This account is not an administrator.']);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        if (Schema::hasTable('admin_audit_events')) {
            DB::table('admin_audit_events')->insert([
                'actor' => $user->email,
                'action' => 'Admin Signed In',
                'target' => 'Verified with email code',
                'severity' => 'info',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function resendOtp(Request $request, AdminOtp $otp): RedirectResponse
    {
        $pending = $request->session()->get(self::PENDING_KEY);

        if (! $pending) {
            return redirect()->route('admin.login');
        }

        $wait = $otp->resendWaitSeconds($pending['user_id']);

        if ($wait > 0) {
            return back()->withErrors(['code' => "Please wait {$wait} seconds before requesting another code."]);
        }

        $user = User::query()->find($pending['user_id']);

        if (! $user) {
            $request->session()->forget(self::PENDING_KEY);

            return redirect()->route('admin.login');
        }

        return $this->sendCode($request, $otp, $user, 'A new code was sent.');
    }

    private function sendCode(Request $request, AdminOtp $otp, User $user, ?string $status = null): RedirectResponse
    {
        try {
            $otpId = $otp->issue($user, $request->ip());
        } catch (Throwable $exception) {
            report($exception);
            $request->session()->forget(self::PENDING_KEY);

            return redirect()->route('admin.login')
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'We could not send the sign-in code. Check the MAIL_ settings in admin/web/.env.']);
        }

        $request->session()->put(self::PENDING_KEY, [
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_id' => $otpId,
        ]);

        return redirect()->route('admin.otp.show')->with('status', $status ?? 'We emailed you a 6-digit code.');
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = mb_substr($name, 0, min(2, mb_strlen($name)));

        return $visible.str_repeat('•', max(1, mb_strlen($name) - mb_strlen($visible))).'@'.$domain;
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Token login for the admin mobile app. Issues an expiring token scoped to "admin".
     */
    public function apiLogin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->whereNull('archived_at')->first();

        if (! $user || ! EnsureAdmin::isAdmin($user->email) || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match an administrator account.']);
        }

        $token = $user->createToken(
            'admin-mobile:'.$credentials['device_name'],
            ['admin'],
            now()->addMinutes(config('admin.token_ttl_minutes'))
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'name' => $user->name,
        ]);
    }

    public function apiLogout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Signed out.']);
    }
}
