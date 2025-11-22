# TicketHub

TicketHub is a Laravel-based ticketing platform that lets users browse events, select seats, purchase tickets via Midtrans, and download e‑tickets with QR codes. It includes a user dashboard for order history and an admin panel for full event and ticket management.

## Overview

- Discover and browse events, select seats, and checkout.
- Secure payments via Midtrans integration (Snap/Transactions API).
- E‑tickets generated as PDF with embedded QR code and verification signature.
- User Dashboard to manage orders, download tickets, and check payment status.
- Admin tools: events, ticket types, seat maps, promos, orders, refunds, and reports.
- Gate Entry module (scanner + QR validation) for on-site check‑in.

## Key Features

- Event browsing and landing experience with modern UI.
- Seat selection with lock/unlock to prevent double booking.
- Cart and checkout flow with promo support.
- Midtrans payments (create transaction, update status, webhook handling).
- E‑ticket generation (PDF via DomPDF, QR code via Endroid).
- User Dashboard: order listing, pagination, order details, ticket downloads.
- Admin Panel: manage events, seat maps, ticket types, promotions, orders, refunds.
- Reports Dashboard (sales, revenue, tickets per event).
- Gate Entry (QR scanner, validate tickets, check-in tracking).

## Architecture

- Framework: Laravel (PHP), Vite + basic JS/CSS assets.
- Storage:
  - `storage/certs/cacert.pem` CA bundle for SSL verification on Windows.
  - `storage/app/public/tickets` for e‑ticket PDFs and QR images.
- Core Models:
  - `Event`, `TicketType`, `SeatMap`, `Promo`, `PromoUsage`
  - `Order` (casts `items` and `seats` as arrays; links to `Event`, `User`)
  - `Payment` (provider, transaction id, status, amount)
- Jobs:
  - `SendOrderEmail` (send confirmation + attach ticket)
  - `ProcessMidtransWebhook` (update order/payment based on webhook)
- Controllers (selected):
  - User: `EventBrowseController`, `SeatSelectionController`, `CartController`, `DashboardController`
  - Admin: `EventController`, `TicketTypeController`, `SeatMapController`, `PromoController`, `OrderController`, `DashboardController`, `ReportController`
  - Gate: `GateEntryController`
  - `WebhookController` (Midtrans)
- Views:
  - Layouts: `resources/views/layouts/user.blade.php`, `layouts/app.blade.php` (admin)
  - User pages: checkout, payment status, ticket download, dashboard
  - Admin pages: events, seat maps, ticket types, orders, reports

## Routes (Highlights)

- Public:
  - `GET /` landing, `GET /events`, `GET /events/{event}`
  - Seat selection API: `GET /events/{event}/seat-map`, `POST /events/{event}/seat-lock`, `POST /events/{event}/seat-unlock`
- User:
  - Cart & checkout: `GET/POST /events/{event}/cart`, `GET /events/{event}/checkout`, `POST /events/{event}/checkout/confirm`
  - Payment status: `GET /orders/{order}/status`, `POST /orders/{order}/status/check`
  - Ticket download: `GET /orders/{order}/ticket`
  - Dashboard: `GET /dashboard`, `GET /orders`, `GET /orders/{order}`
- Webhooks:
  - Midtrans: `POST /webhooks/midtrans` (+ alias `POST /payment/success`)
- Admin:
  - `prefix /admin` resource routes for Events, Ticket Types, Promos; Orders index/show, status update, refund
  - Reports: `GET /admin/reports`, exports
- Gate:
  - `GET /gate/scanner`, `POST /gate/validate`

## Payment Flow (Midtrans)

- Checkout builds a structured item list from selected seats.
- Creates a Snap/Transactions request to Midtrans and stores a `Payment` record.
- Users are redirected to payment status page; can manually refresh status.
- Webhook updates `Payment` and `Order` statuses (`paid`, `pending`, `failed`, `expired`, etc.).
- When `Order` becomes `paid`, the job `SendOrderEmail` generates e‑tickets and emails the buyer.

### SSL Verification on Windows

A common issue during Midtrans integration was:
- `cURL error 60: SSL certificate problem: unable to get local issuer certificate`
- Root cause: cURL could not read the CA bundle path on Windows.
- Fixes implemented:
  - `.env` variable `CURL_CA_BUNDLE=storage/certs/cacert.pem` (use forward slashes or wrap in single quotes).
  - `config/services.php` includes `midtrans.verify_ssl` and `midtrans.cacert_path`.
  - Payment client explicitly sets cURL options (`CURLOPT_CAINFO`, `CURLOPT_CAPATH`) and uses `verify` with the bundle path when valid.
  - Fallback to system CA when bundle invalid; guidance to set `curl.cainfo` and `openssl.cafile` in `php.ini`.
