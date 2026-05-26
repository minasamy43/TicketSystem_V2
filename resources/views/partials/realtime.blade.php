@if(auth()->check() && in_array((int) auth()->user()->role, [0, 1, 2], true))
    @php
        $connection = config('broadcasting.connections.' . config('broadcasting.default'));
        $wsKey = $connection['key'] ?? '';
        $wsHost = $connection['options']['host'] ?? '127.0.0.1';
        $wsPort = (int) ($connection['options']['port'] ?? 8080);
        $wsScheme = $connection['options']['scheme'] ?? 'http';
    @endphp
    <script src="https://js.pusher.com/8.4.0-rc2/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.19.0/dist/echo.iife.js"></script>
    <script>
        window.RealtimeConfig = {
            key: @json($wsKey),
            host: @json($wsHost),
            port: {{ $wsPort }},
            scheme: @json($wsScheme),
            authEndpoint: @json(url('/broadcasting/auth')),
            csrfToken: @json(csrf_token()),
            role: {{ (int) auth()->user()->role }},
            userId: {{ (int) auth()->id() }},
        };
    </script>
    <script src="{{ asset('js/realtime-handlers.js') }}"></script>
    <script src="{{ asset('js/realtime-echo.js') }}"></script>
@endif
