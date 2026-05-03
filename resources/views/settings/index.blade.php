@extends('layouts.app')

@section('title', 'Settings - HelpTK')
@section('breadcrumb', 'Settings')

@push('styles')
    <style>
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .settings-sidebar {
            background: white;
            border-radius: 15px;
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            height: 100%;
        }

        .settings-nav-item {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: var(--gray-dark, #555);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .settings-nav-item i {
            width: 25px;
            font-size: 1.1rem;
        }

        .settings-nav-item:hover {
            background: var(--primary-light);
            color: var(--primary-hover);
        }

        .settings-nav-item.active {
            background: var(--primary-light);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 600;
        }

        .settings-content-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            min-height: 500px;
        }

        .settings-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-color, #333);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem var(--primary-light);
        }



        .avatar-upload {
            position: relative;
            max-width: 120px;
            margin-bottom: 20px;
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            box-shadow: 0 4px 15px var(--primary-light);
        }

        .avatar-edit {
            position: absolute;
            right: 15px;
            bottom: 0;
        }

        .avatar-edit .btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 2px solid #eee;
            color: #555;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Tab hiding/showing */
        .settings-tab-pane {
            display: none;
        }

        .settings-tab-pane.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')
    <div class="settings-container px-3 py-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> Please fix the errors below.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Settings Sidebar -->
            <div class="col-lg-3">
                <div class="settings-sidebar">
                    <div class="px-4 mb-3 text-uppercase text-muted"
                        style="font-size: 0.8rem; font-weight: 700; letter-spacing: 1px;">Personal</div>

                    <div class="settings-nav-item active" onclick="switchTab('profile', this)">
                        <i class="fa-solid fa-user"></i> My Profile
                    </div>
                    <div class="settings-nav-item" onclick="switchTab('security', this)">
                        <i class="fa-solid fa-lock"></i> Security
                    </div>

                    @if(Auth::user()->role == 1)
                        <div class="px-4 mt-4 mb-3 text-uppercase text-muted"
                            style="font-size: 0.8rem; font-weight: 700; letter-spacing: 1px;">System</div>

                        <div class="settings-nav-item" onclick="switchTab('preferences', this)">
                            <i class="fa-solid fa-sliders"></i> Preferences
                        </div>
                    @endif
                </div>
            </div>

            <!-- Settings Content -->
            <div class="col-lg-9">
                <div class="settings-content-card">

                    <!-- Profile Tab -->
                    <div id="tab-profile" class="settings-tab-pane active">
                        <h3 class="settings-section-title"><i class="fa-solid fa-user text-muted"></i> Profile Information
                        </h3>

                        <form
                            action="{{ Auth::user()->role == 1 ? route('admin.settings.profile') : route('user.settings.profile') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Profile Photo</label>
                                    <div class="avatar-upload">
                                        <div class="avatar-preview" id="avatarPreviewContainer"
                                            style="{{ $user->avatar ? 'background-image: url(' . asset('storage/' . $user->avatar) . '); background-size: cover; background-position: center; color: transparent;' : '' }}">
                                            <span id="avatarInitial"
                                                style="{{ $user->avatar ? 'display: none;' : '' }}">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                        <div class="avatar-edit">
                                            <input type="file" id="avatarInput" name="avatar" accept="image/*"
                                                style="display: none;">
                                            <button type="button" class="btn" title="Upload new photo"
                                                onclick="document.getElementById('avatarInput').click()">
                                                <i class="fa-solid fa-camera"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text">Allowed formats: JPEG, PNG, JPG, GIF. Max size 2MB.</div>
                                    @error('avatar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                        name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                        name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top text-end">
                                <button type="submit" class="btn btn-gold px-4">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Tab -->
                    <div id="tab-security" class="settings-tab-pane">
                        <h3 class="settings-section-title"><i class="fa-solid fa-lock text-muted"></i> Security &
                            Password</h3>

                        <form
                            action="{{ Auth::user()->role == 1 ? route('admin.settings.password') : route('user.settings.password') }}"
                            method="POST">
                            @csrf

                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label for="current_password" class="form-label fw-semibold">Current Password</label>
                                    <input type="password"
                                        class="form-control @error('current_password') is-invalid @enderror"
                                        id="current_password" name="current_password" required>
                                    @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">New Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" required>
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirm New
                                        Password</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top text-end">
                                <button type="submit" class="btn btn-gold px-4">Update Password</button>
                            </div>
                        </form>
                    </div>

                    <!-- Preferences Tab (Admin Only Placeholder) -->
                    @if(Auth::user()->role == 1)
                        <div id="tab-preferences" class="settings-tab-pane">
                            <h3 class="settings-section-title"><i class="fa-solid fa-sliders text-muted"></i> System Preferences
                            </h3>

                            <form action="{{ route('admin.settings.preferences') }}" method="POST"
                                enctype="multipart/form-data" id="preferencesForm">
                                @csrf
                                <input type="hidden" name="restore_logo" id="restore_logo" value="0">



                                <div class="p-4 border rounded mb-4 shadow-sm" style="background-color: #fafbfe;">
                                    <h5 class="mb-4 fw-bold text-muted d-flex align-items-center gap-2" style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px;">
                                        <i class="fa-solid fa-palette"></i> General Branding
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">System Logo</label>
                                            <div class="d-flex align-items-center gap-4">
                                                <div class="p-3 border rounded bg-white text-center shadow-sm" style="width: 150px;">
                                                    <img id="logoPreview"
                                                        src="{{ \App\Models\Setting::get('site_logo') ? asset('storage/' . \App\Models\Setting::get('site_logo')) : asset('img/HelpTK--C.png') }}"
                                                        alt="Logo" style="max-height: 40px; max-width: 100%;">
                                                </div>
                                                <div>
                                                    <input type="file" class="form-control" id="site_logo" name="site_logo"
                                                        accept="image/png, image/jpeg, image/svg+xml">
                                                    <div class="form-text mt-2">Recommended size: 200x50px. Allowed formats: PNG,
                                                        JPG, SVG.</div>
                                                    @error('site_logo') <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mt-4">
                                            <label for="site_name" class="form-label fw-semibold">System Name</label>
                                            <input type="text" class="form-control @error('site_name') is-invalid @enderror"
                                                id="site_name" name="site_name"
                                                value="{{ old('site_name', \App\Models\Setting::get('site_name', 'HelpTK')) }}"
                                                required>
                                            <div class="form-text">This appears in the top of the sidebar.</div>
                                            @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6 mt-4">
                                            <label for="primary_color" class="form-label fw-semibold">Primary Theme Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" class="form-control form-control-color" id="primary_color"
                                                    name="primary_color"
                                                    value="{{ old('primary_color', \App\Models\Setting::get('primary_color', '#d4af53')) }}"
                                                    title="Choose your color" required
                                                    style="width: 60px; height: 45px; padding: 5px;">
                                                @if(session()->has('undo_preferences') && isset(session('undo_preferences')['primary_color']) && session('undo_preferences')['primary_color'] !== \App\Models\Setting::get('primary_color'))
                                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-circle shadow-sm" onclick="undoSingle('primary_color')" title="Undo" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                                @endif
                                                <div class="form-text mb-0">Select the main accent color (Gold by default).</div>
                                            </div>
                                            @error('primary_color') <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 border rounded mb-4 shadow-sm" style="background-color: #fafbfe;">
                                    <h5 class="mb-4 fw-bold text-muted d-flex align-items-center gap-2" style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px;">
                                        <i class="fa-solid fa-list"></i> Sidebar Appearance
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="sidebar_bg" class="form-label fw-semibold">Sidebar Background Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" class="form-control form-control-color" id="sidebar_bg"
                                                    name="sidebar_bg"
                                                    value="{{ old('sidebar_bg', \App\Models\Setting::get('sidebar_bg', '#ffffff')) }}"
                                                    title="Choose your color" required
                                                    style="width: 60px; height: 45px; padding: 5px;">
                                                @if(session()->has('undo_preferences') && isset(session('undo_preferences')['sidebar_bg']) && session('undo_preferences')['sidebar_bg'] !== \App\Models\Setting::get('sidebar_bg'))
                                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-circle shadow-sm" onclick="undoSingle('sidebar_bg')" title="Undo" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                                @endif
                                                <div class="form-text mb-0">Color of the left navigation panel.</div>
                                            </div>
                                            @error('sidebar_bg') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="sidebar_text" class="form-label fw-semibold">Sidebar Text & Icons
                                                Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" class="form-control form-control-color" id="sidebar_text"
                                                    name="sidebar_text"
                                                    value="{{ old('sidebar_text', \App\Models\Setting::get('sidebar_text', '#6c7380')) }}"
                                                    title="Choose your color" required
                                                    style="width: 60px; height: 45px; padding: 5px;">
                                                @if(session()->has('undo_preferences') && isset(session('undo_preferences')['sidebar_text']) && session('undo_preferences')['sidebar_text'] !== \App\Models\Setting::get('sidebar_text'))
                                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-circle shadow-sm" onclick="undoSingle('sidebar_text')" title="Undo" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                                @endif
                                                <div class="form-text mb-0">Color of the text and icons in the sidebar.</div>
                                            </div>
                                            @error('sidebar_text') <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mt-4">
                                            <label for="site_name_color" class="form-label fw-semibold">System Name Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" class="form-control form-control-color" id="site_name_color"
                                                    name="site_name_color"
                                                    value="{{ old('site_name_color', \App\Models\Setting::get('site_name_color', '#1a1a1a')) }}"
                                                    title="Choose your color" required
                                                    style="width: 60px; height: 45px; padding: 5px;">
                                                @if(session()->has('undo_preferences') && isset(session('undo_preferences')['site_name_color']) && session('undo_preferences')['site_name_color'] !== \App\Models\Setting::get('site_name_color'))
                                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-circle shadow-sm" onclick="undoSingle('site_name_color')" title="Undo" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                                @endif
                                                <div class="form-text mb-0">Color of the sidebar title (HelpTK).</div>
                                            </div>
                                            @error('site_name_color') <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mt-4">
                                            <label for="user_name_color" class="form-label fw-semibold">User Name & Title Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" class="form-control form-control-color" id="user_name_color"
                                                    name="user_name_color"
                                                    value="{{ old('user_name_color', \App\Models\Setting::get('user_name_color', '#6c7380')) }}"
                                                    title="Choose your color" required
                                                    style="width: 60px; height: 45px; padding: 5px;">
                                                @if(session()->has('undo_preferences') && isset(session('undo_preferences')['user_name_color']) && session('undo_preferences')['user_name_color'] !== \App\Models\Setting::get('user_name_color'))
                                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-circle shadow-sm" onclick="undoSingle('user_name_color')" title="Undo" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                                @endif
                                                <div class="form-text mb-0">Color of the user name and role in the sidebar.</div>
                                            </div>
                                            @error('user_name_color') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6 mt-4">
                                            <label for="sidebar_separator" class="form-label fw-semibold">Sidebar Separator Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" class="form-control form-control-color" id="sidebar_separator"
                                                    name="sidebar_separator"
                                                    value="{{ old('sidebar_separator', \App\Models\Setting::get('sidebar_separator', '#e6e9f4')) }}"
                                                    title="Choose your color" required
                                                    style="width: 60px; height: 45px; padding: 5px;">
                                                @if(session()->has('undo_preferences') && isset(session('undo_preferences')['sidebar_separator']) && session('undo_preferences')['sidebar_separator'] !== \App\Models\Setting::get('sidebar_separator'))
                                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-circle shadow-sm" onclick="undoSingle('sidebar_separator')" title="Undo" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                                @endif
                                                <div class="form-text mb-0">Color of the lines between sidebar sections.</div>
                                            </div>
                                            @error('sidebar_separator') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6 mt-4">
                                            <label for="menu_title_color" class="form-label fw-semibold">Sidebar Menu Title Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" class="form-control form-control-color" id="menu_title_color"
                                                    name="menu_title_color"
                                                    value="{{ old('menu_title_color', \App\Models\Setting::get('menu_title_color', '#828795')) }}"
                                                    title="Choose your color" required
                                                    style="width: 60px; height: 45px; padding: 5px;">
                                                @if(session()->has('undo_preferences') && isset(session('undo_preferences')['menu_title_color']) && session('undo_preferences')['menu_title_color'] !== \App\Models\Setting::get('menu_title_color'))
                                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-circle shadow-sm" onclick="undoSingle('menu_title_color')" title="Undo" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                                @endif
                                                <div class="form-text mb-0">Color of section labels (Overview, Management).</div>
                                            </div>
                                            @error('menu_title_color') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 border rounded mb-4 shadow-sm" style="background-color: #fafbfe;">
                                    <h5 class="mb-4 fw-bold text-muted d-flex align-items-center gap-2" style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px;">
                                        <i class="fa-solid fa-window-maximize"></i> Navbar Appearance
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="navbar_bg" class="form-label fw-semibold">Navbar Background Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" class="form-control form-control-color" id="navbar_bg"
                                                    name="navbar_bg"
                                                    value="{{ old('navbar_bg', \App\Models\Setting::get('navbar_bg', '#ffffff')) }}"
                                                    title="Choose your color" required
                                                    style="width: 60px; height: 45px; padding: 5px;">
                                                @if(session()->has('undo_preferences') && isset(session('undo_preferences')['navbar_bg']) && session('undo_preferences')['navbar_bg'] !== \App\Models\Setting::get('navbar_bg'))
                                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-circle shadow-sm" onclick="undoSingle('navbar_bg')" title="Undo" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                                @endif
                                                <div class="form-text mb-0">Color of the top navigation bar.</div>
                                            </div>
                                            @error('navbar_bg') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="navbar_text" class="form-label fw-semibold">Navbar Text Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" class="form-control form-control-color" id="navbar_text"
                                                    name="navbar_text"
                                                    value="{{ old('navbar_text', \App\Models\Setting::get('navbar_text', '#6c7380')) }}"
                                                    title="Choose your color" required
                                                    style="width: 60px; height: 45px; padding: 5px;">
                                                @if(session()->has('undo_preferences') && isset(session('undo_preferences')['navbar_text']) && session('undo_preferences')['navbar_text'] !== \App\Models\Setting::get('navbar_text'))
                                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-circle shadow-sm" onclick="undoSingle('navbar_text')" title="Undo" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                                @endif
                                                <div class="form-text mb-0">Color of the text and icons in the top navbar.</div>
                                            </div>
                                            @error('navbar_text') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $savedThemes = json_decode(\App\Models\Setting::get('saved_themes', '[]'), true);
                                @endphp

                                @if(!empty($savedThemes))
                                <div class="p-4 border rounded mb-4 shadow-sm" style="background-color: #fff;">
                                    <h5 class="mb-3 fw-bold text-muted d-flex align-items-center gap-2" style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px;">
                                        <i class="fa-solid fa-bookmark"></i> Saved Designs
                                    </h5>
                                    <div class="row g-3">
                                        @foreach($savedThemes as $theme)
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 d-flex flex-column h-100 position-relative" style="background: #fafbfe;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <strong class="text-dark">{{ $theme['name'] ?? 'Unnamed Design' }}</strong>
                                                    <div class="d-flex gap-1" style="height: 15px;">
                                                        <div style="width: 15px; background: {{ $theme['colors']['primary_color'] ?? '#d4af53' }}; border-radius: 2px;"></div>
                                                        <div style="width: 15px; background: {{ $theme['colors']['sidebar_bg'] ?? '#fff' }}; border-radius: 2px; border: 1px solid #ddd;"></div>
                                                    </div>
                                                </div>
                                                <div class="mt-auto pt-3 d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1" onclick='applyTheme(@json($theme["colors"]))'>Apply</button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTheme('{{ $theme['id'] }}')"><i class="fa-solid fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-outline-primary px-4" data-bs-toggle="modal" data-bs-target="#saveThemeModal">
                                        <i class="fa-solid fa-bookmark"></i> Save as New Design
                                    </button>
                                    <div>

                                        <button type="button" class="btn btn-outline-secondary px-4 me-2"
                                            onclick="document.getElementById('site_name').value='HelpTK'; document.getElementById('primary_color').value='#d4af53'; document.getElementById('sidebar_bg').value='#ffffff'; document.getElementById('navbar_bg').value='#ffffff'; document.getElementById('sidebar_text').value='#6c7380'; document.getElementById('navbar_text').value='#6c7380'; document.getElementById('site_name_color').value='#1a1a1a'; document.getElementById('user_name_color').value='#6c7380'; document.getElementById('sidebar_separator').value='#e6e9f4'; document.getElementById('menu_title_color').value='#828795'; document.getElementById('restore_logo').value='1'; document.getElementById('site_logo').value=''; document.getElementById('logoPreview').src='{{ asset('img/HelpTK--C.png') }}'; document.getElementById('preferencesForm').submit();">Restore
                                            Defaults</button>
                                        <button type="submit" class="btn btn-gold px-4">Save Preferences</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- Modals and Hidden Forms -->
    @if(Auth::user()->role == 1)
    <!-- Save Theme Modal -->
    <div class="modal fade" id="saveThemeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Save Custom Design</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.settings.themes.save') }}" method="POST" id="saveThemeForm">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Give a name to your current color configuration to save it as a preset.</p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Design Name</label>
                            <input type="text" name="theme_name" class="form-control" placeholder="e.g., Dark Mode Gold" required>
                        </div>
                        
                        <!-- Hidden inputs to grab current colors from main form -->
                        <input type="hidden" name="primary_color" id="hidden_primary_color">
                        <input type="hidden" name="sidebar_bg" id="hidden_sidebar_bg">
                        <input type="hidden" name="navbar_bg" id="hidden_navbar_bg">
                        <input type="hidden" name="sidebar_text" id="hidden_sidebar_text">
                        <input type="hidden" name="navbar_text" id="hidden_navbar_text">
                        <input type="hidden" name="site_name_color" id="hidden_site_name_color">
                        <input type="hidden" name="user_name_color" id="hidden_user_name_color">
                        <input type="hidden" name="sidebar_separator" id="hidden_sidebar_separator">
                        <input type="hidden" name="menu_title_color" id="hidden_menu_title_color">
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="submitThemeForm()">Save Design</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden form for deleting theme -->
    <form id="deleteThemeForm" method="POST" style="display: none;">
        @csrf
    </form>
    
    <!-- Hidden form for single undo -->
    <form id="undoSingleForm" method="POST" style="display: none;">
        @csrf
    </form>
    @endif
@endsection

@push('scripts')
    <script>
        function undoSingle(key) {
            const form = document.getElementById('undoSingleForm');
            form.action = '/admin/settings/preferences/undo/' + key;
            form.submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit preferences form when a color is selected
            const colorPickers = document.querySelectorAll('#preferencesForm input[type="color"]');
            colorPickers.forEach(picker => {
                picker.addEventListener('change', function() {
                    document.getElementById('preferencesForm').submit();
                });
            });
        });
        @if(Auth::user()->role == 1)
        function applyTheme(colors) {
            if(colors.primary_color) document.getElementById('primary_color').value = colors.primary_color;
            if(colors.sidebar_bg) document.getElementById('sidebar_bg').value = colors.sidebar_bg;
            if(colors.navbar_bg) document.getElementById('navbar_bg').value = colors.navbar_bg;
            if(colors.sidebar_text) document.getElementById('sidebar_text').value = colors.sidebar_text;
            if(colors.navbar_text) document.getElementById('navbar_text').value = colors.navbar_text;
            if(colors.site_name_color) document.getElementById('site_name_color').value = colors.site_name_color;
            if(colors.user_name_color) document.getElementById('user_name_color').value = colors.user_name_color;
            if(colors.sidebar_separator) document.getElementById('sidebar_separator').value = colors.sidebar_separator;
            if(colors.menu_title_color) document.getElementById('menu_title_color').value = colors.menu_title_color;
            
            // Auto-submit the form to apply changes immediately
            document.getElementById('preferencesForm').submit();
        }

        function deleteTheme(id) {
            if(confirm('Are you sure you want to delete this design?')) {
                const form = document.getElementById('deleteThemeForm');
                form.action = '/admin/settings/themes/delete/' + id;
                form.submit();
            }
        }

        function submitThemeForm() {
            // copy values from main form to hidden form
            document.getElementById('hidden_primary_color').value = document.getElementById('primary_color').value;
            document.getElementById('hidden_sidebar_bg').value = document.getElementById('sidebar_bg').value;
            document.getElementById('hidden_navbar_bg').value = document.getElementById('navbar_bg').value;
            document.getElementById('hidden_sidebar_text').value = document.getElementById('sidebar_text').value;
            document.getElementById('hidden_navbar_text').value = document.getElementById('navbar_text').value;
            document.getElementById('hidden_site_name_color').value = document.getElementById('site_name_color').value;
            document.getElementById('hidden_user_name_color').value = document.getElementById('user_name_color').value;
            document.getElementById('hidden_sidebar_separator').value = document.getElementById('sidebar_separator').value;
            document.getElementById('hidden_menu_title_color').value = document.getElementById('menu_title_color').value;
            
            document.getElementById('saveThemeForm').submit();
        }
        @endif

        function switchTab(tabId, element) {
            // Remove active class from all nav items
            document.querySelectorAll('.settings-nav-item').forEach(item => {
                item.classList.remove('active');
            });

            // Add active class to clicked item
            element.classList.add('active');

            // Hide all tab panes
            document.querySelectorAll('.settings-tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });

            // Show target tab pane
            document.getElementById('tab-' + tabId).classList.add('active');

            // Save to session storage
            sessionStorage.setItem('settingsActiveTab', tabId);
        }

        // Keep active tab on validation failure or reload
        document.addEventListener('DOMContentLoaded', function () {
            // Restore active tab from session storage
            const savedTab = sessionStorage.getItem('settingsActiveTab');
            if (savedTab) {
                const tabElement = document.querySelector(`[onclick="switchTab('${savedTab}', this)"]`);
                if (tabElement) {
                    switchTab(savedTab, tabElement);
                }
            }

            @if($errors->has('current_password') || $errors->has('password'))
                // If password errors exist, switch to security tab
                const securityTab = document.querySelector('[onclick="switchTab(\'security\', this)"]');
                if (securityTab) switchTab('security', securityTab);
            @endif

                // Avatar Image Preview
                const avatarInput = document.getElementById('avatarInput');
            if (avatarInput) {
                avatarInput.addEventListener('change', function (e) {
                    if (e.target.files && e.target.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const previewContainer = document.getElementById('avatarPreviewContainer');
                            const initial = document.getElementById('avatarInitial');

                            previewContainer.style.backgroundImage = `url(${e.target.result})`;
                            previewContainer.style.backgroundSize = 'cover';
                            previewContainer.style.backgroundPosition = 'center';
                            previewContainer.style.color = 'transparent';

                            if (initial) initial.style.display = 'none';
                        }
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            }

            // Logo Image Preview
            const logoInput = document.getElementById('site_logo');
            if (logoInput) {
                logoInput.addEventListener('change', function (e) {
                    if (e.target.files && e.target.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            document.getElementById('logoPreview').src = e.target.result;
                        }
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            }
        });
    </script>
@endpush