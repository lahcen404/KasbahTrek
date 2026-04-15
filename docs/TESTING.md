# Kasbah Trek — Testing & command reference

**Architecture & feature decisions:** see **`docs/PROJECT-CONTEXT.md`**.

**Stripe (payments, env, webhook, code map):** see **`docs/STRIPE.md`**.

Use this checklist whenever you want to **run** or **manually test** the full stack (API, queue, scheduler, emails, booking reminders).

---

## Quick Start (Terminal A/B/C)

Run this from repo root first:

```bash
docker compose up -d
docker compose exec kasbah_app php artisan optimize:clear
docker compose exec kasbah_app php artisan migrate
```

Then keep these terminals open:

- **Terminal A (queue):**

```bash
cd /home/lahcen404/Desktop/KasbahTrek
docker compose exec kasbah_app php artisan queue:work --tries=3 --timeout=120
```

- **Terminal B (scheduler):**

```bash
cd /home/lahcen404/Desktop/KasbahTrek
docker compose exec kasbah_app php artisan schedule:work
```

- **Terminal C (Stripe webhook listener):**

```bash
cd /home/lahcen404/Desktop/KasbahTrek
stripe listen --forward-to http://127.0.0.1:8080/api/stripe/webhook
```

If you already use the older `/api/webhooks/stripe` forward URL, it also works now.

After starting Terminal C, copy the `whsec_...` value to `backend/.env` as `STRIPE_WEBHOOK_SECRET` and run:

```bash
docker compose exec kasbah_app php artisan config:clear
```

---

## 1. Environment quick notes

- **Docker (repo root):** root `.env` drives Compose (`POSTGRES_*`, `PHP_PORT`, `LOCAL_PATH`, etc.).
  **`LOCAL_PATH`** must be the **absolute path to `backend/`** (the folder that contains `artisan` and `public/`).
- **Laravel:** real config lives in **`backend/.env`**. If the app runs **inside** `kasbah_app`, that container usually loads the same env you mount (align DB host with service name **`postgres`** when using Compose).
- **Timezone & reminders:** set `APP_TIMEZONE` (e.g. `Africa/Casablanca`) in `backend/.env`.
- **Mail while testing:** `MAIL_MAILER=log` writes messages to `backend/storage/logs/laravel.log`.

---

## 2. Docker — start everything

From the **repository root**:

```bash
docker compose up --build
```

Typical URLs (see your root `.env` for ports):

| What            | URL (defaults from README)   |
|----------------|------------------------------|
| Angular        | http://localhost:4200        |
| Laravel (Nginx)| http://localhost:8080        |
| pgAdmin        | http://localhost:5050        |

Run **Artisan** inside the PHP container:

```bash
docker compose exec kasbah_app php artisan <command>
```

Example:

```bash
docker compose exec kasbah_app php artisan migrate
```

---

## 3. Backend without Docker (local PHP)

```bash
cd backend
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate
```

Serve (optional; or use Nginx/Docker):

```bash
php artisan serve
```

Default: `http://127.0.0.1:8000` — API routes are under **`/api`**.

---

## 4. Commands to run **while** testing (queue, scheduler, Stripe)

Use **separate terminals** (or tmux panes). Start the API first (**Docker** or **`php artisan serve`**), then:

| What | Command | When you need it |
|------|---------|------------------|
| **Queue worker** | `cd backend && php artisan queue:work` | `QUEUE_CONNECTION` is `database` / `redis` — mails & queued jobs won’t run without it. Use **`queue:work -v`** for more output. |
| **Scheduler** | `cd backend && php artisan schedule:work` | Daily **`bookings:send-reminders`** at **08:00** (`APP_TIMEZONE`). Optional if you only trigger reminders **manually**. |
| **Stripe webhooks (local)** | `stripe listen --forward-to http://localhost:8080/api/stripe/webhook` | **Payment flow:** Checkout sends events to Stripe; Stripe must reach your app. Replace **8080** with **`PHP_PORT`** or use **8000** with `artisan serve`. Copy **`whsec_...`** into **`STRIPE_WEBHOOK_SECRET`** (same terminal output). The legacy `/api/webhooks/stripe` alias is accepted too. |
| **Config reload** | `cd backend && php artisan config:clear` | After any **`.env`** change (Stripe keys, `APP_URL`, mail, queue). |

**Docker** (from repo root, same ideas):

```bash
docker compose exec kasbah_app php artisan queue:work
docker compose exec kasbah_app php artisan schedule:work
docker compose exec kasbah_app php artisan config:clear
```

