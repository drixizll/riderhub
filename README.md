# RiderHub

RiderHub is a Laravel 12 ride-booking and package-delivery web application built as a capstone project. It provides separate customer, rider, and administrator workspaces, with a responsive interface powered by Tailwind CSS 3, Alpine.js, Vite, and Leaflet.

## Features

- Customer registration, login, password reset, profile editing, and account deletion.
- Customer or rider self-registration with strong-password rules and live strength feedback.
- Profile photo uploads, a personalized account overview, and in-app booking notifications.
- Customer ride and delivery requests with pickup/drop-off map selection and fare estimates.
- Booking history, filters, booking cancellation while a request is pending, and trip details.
- Rider request queue, protected booking acceptance, trip status transitions, and browser-based live location sharing.
- Customer live-location polling for an assigned active booking.
- Customer star ratings and optional feedback for completed trips, with rider average ratings.
- Installable Progressive Web App shell with a persistent light/dark theme, reduced-motion-aware transitions, and a generic offline page.
- Administrator operations dashboard, booking search and filters, and a searchable people directory.
- Role-specific navigation and server-side role checks for protected routes.

## Local setup

Use PHP and Composer versions compatible with Laravel 12, plus Node.js 20 or newer.

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run dev
```

In a second terminal, start Laravel:

```powershell
php artisan serve
```

Open `http://127.0.0.1:8000`. For a production asset build, run:

```powershell
npm run build
```

If your local `.env` uses SQLite, ensure the configured database file exists before running migrations. Keep environment-specific settings in `.env`; do not commit that file.

## Local demo accounts

`php artisan migrate --seed` creates these demo accounts:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@riderhub.test` | `123` |
| Rider | `rider@riderhub.test` | `123` |
| Customer | `customer@riderhub.test` | `123` |

These intentionally weak seeded credentials are for local demonstration only and are separate from the strong-password rules on public registration. Change or remove them before deploying anywhere accessible to others.

## Main workflows

1. A customer registers, signs in, selects ride or delivery, chooses pickup/drop-off points, and submits a booking.
2. A rider reviews unassigned requests and accepts one active booking at a time.
3. The rider advances the request from accepted to picked up to completed. With permission and the rider page open, the app shares location about every five seconds; the customer page checks for updates at the same interval while visible.
4. After a completed trip, the customer can submit one 1–5-star rider review with an optional comment. Rider ratings appear on the rider dashboard.
5. Customers and riders receive in-app notifications for booking and trip updates. Riders and admins are notified about new booking requests.
6. An administrator reviews platform totals, searches bookings, and browses the people directory.
7. Customers can pay online through PayMongo-hosted checkout using enabled GCash, Maya (PayMaya), or Visa/Mastercard card methods. Rider dispatch waits for a signed, amount-verified payment webhook.

## PayMongo sandbox checkout

RiderHub redirects online customers to PayMongo Hosted Checkout; card numbers and security codes are never collected or stored by RiderHub. The checkout offers GCash (`gcash`), Maya (`paymaya`), and cards (`card`) when those methods are activated for the merchant account. PayMongo supports eligible Philippine and international Visa/Mastercard credit, debit, and prepaid cards; issuer, merchant, and account limits still apply.

1. Create a PayMongo account, complete the required account verification, and enable the payment methods in the PayMongo dashboard.
2. Add the test secret key and webhook secret to your local `.env` file (do not commit them). The variable names are in `.env.example`: `PAYMONGO_SECRET_KEY`, `PAYMONGO_WEBHOOK_SECRET`, and `PAYMONGO_LIVEMODE=false`.
3. In PayMongo **Settings → Webhooks**, register `https://<your-public-https-host>/webhooks/paymongo` and subscribe to `checkout_session.payment.paid`. For local webhook testing, expose your local Laravel server through a trusted HTTPS tunnel and register that temporary URL.
4. Start the app with `php artisan serve` and `npm run dev` (or use `npm run build` for compiled assets). Submit an online booking and complete checkout with PayMongo's test payment methods.
5. Keep `PAYMONGO_LIVEMODE=false` while using test credentials. For production, configure the matching live secret and webhook secrets through a deployment secret manager, set `PAYMONGO_LIVEMODE=true`, use HTTPS, and register the production webhook URL.

The customer return page is informational only; the signed PayMongo webhook is the source of truth for payment status. Webhook processing checks the checkout session, booking reference, metadata, amount, PHP currency, and test/live mode before marking payment paid. Unpaid online bookings are not shown to riders. Refunds and chargeback handling are not automated.

## Capstone and deployment notes

The current implementation is a functional academic/demo MVP, not a production payment or dispatch platform:

- PayMongo hosted checkout and signed webhook verification are integrated, but real-money operations still require merchant activation, live credentials, HTTPS deployment, refund/chargeback handling, and operational reconciliation.
- Location sharing requires the rider's browser to remain open and grant location permission; it is not background tracking.
- RiderHub can be installed as a PWA on supported browsers. The browser's install control appears when installation is available; on iOS, use Safari's Share menu and choose "Add to Home Screen." Core bookings, maps, and trip tracking still require an internet connection. This does not package a native Android/iOS app or provide app-store distribution.
- Profile photos are stored on Laravel's public disk. Run `php artisan storage:link` after setup so uploaded images can be displayed.
- Geocoding uses the public OpenStreetMap Nominatim service. Follow its current usage policy and configure a suitable provider before production traffic.
- Fare is estimated using straight-line distance, not a routed road distance or traffic-aware quote.
- Email/push notifications, customer-support tooling, rider identity verification, and automated operational reporting still need implementation before a real launch. Booking and trip notifications currently appear inside RiderHub.

Before a public deployment, configure production mail and database services, secure demo credentials, add payment-provider integration if needed, review privacy/consent and retention for location data, and run the full test suite.

## Tests

```powershell
php artisan test
```
