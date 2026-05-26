# WebSocket setup (admin & agent realtime)

This project uses **Laravel Reverb** (PHP WebSocket server). No Node.js or Docker required.

## Run locally (two terminals)

**Terminal 1 — WebSocket server:**

```bash
php artisan reverb:start
```

**Terminal 2 — Laravel app:**

```bash
php artisan serve
```

Or use npm:

```bash
npm run websocket
```

## Required `.env` settings

After `php artisan reverb:install`, you should have:

```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

If you see `cURL error 7 ... port 6001`, you are still on old **Soketi/Pusher** settings. Either:

- Run `php artisan reverb:install` and use port **8080**, or  
- Set `BROADCAST_DRIVER=reverb` and run `php artisan config:clear`

## Verify it works

1. `php artisan reverb:start` shows “Reverb server started”.
2. Log in as admin or agent and open the dashboard.
3. In DevTools → Network → **WS**, you should see a connection to `localhost:8080`.

Tickets and replies still save if Reverb is stopped; live UI updates need Reverb running.
