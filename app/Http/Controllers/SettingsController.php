<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Default preference structure.
     */
    public const DEFAULT_PREFERENCES = [
        'email_notifications' => true,
        'mobile_push'         => true,
        'theme'               => 'dark',
    ];

    /**
     * Display the settings page.
     */
    public function edit(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $preferences = array_merge(
            self::DEFAULT_PREFERENCES,
            $user->preferences ?? []
        );

        $deviceCount = $user->devices()->count();
        $integrationCount = $user->integrations()->count();

        return view('account.settings', [
            'user'             => $user,
            'preferences'      => $preferences,
            'deviceCount'      => $deviceCount,
            'integrationCount' => $integrationCount,
        ]);
    }

    /**
     * Update user preferences.
     */
    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $current = $user->preferences ?? self::DEFAULT_PREFERENCES;
        $updated = array_merge($current, [
            'email_notifications' => $request->boolean('email_notifications'),
            'mobile_push'         => $request->boolean('mobile_push'),
            'theme'               => $request->validated('theme'),
        ]);

        $user->update(['preferences' => $updated]);

        return back()->with('status', 'Preferences saved successfully.');
    }
}