`stripe listen` runs on the **host** (install [Stripe CLI](https://stripe.com/docs/stripe-cli)); forward to **host:port** that reaches Nginx (e.g. **8080**).

**Quick Stripe sanity check** (with `stripe listen` running):

```bash
stripe trigger checkout.session.completed
```

**Skip the worker** only for quick tests: set **`QUEUE_CONNECTION=sync`** in **`backend/.env`** (not for production).

Full Stripe flow (env, routes, Postman) is in **`docs/STRIPE.md`**.

---

## 5. Database

```bash
php artisan migrate
php artisan migrate:fresh        # destructive: drops all tables, then migrate
php artisan migrate:fresh --seed # same + seeders
php artisan db:seed
```

Inside Docker:

```bash
docker compose exec kasbah_app php artisan migrate
```

---

## 6. Queue worker (required for queued jobs)

Many mails and listeners use the **queue**. If `QUEUE_CONNECTION` is `database` or `redis`, you **must** run a worker or jobs will stay pending.

**Local / host:**

```bash
cd backend
php artisan queue:work
```

**Verbose (see job names):**

```bash
php artisan queue:work -v
```

**Docker:**

```bash
docker compose exec kasbah_app php artisan queue:work
```

**One-off “run until empty” (useful after tests):**

```bash
php artisan queue:work --stop-when-empty
```

**Tip:** For quick local testing only, you can set `QUEUE_CONNECTION=sync` in `backend/.env` so jobs run immediately without a worker (not for production).

---

## 7. Scheduler (cron / daily tasks)

Scheduled tasks are defined in **`backend/routes/console.php`** (e.g. **`bookings:send-reminders`** at **08:00** app timezone).

**Development — keep scheduler running in a terminal:**

```bash
cd backend
php artisan schedule:work
```

**Run due tasks once (manual tick):**

```bash
php artisan schedule:run
```

**Production:** system cron must call Laravel every minute:

```cron
* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
```

**Docker note:** add a long-running `schedule:work` process, or rely on host cron calling `schedule:run` inside the container.

---

## 8. Booking reminder emails

**Normal rule:** command selects **CONFIRMED** bookings whose **tour `date` is tomorrow** (in `APP_TIMEZONE`), with **`reminder_sent_at` = null**.

**Run manually anytime:**

```bash
cd backend
php artisan bookings:send-reminders
```

**Test a specific tour date (ignore “tomorrow”):**

```bash
php artisan bookings:send-reminders --tour-date=2026-04-15
```

Use the exact **`Y-m-d`** stored on the booking. Then ensure **`queue:work`** is running if mail is queued.

---

## 9. Other useful Artisan commands

| Command | Purpose |
|--------|---------|
| `php artisan route:list` | List HTTP routes |
| `php artisan config:clear` | Clear config cache after `.env` changes |
| `php artisan cache:clear` | Clear app cache |
| `php artisan optimize:clear` | Clear config, route, view, cache |
| `php artisan tinker` | REPL for quick DB checks |
| `php artisan inspire` | Sample scheduled-style command (from `routes/console.php`) |

**Logs (Laravel 11+):**

```bash
php artisan pail
```

---

## 10. Automated tests (PHPUnit)

Uses **SQLite in-memory** per `phpunit.xml` (no Postgres needed for tests).

```bash
cd backend
php artisan test
# or
./vendor/bin/phpunit
```

---

## 11. Frontend (Angular)

```bash
cd frontend
npm install
npm start
# same as: ng serve
```

With Docker, the **frontend** service usually runs this for you on port **4200**.

---

## 12. Typical “full manual test” session

1. **Start stack:** **`docker compose up`** (or Postgres + **`php artisan serve`**).
2. **Migrate:** **`php artisan migrate`** (once or after pull).
3. **`php artisan config:clear`** after changing **`backend/.env`**.
4. **Terminal A — queue:** **`php artisan queue:work`** (unless **`QUEUE_CONNECTION=sync`**).
5. **Terminal B — scheduler (optional):** **`php artisan schedule:work`** if you care about the **08:00** reminder; skip if you only run **`bookings:send-reminders`** by hand.
6. **Terminal C — Stripe (payments):** **`stripe listen --forward-to http://localhost:8080/api/stripe/webhook`** — paste **`whsec_...`** into **`STRIPE_WEBHOOK_SECRET`**, then **`config:clear`** again.
7. **API / Postman:** register, login, tours, bookings, **`POST /api/bookings/{id}/checkout`**, open returned **`url`** in a browser, pay with test card **`4242…`**, then guide **CONFIRM** (see **`docs/STRIPE.md`**).
8. **Reminders:** **CONFIRMED** booking with **`date` = tomorrow** (or **`--tour-date`**), **`php artisan bookings:send-reminders`**, check **`storage/logs/laravel.log`** if **`MAIL_MAILER=log`**.

---

## 13. Postman / API base URL

- Docker + Nginx: `http://localhost:<PHP_PORT>/api` (e.g. **8080**).
- `php artisan serve`: `http://127.0.0.1:8000/api`.

Send header: **`Authorization: Bearer <token>`** for protected routes.

---

For UI/Stitch prompt and API field reference, see **`docs/stitch-kasbah-trek-prompt.md`**.

---

## 14. After a testing session (optional)

- Stop **`queue:work`**, **`schedule:work`**, **`stripe listen`** with **Ctrl+C** in each terminal.
- **`docker compose down`** if you used Docker and want to free ports.
- No special “shutdown” is required for Laravel beyond stopping processes.
