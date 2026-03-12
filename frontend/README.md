# Kasbah Trek – Frontend (Angular 18)

Angular v18 SPA for the Kasbah Trek tourism platform. Talks to the Laravel backend in `../backend`.

## Setup

```bash
npm install
```

## Development

**Local (no Docker):**

```bash
npm start
# or: ng serve
```

App runs at `http://localhost:4200/`. API requests to `/api/*` are proxied to the Laravel backend (default `http://localhost:8080`). Adjust `proxy.conf.json` if your backend runs on another host/port.

**With Docker:**

From the repo root:

```bash
docker compose up frontend
```

Angular runs in the `kasbah_frontend` container; app at `http://localhost:4200` (or `ANGULAR_PORT`). The container uses `proxy.docker.conf.json` so `/api` is forwarded to the Nginx (Laravel) service. Ensure backend and nginx are running (e.g. `docker compose up -d`).

## Build

```bash
npm run build
```

Output is in `dist/kasbah-trek-frontend/`.

## Tests

```bash
npm test
```

## Angular CLI

- Generate component: `ng generate component <name>`
- Generate service: `ng generate service <name>`
- More: `ng help` or [Angular CLI](https://angular.dev/tools/cli)
