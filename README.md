![alt text](<kasbah-trek.png>)

# 📘 Kasbah Trek – World Cup 2030 Tourism Platform

> 🏔️ Mountains, lakes, forests, traditional life — special tours for World Cup 2030 visitors.

A comprehensive tourism marketplace platform connecting travelers, local guides, and administrators for the Beni Mellal-Khénifra region in Morocco.

---

## 📋 Table of Contents

- [Project Context](#-project-context)
- [Project Objectives](#-project-objectives)
- [UML Diagrams](#-uml-diagrams)
- [User Roles](#-user-roles)
- [Technologies](#-technologies)
- [Setup & Installation](#-setup--installation)
- [Running Services](#-running-services)
- [Design & Documentation](#-design--documentation)

---

## 🌍 Project Context

Morocco will host **World Cup 2030**. The **Beni Mellal – Khénifra** region offers unique attractions:

- 🏔️ Mountain trails and trekking routes
- 🏞️ Lakes and natural landscapes
- 🌲 Forests and eco-tourism
- 🏘️ Traditional Berber culture

**Kasbah Trek** connects:
- 🧳 **Travelers**: Discover and book tours
- 🧭 **Local Guides**: Create and manage tours
- 🛡️ **Admins**: Moderate content and verify guides

---

## 🎯 Project Objectives

- ✅ Promote local tourism for World Cup 2030
- ✅ Real-time availability checking
- ✅ Secure payments (Stripe & PayPal)
- ✅ Reviews and safety reports
- ✅ Email notifications
- ✅ Guide verification system
- ✅ Admin dashboard
- ✅ Data security

---

## 📐 UML Diagrams

### Class Diagram

![Class Diagram](UMls/Diagram%20Class%20Kasbha%20Trek.png)

Shows: Users, Tours, Bookings, Reviews, Favorites, TripReports, Verifications, Images, Categories and their relationships.

### Use Case Diagram

![Use Case Diagram](UMls/USE%20CASE%20Kasbha%20Trek.png)

Shows: Actor interactions (Traveler, Guide, Admin) and their system features.

---


## �👥 User Roles & Features

### 🧳 Traveler

- **Authentication**: Register / Login / Logout
- **Discovery**: Browse & filter tours by category, difficulty, price, and location
- **Booking**: Book tours with date selection, view booking history, cancel bookings
- **Payment**: Secure checkout with Stripe or PayPal
- **Reviews**: Write and read reviews for tours
- **Safety**: Report unsafe tours or guides to admins
- **Favorites**: Add/remove tours to favorite list
- **Notifications**: Receive email updates on bookings, payments, and reports

### 🧭 Guide

- **Authentication**: Register / Login / Logout
- **Tours**: Create, update, delete tours with images and detailed information
- **Bookings**: View booking requests, accept/reject bookings
- **Reviews**: View and respond to reviews from travelers
- **Verification**: Request guide badge verification
- **Management**: View active bookings, upcoming tours, and performance metrics
- **Notifications**: Receive email updates on booking decisions and verifications

### 🛡️ Admin

- **User Management**: View, edit, and manage all users
- **Guide Verification**: Review and approve/reject guide verification requests
- **Tours**: View, edit, and delete tours across the platform
- **Categories**: Manage tour categories
- **Trip Reports**: Review safety reports and update status
- **Statistics**: View platform dashboard with key metrics
- **Moderation**: Manage platform content and enforce policies

---

## ⚙️ Technologies

| Backend | Frontend | Infrastructure |
|---------|----------|-----------------|
| Laravel 11 (PHP) | Vue 3 + TypeScript | Docker & Docker Compose |
| PostgreSQL | Tailwind CSS | Nginx |
| Eloquent ORM | Vite | Git & GitHub |
| Stripe & PayPal APIs | Axios | Linux |

---

## 🐳 Setup & Installation

### Quick Start (Docker)

```bash
git clone https://github.com/lahcen404/KasbahTrek
cd KasbahTrek
cp env.example .env
docker compose up -d

# Access
# Frontend: http://localhost:5173
# Backend: http://localhost:8080/api
```

### Local Development

**Backend:**
```bash
cd backend
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

**Frontend:**
```bash
cd frontend
npm install
npm run dev
```

---

## 🚀 Running Services


### Queue Worker (Async Email)

Handles email notifications for bookings, verifications, reports:

```bash
cd backend
php artisan queue:work

# Docker:
docker exec kasbah_backend php artisan queue:work
```

**Queued Events:**
- `BookingStatusUpdated` → Sends booking status emails
- `VerificationStatusUpdated` → Sends verification emails
- `TripReportStatusUpdated` → Sends report status emails
- Payment receipts and confirmations

### Schedule (Cron Jobs)

Send booking reminders 24 hours before tours:

```bash
cd backend
php artisan schedule:run
```

**Scheduled Tasks:**
- `bookings:send-reminders` → 24-hour booking reminders

### Stripe Integration

**Setup:**
1. Stripe Dashboard → Developers → Webhooks
2. Endpoint: `https://yourdomain.com/api/stripe/webhook`
3. Events: `checkout.session.completed`, `charge.refunded`
4. Copy secret to `.env`

**Environment Variables:**
```env
STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_SECRET_KEY=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

**Webhook Endpoint:**
- `POST /api/stripe/webhook`

### PayPal Integration

**Setup:**
1. PayPal Dashboard → Apps & Credentials
2. Webhooks URL: `https://yourdomain.com/api/paypal/webhook`
3. Events: `CHECKOUT.ORDER.COMPLETED`

**Environment Variables:**
```env
PAYPAL_MODE=live|sandbox
PAYPAL_CLIENT_ID=...
PAYPAL_SECRET=...
PAYPAL_WEBHOOK_ID=...
```

**Webhook Endpoints:**
- `POST /api/bookings/{id}/paypal/checkout` - Create order
- `POST /api/paypal/webhook` - Handle payment capture

### Email Configuration

**SMTP Setup:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kasbah-trek.com
```

**Email Templates:**
- `backend/app/Mail/` - Mail classes
- `backend/resources/views/emails/` - Templates

---

## 🎨 Design & Documentation

### Figma Design System
🔗 **[View on Figma](https://www.figma.com/design/MMzGppBjCyC0Q7dkaezywd/Kasbah-Trek?node-id=0-1&t=S5XNOOfNbxmLTYsw-1)**

### Postman API Collection
🔗 **[View on Postman](https://yguhijopl.postman.co/workspace/Lahcen-Workspace~1eb868fb-6d1e-4a06-bc89-fca36f6d4f58/collection/41299916-2a964ae7-400d-402a-996f-03972f01d498?action=share&creator=41299916)**

### Technical Documentation
- **[PROJECT_TECHNICAL_AUDIT.md](docs/PROJECT_TECHNICAL_AUDIT.md)** - Architecture & code inventory
- **[STRIPE.md](docs/STRIPE.md)** - Stripe integration guide
- **[TESTING.md](docs/TESTING.md)** - Testing strategies

---

## 🏗️ Architecture

```
Frontend (Vue 3)
    ↓ HTTP/REST
Nginx (Reverse Proxy)
    ↓
Backend (Laravel)
├── Controllers ► Repositories ► Services
├── Events/Listeners (Async)
└── External (Stripe, PayPal, SMTP)
    ↓
PostgreSQL
```

---

## 🔐 Security

- ✅ Custom bearer token authentication
- ✅ Role-based access control
- ✅ Stripe webhook signature validation
- ✅ PayPal signature verification
- ✅ Hashed passwords
- ✅ Environment variable secrets
- ✅ Input validation (FormRequest)
- ✅ HTTPS enforcement (production)

---

## 📊 System Statistics

- **Models**: 9 entities
- **API Endpoints**: 50+
- **Controllers**: 12+
- **Vue Components**: 20+
- **Email Templates**: 6+
- **Integrations**: Stripe, PayPal, SMTP

---

## 📄 License

Proprietary and confidential.
