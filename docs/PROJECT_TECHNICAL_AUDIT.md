# Kasbah Trek - Comprehensive Technical Audit

## 1. Audit Metadata
- Project: `Kasbah Trek`
- Scope: Full-stack audit (Laravel backend + Vue 3 frontend)
- Date: 2026-04-18
- Branch context: `ResolveProject`
- Objective: Produce a senior-level ownership handover reference, including architecture, code structure, risks, and alignment with UML/cahier/use-case diagram.

## 2. Executive Domain Overview
### 2.1 Business Purpose
Kasbah Trek is a tourism marketplace/workflow platform focused on the Beni Mellal-Khenifra region for World Cup 2030 visitors. It connects:
- Travelers: discover and book tours, review/report tours, and manage favorites.
- Guides: create/manage tours, process bookings, and request verification.
- Admins: moderate users/content, process guide verification and trip reports, and monitor platform metrics.

### 2.2 Core Domain Flows
- Discovery: traveler searches/filter tours.
- Booking: traveler submits booking -> payment by Stripe/PayPal -> guide confirms/rejects.
- Moderation/safety: traveler reports -> admin reviews and updates status.
- Trust: guide verification requests -> admin approves/rejects -> guide badge state updates.
- Communication: email notifications for booking/report/verification updates + reminders.

## 3. High-Level Architecture
### 3.1 Backend Style
- Architecture: layered monolith.
- Main pattern composition:
  - MVC (controllers + Eloquent models)
  - Repository pattern via interfaces and concrete repositories
  - Service layer for payment logic (`BookingPaymentService`, `PayPalService`)
  - Event/listener pattern for async-ish notification flow
  - FormRequest validation pattern

### 3.2 Frontend Style
- SPA with Vue 3 + Vue Router + Axios API modules.
- Role-based route guards and page segregation by actor (traveler/guide/admin).
- Local component state + composables (no Pinia/Vuex).

### 3.3 Deployment Shape
- Monorepo style workspace with separate backend and frontend folders.
- Backend APIs consumed by frontend via Axios client.
- Webhook endpoints for Stripe/PayPal integrated into backend API surface.

## 4. Technology Stack
- Backend: Laravel (PHP), Eloquent ORM, events/listeners, queue-capable mail.
- Frontend: Vue 3, TypeScript, Vue Router, Tailwind CSS, Vite.
- Database: PostgreSQL.
- Payments: Stripe Checkout + webhooks, PayPal order/capture + webhooks.
- Auth token mechanism: custom bearer-token table (`personal_access_tokens`) with hash lookup.
- VCS: Git/GitHub.

## 5. Backend Architecture - File-by-File Inventory

### 5.1 Models (`backend/app/Models/*.php`)
- `backend/app/Models/User.php`
  - Purpose: single account entity for all roles.
  - Fillable: `fullname`, `email`, `password`, `role`, `is_verified`.
  - Hidden: `password`, `remember_token`.
  - Casts: `role -> UserRole`, `is_verified -> boolean`, `password -> hashed`.
  - Relations:
    - Guide-side: `tours`, `verificationRequest`, `receivedBookings`
    - Traveler-side: `bookings`, `reviews`, `favorites`, `reports`
    - Admin-side: `approvedVerifications`, `managedReports`

- `backend/app/Models/Tour.php`
  - Fillable: `title`, `description`, `location`, `price`, `difficulty`, `max_spots`, `duration_hours`, `date`, `current_bookings`, `guide_id`, `category_id`.
  - Casts: `difficulty -> DifficultyLevel`, `price -> float`, `duration_hours -> integer`, `date -> date`.
  - Relations: `guide`, `category`, `images`, `bookings`, `reviews`, `favorites`, `tripReports`.

- `backend/app/Models/Booking.php`
  - Fillable: `date`, `total_price`, `status`, `traveler_id`, `tour_id`, `guide_id`, `reminder_sent_at`, `payment_status`, `paid_at`, `payment_receipt_sent_at`, `paypal_order_id`.
  - Casts: status/payment enums + date/datetime fields.
  - Relations: `traveler`, `tour`, `guide`.

- `backend/app/Models/Category.php`
  - Basic category entity (name/description) linked to tours.

