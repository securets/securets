<?php

namespace App\Admin;

use Illuminate\Support\Facades\DB;

/**
 * Per-admin theme and personalization, stored as JSON in
 * public.system_controls under "admin.preferences.{user id}".
 */
class AdminPreferences
{
    public const OPTIONS = [
        'theme' => ['dark', 'light', 'system'],
        'accent' => ['blue', 'violet', 'emerald', 'amber', 'rose'],
        'density' => ['comfortable', 'compact'],
        'start_page' => ['dashboard', 'users', 'purchases', 'sessions', 'recovery', 'audit', 'status'],
    ];

    public const DEFAULTS = [
        'theme' => 'dark',
        'accent' => 'blue',
        'density' => 'comfortable',
        'start_page' => 'dashboard',
    ];

    public function get(?int $userId): array
    {
        if ($userId === null) {
            return self::DEFAULTS;
        }

        $stored = json_decode((string) DB::table('system_controls')->where('key', $this->key($userId))->value('value'), true);

        return $this->clean(is_array($stored) ? $stored : []);
    }

    public function save(int $userId, array $values): array
    {
        $preferences = $this->clean($values);

        DB::table('system_controls')->updateOrInsert(
            ['key' => $this->key($userId)],
            ['value' => json_encode($preferences), 'updated_at' => now(), 'created_at' => now()]
        );

        return $preferences;
    }

    /**
     * Keep only known keys with allowed values; fall back to the defaults.
     */
    private function clean(array $values): array
    {
        $clean = [];

        foreach (self::DEFAULTS as $key => $default) {
            $value = $values[$key] ?? $default;
            $clean[$key] = in_array($value, self::OPTIONS[$key], true) ? $value : $default;
        }

        return $clean;
    }

    private function key(int $userId): string
    {
        return "admin.preferences.{$userId}";
    }
}