- Temporary validation: `MIDTRANS_VERIFY_SSL=false` allows transactions but should not be used in production.

## E‑Tickets

- Generated as PDF using DomPDF, containing:
  - Buyer information, event details, seats, and QR code.
- QR code:
  - Encodes a signed payload (`HMAC SHA-256`) containing `order_id`, `user_id`, `external_ref`, and `issued_at`.
  - Stored both as PNG (for PDF) and SVG (for HTML display).
- Download:
  - `GET /orders/{order}/ticket` — only available for `paid` orders.
  - Ownership check: only the buyer (or admin) can download.

## User Dashboard

- Lists authenticated user’s orders with pagination.
- Shows event title, seat count, totals, order status, latest payment status.
- Actions:
  - View order details
  - Download ticket (when `paid`)
  - Check payment status (when not paid)

## Admin Panel

- Manage events, seat maps (builder + save), ticket types, promotions.
- Orders:
  - Overview, detail pages (items, seats, payments).
  - Update order status.
  - Process full refund (marks order refunded, disables ticket access, records refund, sends email).
- Reports:
  - Sales, revenue, tickets sold per event.
  - Export endpoints.

## Gate Entry

- QR Scanner UI for gate staff and admins.
- Validate ticket QR payload; mark check‑in and prevent reuse.

## Setup

### Prerequisites

- PHP 8.1+ and Composer
- Node.js 16+ and npm (for assets)
- A database (MySQL/PostgreSQL/SQLite)
- Windows users: ensure `storage/certs/cacert.pem` exists and is readable

### Installation

```bash
composer install
```

```bash
copy .env.example .env
```

Set environment variables in `.env`:

```dotenv
APP_NAME=TicketHub
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tickethub
DB_USERNAME=root
DB_PASSWORD=secret

MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_VERIFY_SSL=true
CURL_CA_BUNDLE=storage/certs/cacert.pem

E_TICKET_SECRET=change_this_to_a_random_secret
```

Generate app key:

```bash
php artisan key:generate
```

Run migrations and seeders (admin and gate staff accounts):

```bash
php artisan migrate
```

```bash
php artisan db:seed --class=AdminSeeder
```

```bash
php artisan db:seed --class=GateStaffSeeder
```

Start the server:

```bash
php artisan serve
```

Install and run frontend assets (optional):

```bash
npm install
```

```bash
npm run dev
```

### File Permissions

- Ensure `storage/` and `bootstrap/cache/` are writable.
- Ensure `storage/certs/cacert.pem` is readable by PHP/CURL.

## Security Notes

- Ticket downloads are restricted to the order owner (or admin) and only when `paid`.
- SSL verification with Midtrans should remain enabled; use the CA bundle fix above on Windows.
- Webhook endpoints are CSRF-exempt but should be protected by secret keys on the provider side.

## Troubleshooting

- cURL error 60:
  - Verify `CURL_CA_BUNDLE` path uses forward slashes: `storage/certs/cacert.pem`.
  - Confirm file is readable and is a valid CA bundle.
  - In `php.ini`, set:
    - `curl.cainfo = "C:\path\to\cacert.pem"`
    - `openssl.cafile = "C:\path\to\cacert.pem"`
  - Clear config cache:
    - `php artisan config:clear`
  - As a temporary test only: set `MIDTRANS_VERIFY_SSL=false` (not for production).
- PDF generation issues:
  - Ensure `storage/app/public/` is writable.
  - Check that QR PNG is properly created and referenced.

## Roadmap

- Enhanced filters and search on the User Dashboard.
- Partial refunds and refund audit trails.
- Multi-event seat map presets and real-time availability.
- More gateways besides Midtrans.

## Folder Structure (Brief)

- `app/Models` — core models (`Event`, `Order`, `Payment`, etc.)
- `app/Http/Controllers` — user/admin/gate controllers + webhook
- `app/Jobs` — background jobs for emails and midtrans processing
- `resources/views` — Blade templates (layouts, user, admin)
- `public/` — assets and entry point
- `routes/web.php` — all web routes (public, user, admin, gate)
- `storage/` — logs, compiled views, `certs/cacert.pem`, and ticket files

---

Happy building with TicketHub! If you need help customizing flows or UI, check `resources/views/layouts/user.blade.php` and the controllers under `app/Http/Controllers/User/`.
