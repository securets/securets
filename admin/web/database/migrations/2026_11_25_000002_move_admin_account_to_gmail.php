<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Moves the administrator account from the placeholder address to the real
 * Gmail inbox that receives the sign-in codes. Only the email changes: the
 * password hash and every other column are left untouched.
 */
return new class extends Migration
{
    private const OLD_EMAIL = 'user@admin.com';

    private const NEW_EMAIL = 'simoneoddy@gmail.com';

    public function up(): void
    {
        $this->move(self::OLD_EMAIL, self::NEW_EMAIL);
    }

    public function down(): void
    {
        $this->move(self::NEW_EMAIL, self::OLD_EMAIL);
    }

    private function move(string $from, string $to): void
    {
        // Never collide with an account that already uses the target address.
        if (DB::table('users')->whereRaw('LOWER(email) = ?', [$to])->exists()) {
            return;
        }

        DB::table('users')
            ->whereRaw('LOWER(email) = ?', [$from])
            ->update(['email' => $to, 'updated_at' => now()]);
    }
};
