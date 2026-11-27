<?php

namespace App\Admin;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Issues and checks the emailed one-time sign-in codes for admin web logins.
 */
class AdminOtp
{
    public const VALID = 'valid';

    public const INVALID = 'invalid';

    public const EXPIRED = 'expired';

    public const LOCKED = 'locked';

    public const MISSING = 'missing';

    /**
     * Create a new code for the user, email it, and return the stored row id.
     * Any earlier unused code for the same user stops working.
     *
     * @throws Throwable when the email cannot be sent (the new code is discarded).
     */
    public function issue(object $user, ?string $ipAddress): int
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $ttl = max(1, (int) config('admin.otp.ttl_minutes'));
        $now = now();

        DB::table('admin_login_otps')
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => $now, 'updated_at' => $now]);

        $id = (int) DB::table('admin_login_otps')->insertGetId([
            'user_id' => $user->id,
            'code_hash' => $this->hash($code),
            'expires_at' => $now->copy()->addMinutes($ttl),
            'attempts' => 0,
            'ip_address' => $ipAddress,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        try {
            Mail::send('admin.emails.otp', [
                'code' => $code,
                'name' => $user->name ?: 'Admin',
                'ttl' => $ttl,
                'ipAddress' => $ipAddress,
            ], function ($message) use ($user): void {
                $message->to($user->email)->subject('Your SecureTS admin sign-in code');
            });
        } catch (Throwable $exception) {
            DB::table('admin_login_otps')->where('id', $id)->delete();

            throw $exception;
        }

        return $id;
    }

    /**
     * Check a submitted code against the pending row. Returns one of the
     * class constants; a VALID result consumes the code.
     *
     * @return array{0: string, 1: int} [result, attempts left]
     */
    public function verify(int $otpId, int $userId, string $code): array
    {
        $maxAttempts = max(1, (int) config('admin.otp.max_attempts'));
        $otp = DB::table('admin_login_otps')
            ->where('id', $otpId)
            ->where('user_id', $userId)
            ->whereNull('consumed_at')
            ->first();

        if (! $otp) {
            return [self::MISSING, 0];
        }

        if (Carbon::parse($otp->expires_at)->isPast()) {
            $this->consume($otpId);

            return [self::EXPIRED, 0];
        }

        if ((int) $otp->attempts >= $maxAttempts) {
            $this->consume($otpId);

            return [self::LOCKED, 0];
        }

        if (! hash_equals($otp->code_hash, $this->hash($code))) {
            $attempts = (int) $otp->attempts + 1;
            DB::table('admin_login_otps')->where('id', $otpId)->update(['attempts' => $attempts, 'updated_at' => now()]);

            if ($attempts >= $maxAttempts) {
                $this->consume($otpId);

                return [self::LOCKED, 0];
            }

            return [self::INVALID, $maxAttempts - $attempts];
        }

        $this->consume($otpId);

        return [self::VALID, $maxAttempts - (int) $otp->attempts];
    }

    /**
     * Seconds the admin must still wait before another code may be sent.
     */
    public function resendWaitSeconds(int $userId): int
    {
        $last = DB::table('admin_login_otps')->where('user_id', $userId)->max('created_at');

        if (! $last) {
            return 0;
        }

        $availableAt = Carbon::parse($last)->addSeconds((int) config('admin.otp.resend_cooldown_seconds'));

        return max(0, (int) ceil(now()->diffInSeconds($availableAt, false)));
    }

    public function expiresAt(int $otpId): ?Carbon
    {
        $value = DB::table('admin_login_otps')->where('id', $otpId)->value('expires_at');

        return $value ? Carbon::parse($value) : null;
    }

    private function consume(int $otpId): void
    {
        DB::table('admin_login_otps')->where('id', $otpId)->update(['consumed_at' => now(), 'updated_at' => now()]);
    }

    private function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }
}
