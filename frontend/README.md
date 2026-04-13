# Kasbah Trek – Frontend (Vue 3 + Vite)

Vue 3 SPA (Vite) for the Kasbah Trek tourism platform. Talks to the Laravel backend in `../backend`.

## Setup

```bash
npm install
```

## Development

**Local (no Docker):**

```bash
npm run dev
```

App runs at `http://localhost:5173/`. API requests to `/api/*` are proxied to the Laravel backend (default `http://localhost:8080`) via `vite.config.ts`.

**With Docker:**

From the repo root:

```bash
docker compose up frontend
```

Vue runs in the `kasbah_frontend` container; app at `http://localhost:5173` (or `FRONTEND_PORT`). In Docker, `/api` is proxied to the `nginx` service.

## Build

```bash
npm run build
```

Output is in `dist/`.
