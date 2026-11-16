# DigitalPay

DigitalPay is an online store for digital goods such as premium app subscriptions, streaming accounts and digital services. Buyers check out without an account, pay with a dynamic QRIS code, and receive their product on the order page and by email as soon as the payment is confirmed. Admins manage the catalog, stock, orders and vouchers from a built-in admin panel.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | Inertia.js 2, Vue 3, Tailwind CSS 3, Vite 5 (with server-side rendering) |
| Admin panel | Filament 3 |
| Database | MySQL 8 (SQLite for tests) |
| Background work | Laravel queue (database driver) and task scheduler |
| Email | Transactional email API, Blade templates |
| Tests | PHPUnit 11 |

## Features

**Storefront**
- Product catalog with categories, variants, promo prices, popular and featured products
- Guest checkout; orders are protected so only the buyer (session or email check) can open them
- Optional member accounts with email OTP verification, password reset and order history
- Public vouchers shown in a promo list, plus private vouchers that only work when the code is typed
- News, FAQ and static pages managed from the admin panel

**Payments**
- Dynamic QRIS per order with the amount locked in the QR code
- A small unique code (Rp 1–20) is added to every total so concurrent orders with the same price can be told apart
- The QR code is rendered on the store's own invoice page; the page updates itself when the payment lands
- Automatic payment detection through a signed webhook (HMAC-SHA256 with replay protection), with a scheduled status check as a fallback when a webhook is missed
- Late payments on an expired order are still accepted and fulfilled
- Admins can mark an order as paid manually when needed
- An alternative card/VA gateway is kept in the codebase and can be switched on with one environment variable

**Delivery**
- Each product variant uses one delivery method:
  - **Automatic**: an account is taken from the variant's digital stock and delivered right after payment
  - **Manual**: the order is queued for an admin, who sends the details from the order page
- Stock for automatic variants is the number of usable accounts in digital stock; variants without stock show as sold out and cannot be bought
- If automatic delivery fails (for example, two buyers paid for the last account at the same time), the order falls back to the manual queue
- Delivered details appear on the buyer's invoice page and are sent by email

**Email notifications**
- Buyer: order confirmation with payment link, payment received, product delivered (automatic and manual)
- Admin: payment received, with a clear "needs manual delivery" warning when action is required
- Account: OTP verification and password reset
- All emails share one minimal layout; subjects are kept in one place in the email service

**Admin panel** (`/admin`)
- Transactions: orders with a "Needs delivery" filter and badge, item and delivery details, Send Manually and Mark as Paid actions; read-only payments
- Catalog: categories, products with their variants, digital stock
- Content: articles, FAQ, pages, slides
- Users and system settings: site name, contact details, payment methods and fees, vouchers

## How an Order Flows

1. The buyer chooses a variant and fills in the checkout form.
2. The server creates a payment transaction and stores the QR string, unique code, final total and expiry time.
3. The invoice page shows the QR code and polls the order status.
4. The payment is confirmed by webhook, by the scheduled status check, or by an admin.
5. The order is marked as paid and a delivery job is queued immediately.
6. The job delivers automatic variants from stock or queues the order for manual delivery, then emails the admin.
7. The buyer receives the product details on the invoice page and by email.

Unpaid orders expire automatically after the payment window.

## Local Setup

Requirements: PHP 8.2+ (with `pdo_mysql`, `mbstring`, `intl`, `gd`, `bcmath`, `curl`), Composer, Node.js 18+, MySQL 8.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
```

Run the app, the queue worker and the scheduler (three terminals, or use `composer dev` for the server, queue, logs and Vite together):

```bash
php artisan serve
php artisan queue:work
php artisan schedule:work
```

The seeder creates a default admin account; see the user seeder and change the password before going live.

## Configuration

All settings live in `.env`. The most important ones:

| Variable | Purpose |
|---|---|
| `APP_URL` | Public URL; used in emails, invoice links and the webhook endpoint |
| `DB_*` | Database connection |
| `QUEUE_CONNECTION` | Keep `database` so deliveries and emails run in the background |
| `PAYMENT_GATEWAY` | `qrisgate` (dynamic QRIS, default) or `midtrans` |
| `QRISGATE_BASE_URL`, `QRISGATE_API_KEY` | Dynamic QRIS API access |
| `QRISGATE_WEBHOOK_SECRET` | Secret used to verify payment webhooks |
| `QRISGATE_TIMEOUT` | API timeout in seconds |
| `MIDTRANS_*` | Alternative gateway credentials (only when `PAYMENT_GATEWAY=midtrans`) |
| `BREVO_API_KEY`, `BREVO_ENABLED` | Transactional email API |
| `BREVO_SENDER_EMAIL`, `BREVO_SENDER_NAME` | Sender address and name |
| `INERTIA_SSR_ENABLED` | Server-side rendering for SEO (default `true`) |

Site name, contact email (also the admin notification address), WhatsApp number and payment method fees are edited in the admin panel under Settings and Payment Methods.

## Production Deployment

1. Install dependencies and build: `composer install --no-dev --optimize-autoloader`, `npm ci`, `npm run build` (builds the client and the SSR bundle).
2. Set the production `.env`, then run `php artisan migrate --force` and cache config, routes and views.
3. Keep these processes running:
   - Queue worker: `php artisan queue:work --sleep=3 --tries=3 --max-time=3600` (under a process supervisor). Without it, payments are still recorded, but deliveries and emails wait in the queue.
   - Scheduler: a cron entry running `php artisan schedule:run` every minute.
   - SSR server: `node bootstrap/ssr/ssr.js` (under a process manager). If it stops, pages fall back to client-side rendering.
4. Register the webhook URL `https://<your-domain>/qrisgate/webhook` with the payment provider and make sure it is reachable over HTTPS.
5. Restart the queue worker after every deploy: `php artisan queue:restart`.
6. Place one small real order and confirm it is paid, delivered and emailed end to end.

## Scheduled Commands

| Command | Schedule | Purpose |
|---|---|---|
| `orders:expire` | Every minute | Expires unpaid orders and their payments |
| `qrisgate:sync` | Every 2 minutes | Checks pending transactions in case a webhook was missed |

## Project Structure

```
app/
  Console/Commands/
  Filament/Resources/
  Http/Controllers/
  Jobs/
  Models/
  Notifications/
  Observers/
  Services/
database/
  migrations/
  seeders/
resources/
  js/Pages/
  views/emails/
routes/
tests/Feature/
```

## Testing

Tests run on an in-memory SQLite database with the synchronous queue and mocked external APIs.

```bash
php artisan test
```

## License

Proprietary. All rights reserved.
