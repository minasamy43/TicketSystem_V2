<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Ticket System')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="icon" type="image/png" href="{{ asset('img/HelpTK--.png') }}?v=2">
  <link rel="preload" as="image" href="{{ asset('img/HelpTK-.png') }}">
  <link rel="stylesheet" href="{{ url('css/layout.css') }}">
  @stack('styles')
</head>

<body>
  <script>
    (function() {
      if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth >= 992) {
        document.body.classList.add('sidebar-collapsed');
      }
    })();
  </script>

  <div class="mobile-sheet-overlay" id="sheetOverlay"></div>
  <!-- New Sidebar -->
  <div class="sidebar" id="sidebar">
    <button class="sidebar-pin-toggle d-none d-lg-flex" id="sidebarPinToggle" title="Toggle Sidebar">
      <i class="fa-solid fa-chevron-left"></i>
    </button>
    @auth
      <div class="sidebar-user">
        <div class="user-avatar">
          {{ strtoupper(substr(Auth::user()->name, 0, 1)) }} 
        </div>
        <div class="user-info">
          <div class="user-info-name">{{ Auth::user()->name }}</div>
          <div class="user-info-role">{{ Auth::user()->role == 1 ? 'Administrator' : 'User Account' }}</div>
        </div>
      </div>
    @endauth

    <div class="nav-menu">
      @auth
        @if(Auth::user()->role == 1)
          <a href="{{ route('admin.dashboard') }}"
            class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i><span class="nav-text">Dashboard</span>
          </a>
          <a href="{{ route('admin.tickets.index') }}"
            class="nav-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
            <i class="fa-solid fa-ticket"></i><span class="nav-text">Tickets</span>
          </a>
          <a href="{{ route('admin.messages.index') }}"
            class="nav-item {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
            <i class="fa-solid fa-envelope"></i><span class="nav-text">Messages</span>
            @php
              $adminId = Auth::id();
              $totalUnread = \App\Models\Reply::whereNull('admin_id')
                ->where('is_read', 0)
                ->whereIn('ticket_id', function($query) use ($adminId) {
                  $query->select('id')->from('tickets')
                    ->where('inprogress_by', $adminId)
                    ->orWhere('closed_by', $adminId)
                    ->orWhereNull('inprogress_by')
                    ->orWhereIn('id', function($sub) use ($adminId) {
                        $sub->select('ticket_id')->from('replies')->where('admin_id', $adminId);
                    });
                })->count();
            @endphp
            <span id="sidebar-messages-badge" class="badge bg-danger rounded-pill ms-auto" 
              style="{{ $totalUnread > 0 ? '' : 'display: none;' }} font-size: 0.7rem;">
              {{ $totalUnread > 99 ? '99+' : ($totalUnread > 0 ? $totalUnread : '') }}
            </span>
          </a>
          <a href="{{ route('admin.knowledge-base.index') }}"
            class="nav-item {{ request()->routeIs('admin.knowledge-base.*') ? 'active' : '' }}">
            <i class="fa-solid fa-book"></i><span class="nav-text">Knowledge Base</span>
          </a>
          <a href="{{ route('admin.users.index') }}"
            class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i><span class="nav-text">Users</span>
          </a>
          <a href="{{ route('admin.ranking.index') }}"
            class="nav-item {{ request()->routeIs('admin.ranking.*') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-line"></i><span class="nav-text">Ranking</span>
          </a>
         
        @else
          <div class="nav-label" style="color: var(--gray-dark); font-size: 12px; font-weight: 600; margin: 10px 20px;">
            User Panel</div>
          <a href="{{ route('user.dashboard') }}"
            class="nav-item {{ request()->routeIs('user.dashboard') || request()->routeIs('tickets.show') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i><span class="nav-text">Dashboard</span>
          </a>
          <a href="{{ route('tickets.create') }}"
            class="nav-item {{ request()->routeIs('tickets.create') ? 'active' : '' }}">
            <i class="fa-solid fa-plus"></i><span class="nav-text">Create Ticket</span>
          </a>
        @endif
      @endauth

      <form method="POST" action="{{ route('logout') }}" id="logout-form">
        @csrf
        <button type="submit" class="nav-item"
          style="width: 100%; border: none; background: transparent; cursor: pointer; text-align: left;">
          <i class="fa-solid fa-right-from-bracket"></i><span class="nav-text">Logout</span>
        </button>
      </form>

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
        <div
          style="text-align: center; margin-top: 10px; font-family: 'Playfair Display', serif; font-size: 0.65rem; color: #d4af53; opacity: 0.6; letter-spacing: 2px;">
          HELPTK
        </div>
      </div>
    </div>
  </div>

  <div class="main-wrapper">
    <header class="navbar">
      <a href="{{ Auth::user() && Auth::user()->role == 1 ? route('admin.dashboard') : route('user.dashboard') }}"
        class="navbar-brand">
        <img src="{{ asset('img/HelpTK--C.png') }}" alt="Logo">
        <span>Ticket System</span>
      </a>

      <div class="navbar-actions d-flex align-items-center gap-2">

        <button class="sidebar-toggle" id="sidebarToggle">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </header>

    <main class="content-area">
      @yield('content')
    </main>

    <footer class="footer-pro">
      <div>&copy; {{ date('Y') }} <span style="color: var(--primary-color); font-weight: 600;">HelpTK</span>. All rights
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
</body>

</html>