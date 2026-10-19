# PortGen

PortGen is a responsive portfolio generator for students, freelancers, and job seekers. People enter one structured profile, choose Simple, Modern, or Creative, then share a public portfolio. A separate signed private link lets the owner edit, change templates, or delete it.

## Stack

Laravel 12, PHP 8.3, Blade, Tailwind CSS 4, Vite 8, PostgreSQL 16, Playwright, and axe-core. Deployment uses a Render Docker web service running Apache and a dedicated Render PostgreSQL database on the Free plan for demonstration use.

## Run locally

```powershell
composer install
if (!(Test-Path .env)) { Copy-Item .env.example .env }
New-Item -ItemType File -Path database/database.sqlite -Force
php artisan key:generate
npm ci
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000/portfolios/create`. The local environment uses SQLite, file sessions/cache, and the synchronous queue.

## Checks

```powershell
./vendor/bin/pint --test
php artisan test
$env:HCI_RELEASE = '1'
php artisan test
Remove-Item Env:HCI_RELEASE
npm run build
npm audit --omit=optional
npx playwright install chromium
npm run test:e2e
php artisan hci:summary
php scripts/check-secrets.php --worktree
```

The browser suite prepares an isolated test database and never reads a production database URL. CI runs PHP and browser tests against its own local PostgreSQL 16 service. The suite saves mobile and desktop screenshots under `docs/screenshots/` and fails on any serious or critical axe-core finding.

The fictional seeded sample is available at `/p/portgen001` after `php artisan db:seed`. The owner URL is shown once after creating a portfolio; anyone with that private URL can manage the portfolio.

## HCI evidence

The tracker and generated status summary are in [`docs/HCI_TRACKER.md`](docs/HCI_TRACKER.md) and [`docs/HCI_TRACKER_SUMMARY.md`](docs/HCI_TRACKER_SUMMARY.md). Screenshots, the expert heuristic evaluation, and the classmate usability kit are documented under `docs/`.

The school-style system paper is available as [`docs/SYSTEM_DOCUMENTATION.md`](docs/SYSTEM_DOCUMENTATION.md), with exported [`DOCX`](docs/export/PortGen_System_Documentation.docx) and [`PDF`](docs/export/PortGen_System_Documentation.pdf) versions.

## Deployment status

The Render deployment uses `Dockerfile`, `docker/start.sh`, and `render.yaml`. The service listens on Render's `PORT`, uses Render PostgreSQL for database-backed sessions and cache, and runs additive migrations and the fictional demo seeder at startup. Render generates a 256-bit `APP_KEY` secret; Laravel's config adds the `base64:` marker when needed. The database connection is bound without storing credentials in Git. The Free database expires after 30 days; upgrade it within the following 14-day grace period to keep its data.

The public source repository is [crazyfrogfrog84-dotcom/portgen](https://github.com/crazyfrogfrog84-dotcom/portgen). The deployment branch is `codex/render-deployment`; the expected public URL is `https://portgen-m1uh.onrender.com`, pending Render creation and health checks. The Laravel framework was upgraded to 12.70.0 after Laravel 11's security-fix window ended; `composer audit --locked` now passes.
