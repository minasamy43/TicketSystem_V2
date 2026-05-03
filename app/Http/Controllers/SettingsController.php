<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application settings.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }

    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists (optional but good practice)
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = bcrypt($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    /**
     * Update the system preferences (admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePreferences(Request $request)
    {
        if (Auth::user()->role != 1) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'site_name' => 'required|string|max:255',
            'primary_color' => 'required|string|max:50',
            'sidebar_bg' => 'required|string|max:50',
            'navbar_bg' => 'required|string|max:50',
            'sidebar_text' => 'required|string|max:50',
            'navbar_text' => 'required|string|max:50',
            'site_name_color' => 'required|string|max:50',
            'user_name_color' => 'required|string|max:50',
            'sidebar_separator' => 'required|string|max:50',
            'menu_title_color' => 'required|string|max:50',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        if ($request->hasFile('site_logo')) {
            $oldLogo = \App\Models\Setting::get('site_logo');
            if ($oldLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldLogo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('site_logo')->store('logos', 'public');
            \App\Models\Setting::set('site_logo', $path);
        } elseif ($request->restore_logo == '1') {
            $oldLogo = \App\Models\Setting::get('site_logo');
            if ($oldLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldLogo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
            }
            \App\Models\Setting::set('site_logo', null);
        }

        $keys = [
            'site_name', 'primary_color', 'sidebar_bg', 'navbar_bg', 
            'sidebar_text', 'navbar_text', 'site_name_color', 
            'user_name_color', 'sidebar_separator', 'menu_title_color'
        ];

        // Save current state for undo
        $oldSettings = [];
        foreach ($keys as $key) {
            $oldSettings[$key] = \App\Models\Setting::get($key);
        }
        session(['undo_preferences' => $oldSettings]);

        \App\Models\Setting::set('site_name', $request->site_name);
        \App\Models\Setting::set('primary_color', $request->primary_color);
        \App\Models\Setting::set('sidebar_bg', $request->sidebar_bg);
        \App\Models\Setting::set('navbar_bg', $request->navbar_bg);
        \App\Models\Setting::set('sidebar_text', $request->sidebar_text);
        \App\Models\Setting::set('navbar_text', $request->navbar_text);
        \App\Models\Setting::set('site_name_color', $request->site_name_color);
        \App\Models\Setting::set('user_name_color', $request->user_name_color);
        \App\Models\Setting::set('sidebar_separator', $request->sidebar_separator);
        \App\Models\Setting::set('menu_title_color', $request->menu_title_color);

        return redirect()->back()->with('success', 'System preferences updated successfully.');
    }

    /**
     * Undo a single preference update.
     */
    public function undoSinglePreference(Request $request, $key)
    {
        if (session()->has('undo_preferences') && isset(session('undo_preferences')[$key])) {
            $oldValue = session('undo_preferences')[$key];
            \App\Models\Setting::set($key, $oldValue);
            
            // Optionally remove this specific key from session so it can't be undone again,
            // or clear the whole undo session if you only want 1 strict undo step.
            // We'll just clear the session to mimic a 1-step global undo that was consumed.
            session()->forget('undo_preferences');

            return redirect()->back()->with('success', 'Color change undone successfully.');
        }

        return redirect()->back()->with('error', 'Nothing to undo.');
    }

    /**
     * Save current theme configuration as a preset.
     */
    public function saveTheme(Request $request)
    {
        $request->validate([
            'theme_name' => 'required|string|max:100',
            'primary_color' => 'required|string|max:50',
            'sidebar_bg' => 'required|string|max:50',
            'navbar_bg' => 'required|string|max:50',
            'sidebar_text' => 'required|string|max:50',
            'navbar_text' => 'required|string|max:50',
            'site_name_color' => 'required|string|max:50',
            'user_name_color' => 'required|string|max:50',
            'sidebar_separator' => 'required|string|max:50',
            'menu_title_color' => 'required|string|max:50',
        ]);

        $savedThemesJson = \App\Models\Setting::get('saved_themes', '[]');
        $savedThemes = json_decode($savedThemesJson, true);
        if (!is_array($savedThemes)) {
            $savedThemes = [];
        }

        $newTheme = [
            'id' => uniqid('theme_'),
            'name' => $request->theme_name,
            'colors' => [
                'primary_color' => $request->primary_color,
                'sidebar_bg' => $request->sidebar_bg,
                'navbar_bg' => $request->navbar_bg,
                'sidebar_text' => $request->sidebar_text,
                'navbar_text' => $request->navbar_text,
                'site_name_color' => $request->site_name_color,
                'user_name_color' => $request->user_name_color,
                'sidebar_separator' => $request->sidebar_separator,
                'menu_title_color' => $request->menu_title_color,
            ],
            'created_at' => now()->toDateTimeString(),
        ];

        $savedThemes[] = $newTheme;
        \App\Models\Setting::set('saved_themes', json_encode($savedThemes));

        return redirect()->back()->with('success', 'Custom design "' . $request->theme_name . '" saved successfully.');
    }

    /**
     * Delete a saved theme preset.
     */
    public function deleteTheme($id)
    {
        $savedThemesJson = \App\Models\Setting::get('saved_themes', '[]');
        $savedThemes = json_decode($savedThemesJson, true);
        if (!is_array($savedThemes)) {
            $savedThemes = [];
        }

        $filteredThemes = array_filter($savedThemes, function($theme) use ($id) {
            return isset($theme['id']) && $theme['id'] !== $id;
        });

        \App\Models\Setting::set('saved_themes', json_encode(array_values($filteredThemes)));

        return redirect()->back()->with('success', 'Saved design deleted successfully.');
    }
}