- `backend/app/Models/Image.php`
  - Fillable: `path`, `tour_id`.
  - Relation: belongs to `Tour`.

- `backend/app/Models/Review.php`
  - Fillable: `rating`, `comment`, `traveler_id`, `tour_id`.
  - Relations: `traveler`, `tour`.

- `backend/app/Models/Favorite.php`
  - Fillable: `traveler_id`, `tour_id`.
  - Relations: `traveler`, `tour`.
  - Important: implemented as association entity (join-like model), not implicit many-to-many pivot helper.

- `backend/app/Models/TripReport.php`
  - Fillable: `reason`, `status`, `traveler_id`, `tour_id`, `admin_id`.
  - Cast: `status -> Status` enum.
  - Relations: `traveler`, `tour`, `admin`.

- `backend/app/Models/Verification.php`
  - Fillable: `file_url`, `status`, `guide_id`, `admin_id`.
  - Cast: `status -> Status`.
  - Relations: `guide`, `admin`.
  - Note: model expects `admin_id`, but migration currently does not create it.

### 5.2 Enums (`backend/app/Enums/*.php`)
- `UserRole`: `TRAVELER`, `GUIDE`, `ADMIN`.
- `BookingStatus`: `PENDING`, `CONFIRMED`, `REJECTED`, `CANCELLED`.
- `PaymentStatus`: `UNPAID`, `PAID`, `FAILED`.
- `DifficultyLevel`: `EASY`, `MEDIUM`, `HARD`.
- `Status`: `PENDING`, `APPROVED`, `REJECTED`.

### 5.3 Controllers (`backend/app/Http/Controllers/Api/**/*.php`)
- Auth:
  - `AuthController.php`: register/login/me/logout.
- Public domain:
  - `TourController.php`: list/show tours + guide CRUD + image upload/delete.
  - `CategoryController.php`: public list.
  - `BookingController.php`: traveler booking lifecycle + guide status updates + Stripe/PayPal checkout/capture.
  - `ReviewController.php`: traveler create/update/delete + guide/public retrieval.
  - `FavoriteController.php`: add/list/remove favorites.
  - `TripReportController.php`: traveler create/list reports.
  - `VerificationController.php`: guide submit request; admin review/update.
- Payment webhooks:
  - `StripeWebhookController.php`: signature validation and booking payment marking.
  - `PayPalWebhookController.php`: signature validation and booking payment marking.
- Admin:
  - `Admin/StatisticController.php`: dashboard metrics.
  - `Admin/UserController.php`: user management.
  - `Admin/TourController.php`: global tour management.
  - `Admin/CategoryController.php`: category CRUD.
  - `Admin/TripReportController.php`: report moderation.

### 5.4 Routes (`backend/routes/*.php`)
- `backend/routes/api.php`
  - Defines public, authenticated, and role-scoped groups (`role:GUIDE`, `role:TRAVELER`, `role:ADMIN`).
  - Registers webhooks: `/stripe/webhook`, `/webhooks/stripe` alias, `/paypal/webhook`.
  - Includes admin prefix group `/admin/*`.
- `backend/routes/console.php`
  - Schedules `bookings:send-reminders` daily.
- `backend/routes/web.php`
  - Minimal web routes (API-first app).

### 5.5 Form Requests (`backend/app/Http/Requests/**/*.php`)
- Auth: `LoginRequest`, `RegisterRequest`.
- Tour: `StoreTourRequest`, `UpdateTourRequest`, `UploadTourImagesRequest`.
- Booking: `StoreBookingRequest`, `UpdateBookingStatusRequest`.
- Review: `StoreReviewRequest`, `UpdateReviewRequest`.
- Favorite: `StoreFavoriteRequest`.
- Verification: `StoreVerificationRequest`, `UpdateVerificationStatusRequest`.
- TripReport: `StoreTripReportRequest`.
- Category/Admin: category create/update, admin user update.

### 5.6 Middleware (`backend/app/Http/Middleware/*.php`)
- `CustomApiAuth.php`: custom bearer-token authentication against personal_access_tokens table.
- `CheckRole.php`: role authorization gate by middleware parameter.

