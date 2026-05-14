<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title') | {{ \App\Models\Setting::get('site_name', 'HelpTK') }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="icon" type="image/png" href="{{ \App\Models\Setting::getLogoUrl() }}?v=2">
  <link rel="preload" as="image" href="{{ asset('img/HelpTK--C.png') }}">
  @auth
    @if(Auth::user()->avatar)
      <link rel="preload" as="image" href="{{ asset('storage/' . Auth::user()->avatar) }}">
    @endif
  @endauth
  <link rel="stylesheet" href="{{ url('css/layout.css') }}">
  <style>
    :root {
      --primary-color:
        {{ \App\Models\Setting::get('primary_color', '#d4af53') }}
      ;
      --primary-hover: color-mix(in srgb, var(--primary-color), black 10%);
      --primary-light: color-mix(in srgb, var(--primary-color), transparent 90%);
      --sidebar-bg:
        {{ \App\Models\Setting::get('sidebar_bg', '#ffffff') }}
      ;
      --navbar-bg:
        {{ \App\Models\Setting::get('navbar_bg', '#ffffff') }}
      ;
      --sidebar-text:
        {{ \App\Models\Setting::get('sidebar_text', '#6c7380') }}
      ;
      --navbar-text:
        {{ \App\Models\Setting::get('navbar_text', '#6c7380') }}
      ;
      --site-name-color:
        {{ \App\Models\Setting::get('site_name_color', '#1a1a1a') }}
      ;
      --sidebar-separator:
        {{ \App\Models\Setting::get('sidebar_separator', '#e6e9f4') }}
      ;
      --user-name-color:
        {{ \App\Models\Setting::get('user_name_color', '#6c7380') }}
      ;
      --menu-title-color:
        {{ \App\Models\Setting::get('menu_title_color', '#828795') }}
      ;
    }
  </style>
  @stack('styles')
</head>

