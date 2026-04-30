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

        \App\Models\Setting::set('site_name', $request->site_name);
        \App\Models\Setting::set('primary_color', $request->primary_color);
        \App\Models\Setting::set('sidebar_bg', $request->sidebar_bg);
        \App\Models\Setting::set('navbar_bg', $request->navbar_bg);
        \App\Models\Setting::set('sidebar_text', $request->sidebar_text);
        \App\Models\Setting::set('navbar_text', $request->navbar_text);

        return redirect()->back()->with('success', 'System preferences updated successfully.');
    }
}
