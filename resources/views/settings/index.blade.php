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
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
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
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
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
    .btn-gold {
        background-color: var(--primary-color, #d4af53);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-gold:hover {
        background-color: var(--primary-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px var(--primary-light);
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
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
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
                <div class="px-4 mb-3 text-uppercase text-muted" style="font-size: 0.8rem; font-weight: 700; letter-spacing: 1px;">Personal</div>
                
                <div class="settings-nav-item active" onclick="switchTab('profile', this)">
                    <i class="fa-solid fa-user"></i> My Profile
                </div>
                <div class="settings-nav-item" onclick="switchTab('security', this)">
                    <i class="fa-solid fa-shield-halved"></i> Security
                </div>
                
                @if(Auth::user()->role == 1)
                <div class="px-4 mt-4 mb-3 text-uppercase text-muted" style="font-size: 0.8rem; font-weight: 700; letter-spacing: 1px;">System</div>
                
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
                    <h3 class="settings-section-title"><i class="fa-solid fa-user text-muted"></i> Profile Information</h3>
                    
                    <form action="{{ Auth::user()->role == 1 ? route('admin.settings.profile') : route('user.settings.profile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Profile Photo</label>
                                <div class="avatar-upload">
                                    <div class="avatar-preview" id="avatarPreviewContainer" style="{{ $user->avatar ? 'background-image: url('.asset('storage/'.$user->avatar).'); background-size: cover; background-position: center; color: transparent;' : '' }}">
                                        <span id="avatarInitial" style="{{ $user->avatar ? 'display: none;' : '' }}">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <div class="avatar-edit">
                                        <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;">
                                        <button type="button" class="btn" title="Upload new photo" onclick="document.getElementById('avatarInput').click()">
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
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
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
                    <h3 class="settings-section-title"><i class="fa-solid fa-shield-halved text-muted"></i> Security & Password</h3>
                    
                    <form action="{{ Auth::user()->role == 1 ? route('admin.settings.password') : route('user.settings.password') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="current_password" class="form-label fw-semibold">Current Password</label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                                @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">New Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
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
                    <h3 class="settings-section-title"><i class="fa-solid fa-sliders text-muted"></i> System Preferences</h3>
                    
                    <form action="{{ route('admin.settings.preferences') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="restore_logo" id="restore_logo" value="0">
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">System Logo</label>
                                <div class="d-flex align-items-center gap-4">
                                    <div class="p-3 border rounded bg-light text-center" style="width: 150px;">
                                        <img id="logoPreview" src="{{ \App\Models\Setting::get('site_logo') ? asset('storage/'.\App\Models\Setting::get('site_logo')) : asset('img/HelpTK--C.png') }}" alt="Logo" style="max-height: 40px; max-width: 100%;">
                                    </div>
                                    <div>
                                        <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/png, image/jpeg, image/svg+xml">
                                        <div class="form-text mt-2">Recommended size: 200x50px. Allowed formats: PNG, JPG, SVG.</div>
                                        @error('site_logo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="site_name" class="form-label fw-semibold">System Name</label>
                                <input type="text" class="form-control @error('site_name') is-invalid @enderror" id="site_name" name="site_name" value="{{ old('site_name', \App\Models\Setting::get('site_name', 'HelpTK')) }}" required>
                                <div class="form-text">This appears in the top of the sidebar.</div>
                                @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="primary_color" class="form-label fw-semibold">Primary Theme Color</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="{{ old('primary_color', \App\Models\Setting::get('primary_color', '#d4af53')) }}" title="Choose your color" required style="width: 60px; height: 45px; padding: 5px;">
                                    <div class="form-text mb-0">Select the main accent color (Gold by default).</div>
                                </div>
                                @error('primary_color') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="sidebar_bg" class="form-label fw-semibold">Sidebar Background Color</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" class="form-control form-control-color" id="sidebar_bg" name="sidebar_bg" value="{{ old('sidebar_bg', \App\Models\Setting::get('sidebar_bg', '#ffffff')) }}" title="Choose your color" required style="width: 60px; height: 45px; padding: 5px;">
                                    <div class="form-text mb-0">Color of the left navigation panel.</div>
                                </div>
                                @error('sidebar_bg') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="navbar_bg" class="form-label fw-semibold">Navbar Background Color</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" class="form-control form-control-color" id="navbar_bg" name="navbar_bg" value="{{ old('navbar_bg', \App\Models\Setting::get('navbar_bg', '#ffffff')) }}" title="Choose your color" required style="width: 60px; height: 45px; padding: 5px;">
                                    <div class="form-text mb-0">Color of the top navigation bar.</div>
                                </div>
                                @error('navbar_bg') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="sidebar_text" class="form-label fw-semibold">Sidebar Text Color</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" class="form-control form-control-color" id="sidebar_text" name="sidebar_text" value="{{ old('sidebar_text', \App\Models\Setting::get('sidebar_text', '#6c7380')) }}" title="Choose your color" required style="width: 60px; height: 45px; padding: 5px;">
                                    <div class="form-text mb-0">Color of the text and icons in the sidebar.</div>
                                </div>
                                @error('sidebar_text') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="navbar_text" class="form-label fw-semibold">Navbar Text Color</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" class="form-control form-control-color" id="navbar_text" name="navbar_text" value="{{ old('navbar_text', \App\Models\Setting::get('navbar_text', '#6c7380')) }}" title="Choose your color" required style="width: 60px; height: 45px; padding: 5px;">
                                    <div class="form-text mb-0">Color of the text and icons in the top navbar.</div>
                                </div>
                                @error('navbar_text') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top text-end">
                            <button type="button" class="btn btn-outline-secondary px-4 me-2" onclick="document.getElementById('site_name').value='HelpTK'; document.getElementById('primary_color').value='#d4af53'; document.getElementById('sidebar_bg').value='#ffffff'; document.getElementById('navbar_bg').value='#ffffff'; document.getElementById('sidebar_text').value='#6c7380'; document.getElementById('navbar_text').value='#6c7380'; document.getElementById('restore_logo').value='1'; document.getElementById('site_logo').value=''; document.getElementById('logoPreview').src='{{ asset('img/HelpTK--C.png') }}';">Restore Defaults</button>
                            <button type="submit" class="btn btn-gold px-4">Save Preferences</button>
                        </div>
                    </form>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
    document.addEventListener('DOMContentLoaded', function() {
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
            if(securityTab) switchTab('security', securityTab);
        @endif
        
        // Avatar Image Preview
        const avatarInput = document.getElementById('avatarInput');
        if (avatarInput) {
            avatarInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
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
            logoInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('logoPreview').src = e.target.result;
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
    });
</script>
@endpush