### 5.7 Repositories (`backend/app/Repositories/*.php`)
- `AuthRepository.php`: credential lookup + token generation/storage + logout token revoke.
- `TourRepository.php`: filtered query construction, CRUD, guide-specific retrieval, image management.
- `BookingRepository.php`: transactional booking create/update/cancel; capacity/date enforcement.
- `ReviewRepository.php`: review ownership and eligibility behavior.
- `FavoriteRepository.php`: idempotent favorite add/remove/list behavior.
- `TripReportRepository.php`: create/list/update status with admin attribution.
- `VerificationRepository.php`: request creation, pending retrieval, status change + `users.is_verified` sync.
- `CategoryRepository.php`: CRUD.
- `AdminUserRepository.php`: administrative user operations.
- `AdminStatisticRepository.php`: aggregate counters.

### 5.8 Interfaces (`backend/app/Interfaces/*.php`)
Repository interfaces map 1:1 with concrete repositories and are bound in provider.

### 5.9 Services (`backend/app/Services/*.php`)
- `BookingPaymentService.php`
  - `markPaidAndSendReceipt()`
  - `sendReceiptIfNeeded()`
  - Centralized paid-state and receipt behavior.
- `PayPalService.php`
  - OAuth token retrieval, order creation/capture, webhook signature verification.

### 5.10 Events, Listeners, Mail, Command
- Events: `BookingStatusUpdated`, `TripReportStatusUpdated`, `VerificationStatusUpdated`.
- Listeners: send respective mail notifications (queue-capable via `ShouldQueue`).
- Mail: booking status, report status, verification status, payment receipt, reminders.
- Command: `SendBookingReminders` scheduled daily.

## 6. Backend Request Flow by Critical Feature
### 6.1 Booking Creation
1. Frontend sends `POST /api/bookings` with `tour_id`, `date`.
2. `StoreBookingRequest` validates structure.
3. `BookingRepository.create()` transaction:
   - lock tour row
   - verify authenticated traveler
   - ensure booking date equals tour date and tour date not past
   - prevent duplicate active booking for same traveler/tour/date
   - enforce `current_bookings < max_spots`
   - create booking + increment `current_bookings`
4. Controller returns booking payload or validation/business error.

### 6.2 Booking Cancellation
1. Traveler calls `PATCH /api/bookings/{id}/cancel`.
2. Controller ownership check.
3. Repository transaction:
   - lock booking and tour
   - if not already cancelled, set `CANCELLED`
   - decrement `current_bookings` only when previous status occupied spot (`PENDING`/`CONFIRMED`).

### 6.3 Stripe Payment
1. Traveler starts checkout (`/bookings/{id}/checkout`).
2. Backend creates Stripe session (booking metadata).
3. Stripe webhook callback validates signature.
4. `BookingPaymentService` marks booking paid and sends receipt if needed.

### 6.4 PayPal Payment
1. Traveler starts PayPal checkout (`/bookings/{id}/paypal/checkout`).
2. Backend creates PayPal order and stores `paypal_order_id`.
3. Capture endpoint/webhook finalizes payment.
4. `BookingPaymentService` marks paid + receipt.

## 7. Security Analysis
### 7.1 Authentication and Authorization
- Custom API token authentication via `CustomApiAuth`.
- Role-based route access via `CheckRole` middleware and grouped routes.
- Controller-level ownership checks for sensitive records.

### 7.2 Input Validation
- FormRequests broadly used for API input validation.
- Business-level rules additionally enforced in repositories (example: booking capacity/date).

### 7.3 Injection/XSS/CSRF
- SQL injection mitigation via Eloquent/query builder parameterization.
- XSS risk reduced by Vue escaping by default (unless raw HTML rendering introduced).
- CSRF mainly non-applicable to stateless API bearer-token calls.

### 7.4 Sensitive Data Handling
- Password hashing via cast (`password => hashed`).
- Tokens hashed before storage.
- Payment secrets and webhook secrets from env/config.

### 7.5 Security Risks
- Missing rate limits on auth endpoints in routes.
- Verification schema mismatch (`admin_id` in model, not migration).
- Status inconsistency (`RESOLVED` accepted in admin report update path while enum lacks it).
- Admin user update surface should be constrained carefully.

## 8. Performance and Optimization Review
### 8.1 Positive Practices
- Eager loading used in many retrieval paths.
- Pagination support in tour listing.
- Transaction + `lockForUpdate` in booking capacity-sensitive operations.
- Queue-capable listeners for notification events.

