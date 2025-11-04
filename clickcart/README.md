# ClickCart.pk Marketplace

ClickCart.pk is a lightweight PHP marketplace where multiple sellers manage products, inventory, and orders while buyers shop from a unified storefront. The first ten sales for every seller are free; the eleventh sale automatically includes a one-time ₨100 platform fee.

## Stack

- PHP 8.1+
- MySQL / MariaDB
- jQuery + AJAX polling
- Custom CSS (no heavy framework)
- Apache (LAMP) or Nginx (LEMP)

## Features

- Buyer & seller authentication with password resets
- Seller dashboard with sales metrics, inventory controls, order management, and CSV exports
- Product management with image uploads, SKU auto-generation, and category tools
- Session-backed cart and checkout flow with modular payment gateways (JazzCash, EasyPaisa, NayaPay, mock gateway)
- Platform fee logic applied after 10 confirmed sales per seller
- Admin panel for platform monitoring and resetting platform fees
- Static legal pages (Terms, Privacy, Refund, Shipping, Seller Agreement)
- Scripts for VPS provisioning, deployment, and backups
- Postman collection covering core endpoints

## Requirements

- PHP 8.1 or newer with `pdo_mysql`, `mbstring`, `curl`, and `zip`
- MySQL 8+ or MariaDB 10+
- Composer
- Node is not required

## Local Setup

```bash
git clone <repo> clickcart
cd clickcart
cp .env.example .env
composer install
```

Update `.env` with local database credentials, then import the schema and seeds:

```bash
mysql -u root -p < db_init.sql
```

Configure your local web server to serve `public/` (for example via Apache virtual host or `php -S localhost:8000 -t public`).

### Sample Credentials

- Admin: `admin@clickcart.pk` / `password123`
- Seller: `seller@clickcart.pk` / `password123`
- Buyer: `buyer@clickcart.pk` / `password123`

> Password hashes in `db_init.sql` correspond to `password123`.

## Running Tests

Tests require PHPunit. After installing dependencies you can run:

```bash
./vendor/bin/phpunit
```

(Add your own tests under `tests/` as needed.)

## Deployment

1. Provision a Ubuntu VPS and run the helper script:
   ```bash
   ./scripts/install_lamp.sh
   ```
2. Deploy the code:
   ```bash
   ./scripts/deploy.sh
   ```
3. Point DNS for `clickcart.pk` to your server and obtain SSL:
   ```bash
   sudo certbot --apache -d clickcart.pk -d www.clickcart.pk
   ```
4. Configure a nightly backup:
   ```bash
   echo "0 2 * * * root /var/www/clickcart/scripts/backup.sh" | sudo tee /etc/cron.d/clickcart-backup
   ```

### Environment Variables

- `BASE_URL` – public URL of the site
- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Payment keys: `PAYMENT_JAZZCASH_KEY`, `PAYMENT_EASYPAY_KEY`, `PAYMENT_NAYAPAY_KEY`
- `PAYMENT_WEBHOOK_SECRET` – optional HMAC secret for webhook validation
- `MAIL_*` settings for outbound email (defaults log to `storage/logs/mail.log`)

## Platform Fee Logic

- `users.total_sales` increments for each paid order item quantity
- If `total_sales >= 10` and `platform_fee_paid = 0`, the next checkout adds ₨100
- After payment success the webhook sets `platform_fee_paid = 1`
- Admins can reset the flag in `Admin → Sellers`

## AJAX Polling

Seller dashboards poll `/api/seller/orders_poll.php` every 10 seconds (session-protected). Adjust the interval inside `public/dashboard/index.php` if needed.

## File Structure

```
clickcart/
  app/            # controllers, models, helpers, bootstrap
  public/         # web root (index.php, assets, views)
  api/            # JSON endpoints and webhooks
  config/         # environment + database bootstrapping
  scripts/        # deployment & maintenance helpers
  storage/        # logs, seller settings (ensure writable)
  docs/postman/   # Postman collection
  db_init.sql     # schema + seed data
```

## Postman Collection

Import `docs/postman/clickcart.postman_collection.json` and set `{{base_url}}` to your environment. Use browser developer tools to copy the authenticated seller session cookie into `{{session_cookie}}` for private endpoints.

## Security Checklist

- Always serve production over HTTPS (Let’s Encrypt via `certbot`)
- Store `.env` outside version control
- Lock file permissions (`public/uploads` writable, everything else 644/755)
- Rotate `PAYMENT_WEBHOOK_SECRET` and gateway credentials regularly
- Use Fail2ban or similar for SSH and login protection

## Support

For issues or feature requests, create a ticket or email `support@clickcart.pk`.
