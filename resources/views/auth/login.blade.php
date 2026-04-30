<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ \App\Models\Setting::get('site_logo') ? asset('storage/' . \App\Models\Setting::get('site_logo')) : asset('img/HelpTK--.png') }}">
    <link rel="stylesheet" href="{{ url('css/login.css') }}">
    <style>
        :root {
            --primary-color: {{ \App\Models\Setting::get('primary_color', '#d4af53') }};
            --primary-hover: color-mix(in srgb, var(--primary-color), black 10%);
            --primary-light: color-mix(in srgb, var(--primary-color), transparent 90%);
        }
    </style>
</head>
<body>

<div class="glow"></div>

<div class="card">
    <div class="gold-bar"></div>
    <div class="card-inner">

        <div class="header">
            <div class="logo-wrap" style="width: auto; height: auto;">
                <img src="{{ \App\Models\Setting::get('site_logo') ? asset('storage/' . \App\Models\Setting::get('site_logo')) : asset('img/HelpTK-.png') }}" alt="HelpTK" style="height: 120px; width: auto; object-fit: contain;" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                <span style="display:none">HTK</span>
            </div>
            <h1>Support Ticket System</h1>
            <p>Welcome back — sign in to continue</p>
        </div>

        <div class="divider">
            <div class="divider-line"></div>
            <span>HelpTK</span>
            <div class="divider-line"></div>
        </div>

        @if ($errors->any())
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <div class="field">
                <label for="email">Email Address</label>
                <div class="field-wrap">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" id="email" name="email"
                        value="{{ old('email') }}" placeholder="you@example.com"
                        class="{{ $errors->has('email') ? 'is-error' : '' }}"
                        required autofocus autocomplete="email">
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="field-wrap">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password"
                        placeholder="••••••••"
                        class="{{ $errors->has('password') ? 'is-error' : '' }}"
                        required autocomplete="current-password">
                    <button type="button" class="toggle-pwd" id="togglePwd">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>


            <button type="submit" class="btn-submit" id="submitBtn">
                <i class="fas fa-right-to-bracket"></i> Sign In
            </button>
        </form>

        <p class="footer-note">HelpTK &copy; {{ date('Y') }} &nbsp;·&nbsp; <strong>Ticket System</strong></p>
    </div>
</div>

<script>
    document.getElementById('togglePwd').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('toggleIcon');
        const show  = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in…';
    });
</script>
</body>
</html>