### 8.2 Potential Bottlenecks
- Text search using `LIKE/ILIKE` patterns can degrade at scale without dedicated indexing/search engine.
- Admin/global list endpoints may grow heavy without pagination constraints in some paths.
- Aggregate stats are computed live (can be cached for high traffic).

### 8.3 Optimization Suggestions
- Add caching for dashboard aggregates.
- Add DB indexes for high-frequency query paths (status/date combos, foreign-key-heavy filters).
- Introduce consistent pagination for all large lists.

## 9. Database Design and Relationship Map (Text ERD)
- `users` 1..* `tours` via `guide_id`.
- `users` 1..* `bookings` via `traveler_id`; also `guide_id` on booking.
- `tours` 1..* `bookings`.
- `categories` 1..* `tours` (`category_id`, nullable).
- `tours` 1..* `images`.
- `users` 1..* `reviews` and `tours` 1..* `reviews`; unique `(traveler_id, tour_id)`.
- `users` 1..* `favorites` and `tours` 1..* `favorites`; unique `(traveler_id, tour_id)`.
- `users` 1..* `trip_reports` as traveler and optional admin moderator.
- `users` 1..* `verifications` as guide; admin link expected by model but missing in migration.

Normalization level:
- Mostly 3NF with explicit association entities for favorites/reviews.
- Denormalization: `tours.current_bookings` for performance.

## 10. Frontend Architecture
### 10.1 Structure and Routing
- Central route config: `frontend/src/router/index.ts`.
- Role-aware protected routes by traveler/guide/admin pages.
- Not-found route in common pages.

### 10.2 API Communication
- Axios client in `frontend/src/api/client.ts` with auth interceptor.
- Domain API modules:
  - `auth.ts`, `tours.ts`, `bookings.ts`, `favorites.ts`, `reviews.ts`, `reports.ts`, `guide.ts`, `admin.ts`, `categories.ts`.

### 10.3 Components and Reuse
- Layout: `AppNavbar.vue`, `AppFooter.vue`.
- Reusable interaction components: `StarRating.vue`, `TourReviewForm.vue`, `TourReportForm.vue`.
- Shared favorites state logic via composable `useTravelerFavorites.ts`.

### 10.4 UI/UX Patterns
- Form-based interactions with inline feedback.
- Modal workflows for review/report/cancel actions.
- Responsive-first effort across role dashboards and traveler pages.

## 11. External Integrations
- Stripe: checkout session + webhook completion processing.
- PayPal: order creation/capture + webhook signature verification.
- SMTP/Laravel Mail: notifications and reminders.
- Queue usage implied through `ShouldQueue` listeners and queued mails.

## 12. Testing Assessment
### 12.1 Existing State
- Minimal baseline tests only (`ExampleTest` style).

### 12.2 Missing Coverage (Critical)
- Booking concurrency/capacity/date rules.
- Payment callbacks and idempotency.
- Role authorization and ownership protection.
- Verification/report moderation flows.
- Tour filters and query contract.

### 12.3 Recommended Priority Tests
1. Booking create/cancel/update state transitions.
2. Stripe/PayPal webhook integration and duplicate callback handling.
3. Auth + middleware role checks.
4. Favorites/reviews duplicate prevention and ownership operations.

## 13. Architecture Review (Cohesion, Coupling, Best Practices)
### 13.1 Strengths
- Clear modular separation by domain.
- Repository + interface usage gives clear abstraction seams.
- Good domain events for status-change communications.

### 13.2 Weaknesses / Smells
- Enum reuse mismatch for report statuses.
- Verification schema mismatch vs model contract.
- Heavy endpoint growth without uniform API documentation.
- Some business rules split between UI and backend historically; backend should remain source of truth.

## 14. UML and Design Validation

### 14.1 Provided Class UML vs Code
Given class UML:
- Correct:
  - Roles, main entities, and most relationships align.
  - `Favorite` as association class is valid for this implementation.
- Mismatches:
  1. `Verification.admin_id` implied by model/relations but absent in DB schema.
  2. `TripReport` admin status flow allows `RESOLVED` in admin update path while `Status` enum excludes it.
  3. If diagram includes `Tour.status`, this is not in current model/migration.

