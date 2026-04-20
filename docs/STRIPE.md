# Kasbah Trek — Stripe payments (reference)

Everything below is **Stripe-only**: how it works in this project, what to configure, and where the code lives.

Commands for running and testing the stack (Docker, `stripe listen`, queues) are in **`docs/TESTING.md`**.

---

## 1. Product decision

- **No separate `Payment` table/class** for MVP: **`payment_status`** and **`paid_at`** live on **`bookings`**.
- **Enum:** `App\Enums\PaymentStatus`: `UNPAID`, `PAID`, `FAILED`.
- **Flow:** **Book first** → traveler pays via **Stripe Checkout** → **webhook** marks **PAID** → guide can **CONFIRM** only after payment (otherwise **422**).

---

## 2. Lifecycle (short)

1. Traveler **`POST /api/bookings`** → booking **`PENDING`**, **`payment_status`** default **UNPAID**.
2. Traveler **`POST /api/bookings/{id}/checkout`** (Bearer, **TRAVELER**) → response includes **`url`** (open in browser) and **`session_id`**.
3. User pays on Stripe’s hosted page.
4. Stripe sends **`checkout.session.completed`** to **`POST /api/stripe/webhook`** (no user JWT; verified with **`Stripe-Signature`** + **`STRIPE_WEBHOOK_SECRET`**).
5. Webhook reads **`metadata.booking_id`**, sets **`payment_status = PAID`**, **`paid_at = now()`** (idempotent if already **PAID**).
6. Guide **`PATCH /api/bookings/{id}/status`** with **`CONFIRMED`** only works if **`payment_status === PAID`**.

**Trust:** the **webhook** is the source of truth for “paid”, not the browser redirect after Checkout.

---

## 3. Environment variables (`backend/.env`)

| Variable | Purpose |
|----------|---------|
| `STRIPE_SECRET` | Server-side API key (`sk_test_...` / `sk_live_...`). Never expose to frontend. |
| `STRIPE_KEY` | Publishable key (`pk_test_...`) — for future Stripe.js / mobile; Checkout uses **secret** on server. |
| `STRIPE_WEBHOOK_SECRET` | Signing secret **`whsec_...`** — verifies webhook requests. |
| `STRIPE_CURRENCY` | Lowercase ISO code (e.g. `mad`, `eur`) — must match what your Stripe account supports. |

Optional URL overrides (defaults use `APP_URL`):

- `STRIPE_SUCCESS_URL` — must include `{CHECKOUT_SESSION_ID}` where Stripe expects it.
- `STRIPE_CANCEL_URL`

After changes: **`php artisan config:clear`**.

Template for teammates: **`backend/.env.example`** (Stripe section).

---

## 4. Config file

**`config/stripe.php`** — reads `env()` for keys, currency, success/cancel URLs.

---

## 5. API routes

| Method | Path | Auth |
|--------|------|------|
| `POST` | `/api/bookings/{id}/checkout` | **Bearer**, **TRAVELER**, must own booking |
| `POST` | `/api/stripe/webhook` | **None** (Stripe signature only) |

---

## 6. Code files (backend)

| File | Role |
|------|------|
| `app/Http/Controllers/Api/BookingController.php` | `checkout()` builds Checkout Session; `updateStatus()` blocks **CONFIRM** unless **PAID** |
| `app/Http/Controllers/Api/StripeWebhookController.php` | Verifies webhook, handles `checkout.session.completed` |
| `routes/api.php` | Registers both routes |
| `app/Models/Booking.php` | `payment_status`, `paid_at` fillable + casts |
| `database/migrations/...create_bookings_table.php` | Columns `payment_status`, `paid_at` (if you edited migrations in place) |

**Composer:** `stripe/stripe-php`.

---

## 7. Webhook secret — two ways

| Method | When |
|--------|------|
| **Stripe CLI** `stripe listen --forward-to http://HOST:PORT/api/stripe/webhook` | Local dev; copy **`whsec_...`** from the terminal into **`STRIPE_WEBHOOK_SECRET`**. Restart `listen` → secret may change → update `.env`. |
| **Stripe Dashboard** → Developers → Webhooks | Needs a **public HTTPS** URL (deployed API or **ngrok**). **localhost** is not reachable by Stripe’s servers without a tunnel. |

**Install CLI (Arch):** not in official repos; use **`yay -S stripe-cli`** (AUR) or the **GitHub release** binary.

---

## 8. Class diagram (UML)

You can show **payment fields on `Booking`** only. A separate **`Payment`** class is optional later (multiple charges/refunds).

---

## 9. What’s not in v1

- Automatic **refund** when guide **REJECTS** after payment (optional next step).
- Storing **`stripe_checkout_session_id`** (omitted by design; webhook uses **`metadata.booking_id`**).

---

## 10. Related docs

- **`docs/TESTING.md`** — Docker, `stripe listen`, `stripe trigger`, queues, logs.
