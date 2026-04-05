![alt text](<kasbah trek.png>)
# 📘 Kasbah Trek – World Cup 2030 Tourism Platform

> 🏔️ Mountains, lakes, forests, traditional life — special tours for World Cup 2030 visitors.

---

## 🌍 Project Context

Morocco will host the **World Cup 2030**, attracting millions of visitors.
While most tourists will visit major cities, the **Beni Mellal – Khénifra** region offers unique natural and cultural attractions.

**Kasbah Trek** is a tourism web application that connects:

* 🧳 Travelers
* 🧭 Local Guides
* 🛡️ Admins

The platform ensures safe, verified, and organized tour experiences.

---

## 🎯 Project Objectives

* Promote local tourism during World Cup 2030
* Provide easy booking with real-time availability check
* Enable secure online payments
* Collect reviews and trip reports
* Send automated email notifications
* Provide an admin dashboard

---

## 👥 User Roles

### 🧳 Traveler

* Register / Login / Logout
* Browse & filter tours
* View tour details
* Book & cancel tours
* Add favorites
* Write reviews & ratings
* Report unsafe tours
* Receive email notifications

### 🧭 Guide

* Register / Login / Logout
* Create, update, delete tours
* Upload tour images
* Accept/reject bookings
* View reviews
* Request verification badge
* Receive email notifications

### 🛡️ Admin

* Manage users & guides
* Manage tours & categories
* Approve/reject guide verification
* Review trip reports
* View statistics dashboard

---

## ⚙️ Technologies

| Layer            | Technology                      |
| ---------------- | ------------------------------- |
| Backend          | Laravel (MVC)                   |
| Frontend         | Angular 18 (SCSS)              |
| Database         |  PostgreSQL                     |
| Email            | Laravel Mail (SMTP)             |
| Payment          | Stripe / PayPal                 |
| Web Server       | Nginx                           |
| Containerization | Docker                          |
| Orchestration    | Docker Compose                  |
| Version Control  | Git & GitHub                    |

---

## 📁 Project Structure

* `backend/` → Laravel application source code
* `frontend/` → Angular 18 SPA (dev server on port 4200; proxies `/api` to backend)
* `nginx/` → Nginx configuration
* `Dockerfile` / `docker-compose.yml` → Backend, Frontend (Angular), DB

---

## 🐳 Docker Environment

The project is fully containerized using Docker (backend, frontend, and DB).

### 📦 Services

| Service    | Description                                      | Port (default) |
| --------- | ------------------------------------------------ | -------------- |
| **frontend** | Angular 18 dev server (proxies `/api` → Nginx) | 4200           |
| **kasbah_app** | Laravel (PHP-FPM)                             | —              |
| **nginx** | Web server for Laravel                           | 8080           |
| **postgres** | PostgreSQL                                    | 5432           |
| **pgadmin** | pgAdmin UI                                    | 5050           |

### 🚀 Run with Docker

```bash
# From repo root
docker compose up --build
```

- **Angular app:** http://localhost:4200 (set `ANGULAR_PORT` in `.env` to change)
- **Laravel (via Nginx):** http://localhost:8080
- **pgAdmin:** http://localhost:5050 (see `.env` for `PGADMIN_*`)

The frontend container runs `ng serve` with a proxy so requests to `/api` are forwarded to Nginx (Laravel).

### 🧪 Testing & Artisan commands

Full checklist (queue, scheduler, migrations, reminders, Postman base URLs): **[docs/TESTING.md](docs/TESTING.md)**

---

## 🔥 Core Features

* ✅ Availability Check (Prevent Overbooking)
* 💳 Secure Online Payments (Stripe / PayPal)
* ❤️ Favorites
* ⭐ Reviews & Ratings
* 🚨 Trip Reports
* 🏅 Guide Verification
* 📧 Email Notifications

---

## 📌 Summary

**Kasbah Trek** allows World Cup 2030 visitors to explore the Beni Mellal – Khénifra region through:

* Verified guides
* Safe bookings
* Real-time availability
* Secure online payments
* Reviews and reports

Admin ensures safety, quality, and proper management of tours and guides.