### 14.2 Cahier de Charge vs Implementation
- Largely implemented:
  - traveler/guide/admin feature families, payments, favorites/reviews/reports, verification, dashboard metrics.
- Partial or needs tightening:
  - strict real-time availability behavior (current consistency is transactional but no real-time push layer).
  - some filter experience differences between cahier wording and current UI behavior.

### 14.3 Use-Case Diagram Picture Validation
From the provided use-case diagram image:
- Good:
  - actor coverage for Traveler/Guide/Admin is broad.
  - protected action grouping around authentication intent is clear.
- Needs improvement:
  - repeated `<<include>> Authentication` makes the diagram visually overloaded.
  - use-case naming can be simplified (verb-object form) to reduce ambiguity.
  - consider precondition notation for auth instead of many include arrows.

## 15. Problems, Risks, and Weaknesses
1. Schema-model mismatch: `verifications.admin_id` not migrated.
2. Status mismatch: admin report flow accepts `RESOLVED`, enum does not.
3. Security hardening gaps: route throttling, stricter admin update controls.
4. Low automated test coverage for business-critical flows.
5. Potential scaling pressure in unindexed/filter-heavy queries.

## 16. Optimization and Refactoring Recommendations
### 16.1 High Priority
1. Add migration for `verifications.admin_id` and set on admin status update.
2. Align report status strategy:
   - either extend enum with `RESOLVED`
   - or remove `RESOLVED` from controller/frontend.
3. Add API rate limiting middleware for auth and write-heavy endpoints.
4. Add feature tests for booking/payment/report/verification workflows.

### 16.2 Medium Priority
1. Add consistent pagination across all admin listings.
2. Add caching strategy for dashboard stats.
3. Add API contract documentation (OpenAPI/Swagger).
4. Expand DB indexing for high-frequency filter queries.

### 16.3 Estimated Impact
- Data integrity fixes: high impact, immediate reliability gains.
- Security hardening: medium-high impact.
- Testing uplift: high long-term maintainability impact.
- Query/index optimization: medium performance gain now, high at scale.

## 17. Developer Ownership Knowledge Checklist
### 17.1 Laravel Concepts Required
- Eloquent relations and casts.
- FormRequest validation + authorization patterns.
- Middleware authoring and route middleware composition.
- Events/listeners/mail + queue behavior.
- Transactions and row-locking (`lockForUpdate`) for concurrency-sensitive paths.
- Service container bindings and repository interfaces.

### 17.2 Vue Concepts Required
- Composition API (`ref`, `computed`, lifecycle hooks).
- Vue Router guard logic and role-based route protection.
- Axios interception and API module organization.
- Form/state handling and reusable composables.

### 17.3 System Design Concepts
- Layered monolith responsibilities.
- Consistency boundaries between backend truth and frontend constraints.
- Payment flow reliability and webhook idempotency principles.
- Moderation and auditability in multi-role systems.

### 17.4 Domain-Specific Rules to Master
- Booking capacity and date alignment with tour schedule.
- Payment must precede guide confirmation (as implemented in controller rules).
- Review eligibility logic (depends on booking/payment state).
- Verification/report status transitions and notification triggers.

## 18. Route and File Reference Index
### 18.1 Key Backend Paths
- `backend/routes/api.php`
- `backend/app/Http/Controllers/Api/*.php`
- `backend/app/Http/Controllers/Api/Admin/*.php`
- `backend/app/Repositories/*.php`
- `backend/app/Services/*.php`
- `backend/app/Models/*.php`
- `backend/database/migrations/*.php`

### 18.2 Key Frontend Paths
- `frontend/src/router/index.ts`
- `frontend/src/api/*.ts`
- `frontend/src/pages/**/*.vue`
- `frontend/src/components/**/*.vue`
- `frontend/src/composables/useTravelerFavorites.ts`
- `frontend/src/types/**/*.ts`

## 19. Final Handover Verdict
- Overall maturity: good functional breadth with clear layering.
- Immediate blockers for senior ownership confidence:
  1. schema/enum consistency fixes,
  2. high-value automated tests,
  3. security hardening and API contract formalization.

When these targeted fixes are applied, the project will be substantially easier to maintain, onboard, and scale.