<body class="{{ Auth::check() && Auth::user()->role != 2 ? 'has-sidebar' : 'no-sidebar' }}">
  @php
    $avatarBase64 = null;
    if (Auth::check() && Auth::user()->avatar) {
      $avatarPath = storage_path('app/public/' . Auth::user()->avatar);
      if (file_exists($avatarPath)) {
        $avatarType = pathinfo($avatarPath, PATHINFO_EXTENSION);
        $avatarData = file_get_contents($avatarPath);
        $avatarBase64 = 'data:image/' . $avatarType . ';base64,' . base64_encode($avatarData);
      }
    }
  @endphp
  <script>
    (function () {
      if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth >= 992) {
        document.body.classList.add('sidebar-collapsed');
      }
    })();
  </script>

  <div class="mobile-sheet-overlay" id="sheetOverlay"></div>
  @auth
  @if(Auth::user()->role != 2)
  <div class="sidebar" id="sidebar">
    <button class="sidebar-pin-toggle d-none d-lg-flex" id="sidebarPinToggle" title="Toggle Sidebar">
      <i class="fa-solid fa-chevron-left"></i>
    </button>
    <a href="{{ Auth::user()->role == 1 ? route('admin.dashboard') : (Auth::user()->role == 0 ? route('agent.dashboard') : route('user.dashboard')) }}"
      class="sidebar-brand" style="text-decoration: none;">
      <img src="{{ \App\Models\Setting::getLogoUrl() }}" alt="Logo" class="sidebar-brand-logo">
      <span class="sidebar-brand-name"
        style="color: var(--site-name-color);">{{ \App\Models\Setting::get('site_name', 'HelpTK') }}</span>
    </a>

    <div class="sidebar-user">
      <div class="user-avatar" style="position: relative; overflow: hidden;">
        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        @if(Auth::user()->avatar)
          <img src="{{ $avatarBase64 ?? asset('storage/' . Auth::user()->avatar) }}" alt="Avatar"
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
        @endif
      </div>
      <div class="user-info">
        <div class="user-info-name">{{ Auth::user()->name }}</div>
        <div class="user-info-role">
          @if(Auth::user()->role == 1) Administrator
          @elseif(Auth::user()->role == 0) Agent
          @else User
          @endif
        </div>
      </div>
    </div>

    <div class="nav-menu">
      @if(Auth::user()->role == 1)
        {{-- Admin Menu --}}
        <div class="nav-label" style="color: var(--menu-title-color); font-size: 12px; font-weight: 600; letter-spacing: 1px; margin: 15px 20px 5px;">Overview</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <i class="fa-solid fa-house"></i><span class="nav-text">Dashboard</span>
        </a>

        <div class="nav-label" style="color: var(--menu-title-color); font-size: 12px; font-weight: 600; letter-spacing: 1px; margin: 15px 20px 5px;">Management</div>
        <a href="{{ route('admin.tickets.index') }}" class="nav-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
          <i class="fa-solid fa-ticket"></i><span class="nav-text">Tickets</span>
        </a>
        <a href="{{ route('admin.messages.index') }}" class="nav-item {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
          <i class="fa-solid fa-envelope"></i><span class="nav-text">Messages</span>
          @php
            $adminId = Auth::id();
            $totalUnread = \Illuminate\Support\Facades\Cache::remember('admin_sidebar_unread_' . $adminId, 60, function () use ($adminId) {
              return \App\Models\Reply::whereNull('admin_id')
                ->where('is_read', 0)
                ->whereIn('ticket_id', function ($query) use ($adminId) {
                  $query->select('id')->from('tickets')
                    ->where('inprogress_by', $adminId)
                    ->orWhere('closed_by', $adminId)
                    ->orWhereNull('inprogress_by')
                    ->orWhereIn('id', function ($sub) use ($adminId) {
                      $sub->select('ticket_id')->from('replies')->where('admin_id', $adminId);
                    });
                })->count();
            });
          @endphp
          <span id="sidebar-messages-badge" class="badge bg-danger rounded-pill ms-auto" style="{{ $totalUnread > 0 ? '' : 'display: none;' }} font-size: 0.7rem;">
            {{ $totalUnread > 99 ? '99+' : ($totalUnread > 0 ? $totalUnread : '') }}
          </span>
        </a>
        <a href="{{ route('admin.knowledge-base.index') }}" class="nav-item {{ request()->routeIs('admin.knowledge-base.*') ? 'active' : '' }}">
          <i class="fa-solid fa-book"></i><span class="nav-text">Knowledge Base</span>
        </a>
        <a href="{{ route('admin.agents.index') }}" class="nav-item {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}">
          <i class="fa-solid fa-users"></i><span class="nav-text">Agents</span>
        </a>
        <a href="{{ route('admin.ranking.index') }}" class="nav-item {{ request()->routeIs('admin.ranking.*') ? 'active' : '' }}">
          <i class="fa-solid fa-chart-line"></i><span class="nav-text">Ranking</span>
        </a>

        <div class="nav-label" style="color: var(--menu-title-color); font-size: 12px; font-weight: 600; letter-spacing: 1px; margin: 15px 20px 5px;">System</div>
        <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
          <i class="fa-solid fa-gear"></i><span class="nav-text">Settings</span>
        </a>
      @elseif(Auth::user()->role == 0)
        {{-- Agent Menu --}}
        <div class="nav-label" style="color: var(--menu-title-color); font-size: 12px; font-weight: 600; letter-spacing: 1px; margin: 15px 20px 5px;">Overview</div>
        <a href="{{ route('agent.dashboard') }}" class="nav-item {{ request()->is('agent/dashboard') ? 'active' : '' }}">
          <i class="fa-solid fa-house"></i><span class="nav-text">Dashboard</span>
        </a>

        <div class="nav-label" style="color: var(--menu-title-color); font-size: 12px; font-weight: 600; letter-spacing: 1px; margin: 15px 20px 5px;">Support</div>
        <a href="{{ route('agent.tickets.create') }}" class="nav-item {{ request()->routeIs('agent.tickets.create') ? 'active' : '' }}">
          <i class="fa-solid fa-plus-circle"></i><span class="nav-text">Create Ticket</span>
        </a>
        <a href="{{ route('agent.messages.index') }}" class="nav-item {{ request()->routeIs('agent.messages.*') ? 'active' : '' }}">
          <i class="fa-solid fa-envelope"></i><span class="nav-text">Messages</span>
          @php
            $userId = Auth::id();
            $userUnread = \Illuminate\Support\Facades\Cache::remember('user_sidebar_unread_' . $userId, 60, function () use ($userId) {
              return \App\Models\Reply::whereNotNull('admin_id')
                ->where('is_read', 0)
                ->whereIn('ticket_id', function ($query) use ($userId) {
                  $query->select('id')->from('tickets')->where('user_id', $userId);
                })->count();
            });
          @endphp
          <span id="sidebar-user-messages-badge" class="badge bg-danger rounded-pill ms-auto" style="{{ $userUnread > 0 ? '' : 'display: none;' }} font-size: 0.7rem;">
            {{ $userUnread > 99 ? '99+' : ($userUnread > 0 ? $userUnread : '') }}
          </span>
        </a>

        <a href="{{ route('knowledge.base') }}" class="nav-item {{ request()->routeIs('knowledge.*') ? 'active' : '' }}">
          <i class="fa-solid fa-book"></i><span class="nav-text">Knowledge Base</span>
        </a>

        <div class="nav-label" style="color: var(--menu-title-color); font-size: 12px; font-weight: 600; letter-spacing: 1px; margin: 15px 20px 5px;">Account</div>
        <a href="{{ route('agent.settings') }}" class="nav-item {{ request()->routeIs('agent.settings') ? 'active' : '' }}">
          <i class="fa-solid fa-gear"></i><span class="nav-text">Settings</span>
        </a>
      @else
        {{-- User Menu --}}
        <div class="nav-label" style="color: var(--menu-title-color); font-size: 12px; font-weight: 600; letter-spacing: 1px; margin: 15px 20px 5px;">Overview</div>
        <a href="{{ route('user.dashboard') }}" class="nav-item {{ request()->is('user/dashboard') ? 'active' : '' }}">
          <i class="fa-solid fa-house"></i><span class="nav-text">Dashboard</span>
        </a>

        <div class="nav-label" style="color: var(--menu-title-color); font-size: 12px; font-weight: 600; letter-spacing: 1px; margin: 15px 20px 5px;">Support</div>
        <a href="{{ route('user.tickets.create') }}" class="nav-item {{ request()->routeIs('user.tickets.create') ? 'active' : '' }}">
          <i class="fa-solid fa-plus-circle"></i><span class="nav-text">Create Ticket</span>
        </a>
      @endif

      <div class="sidebar-accent-container">
        <div class="accent-crown-mini">
          <i class="fa-solid fa-crown"></i>
        </div>
        <div class="geometric-accent">
          <div class="accent-line"></div>
          <div class="accent-line-short"></div>
          <div class="accent-line-dots">
            <div class="accent-dot"></div>
            <div class="accent-dot"></div>
            <div class="accent-dot"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif
  @endauth

  <div class="main-wrapper">
    <header class="navbar">
      <div class="d-flex align-items-center">
        @auth
          @if(Auth::user()->role != 2)
            <nav class="navbar-breadcrumb" aria-label="breadcrumb">
              <a href="{{ Auth::user()->role == 1 ? route('admin.dashboard') : route('agent.dashboard') }}" class="breadcrumb-home">
                <i class="fa-solid fa-house"></i>
                <span>Home</span>
              </a>
              <i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
              <span class="breadcrumb-current">
                @yield('breadcrumb', (Auth::user()->role == 1 ? 'Admin Panel' : 'Agent Interface'))
              </span>
            </nav>
          @else
            {{-- Show brand in navbar for users since there is no sidebar --}}
            <a href="{{ route('user.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none ms-3">
              <img src="{{ \App\Models\Setting::getLogoUrl() }}" alt="Logo" style="height: 32px;">
              <span style="font-weight: 700; font-size: 1.2rem; color: var(--site-name-color);">{{ \App\Models\Setting::get('site_name', 'HelpTK') }}</span>
            </a>
          @endif
        @endauth
      </div>

      <div class="navbar-actions d-flex align-items-center gap-3">
        @auth

          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown"
              data-bs-toggle="dropdown" aria-expanded="false" style="color: inherit;">
              <div class="user-avatar-nav shadow-sm"
                style="position: relative; width: 38px; height: 38px; border-radius: 50%; background: var(--primary-color, #d4af53); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; border: 2px solid #fff; overflow: hidden;">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                @if(Auth::user()->avatar)
                  <img src="{{ $avatarBase64 ?? asset('storage/' . Auth::user()->avatar) }}" alt="Avatar"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                @endif
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown"
              style="min-width: 200px; border-radius: 10px;">
              <li><a class="dropdown-item py-2"
                  href="{{ Auth::user()->role == 1 ? route('admin.settings') : (Auth::user()->role == 0 ? route('agent.settings') : '#') }}"><i
                    class="fa-solid fa-user me-2 text-muted"></i> My Profile</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="dropdown-item py-2 text-danger"><i
                      class="fa-solid fa-right-from-bracket me-2"></i> Logout</button>
                </form>
              </li>
            </ul>
          </div>

          @if(Auth::user()->role != 2)
          <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fa-solid fa-bars"></i>
          </button>
          @endif
        @endauth
      </div>
    </header>

    <main class="content-area">
      @include('layouts._alerts')
      @yield('content')
    </main>

    <footer class="footer-pro">
      <div>&copy; {{ date('Y') }} <span
          style="color: var(--site-name-color, var(--primary-color)); font-weight: 600;">HelpTK</span>.
        All rights
        reserved.</div>
    </footer>
  </div>

  {{-- Global Lightbox Overlay --}}
  <div id="globalLightbox" class="lb-overlay" onclick="closeGlobalLightbox(event)">
    <div class="lb-content">
      <button class="lb-close" onclick="closeGlobalLightbox(event)">&times;</button>
      <img src="" id="globalLbImg" class="lb-img">
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
  <script>
    function openGlobalLightbox(src) {
      const lb = document.getElementById('globalLightbox');
      const img = document.getElementById('globalLbImg');
      img.src = src;
      lb.style.display = 'flex';
      setTimeout(() => lb.classList.add('active'), 10);
      document.body.style.overflow = 'hidden';
    }

    function closeGlobalLightbox(e) {
      if (!e || e.target.classList.contains('lb-overlay') || e.target.classList.contains('lb-close')) {
        const lb = document.getElementById('globalLightbox');
        lb.classList.remove('active');
        setTimeout(() => {
          lb.style.display = 'none';
          document.body.style.overflow = '';
        }, 300);
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      const sidebarToggle = document.getElementById('sidebarToggle');
      const sidebarPinToggle = document.getElementById('sidebarPinToggle');
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sheetOverlay');

      function toggleSidebar() {
        if (window.innerWidth >= 992) {
          document.body.classList.toggle('sidebar-collapsed');
          localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
        } else {
          sidebar.classList.toggle('active');
          overlay.classList.toggle('active');
        }
      }

      if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
      }

      if (sidebarPinToggle) {
        sidebarPinToggle.addEventListener('click', toggleSidebar);
      }

      if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
      }
    });
  </script>
  @stack('scripts')
</body>

</html>