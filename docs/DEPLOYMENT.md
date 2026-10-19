# Deployment configuration

## Current status

The Docker, Render Blueprint, and smoke workflow are prepared, but no live service has been built or deployed from this workspace. Docker and a Git remote are unavailable here, and Laravel 11 is outside its published security-fix window. The framework requirement is held at Laravel 11 because the product brief specifies it. Keep deployment blocked until the framework risk is resolved; a successful local SQLite test is not a production or PostgreSQL release result.

## Render service

`render.yaml` describes one Docker web service in Singapore. It uses `/up` as its health check and waits for linked CI checks before auto-deploying. The container binds Apache to Render's `PORT`, serves Laravel's `public/` directory, runs additive migrations, seeds the public demo, and builds Laravel's configuration, route, and view caches. The start script supervises both Apache and `php artisan schedule:work`; the Laravel schedule runs expired portfolio cleanup every ten seconds so the undo window completes even if the owner closes the browser.

Configure these values in the Render dashboard. The names are listed here; secret values are never stored in the repository.

| Variable | Configuration |
|---|---|
| `APP_KEY` | Generate for this deployment and store in Render's secret environment. |
| `APP_URL` | The deployed HTTPS service URL. |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | Supabase shared session-pooler hostname copied from the dashboard. |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `postgres` unless the project uses another database name. |
| `DB_USERNAME` | The shared-pooler username format from Supabase, typically `postgres.[PROJECT_REF]`. |
| `DB_PASSWORD` | The database password, stored as a Render secret. |
| `DB_SSLMODE` | `require` |
| `SESSION_DRIVER` | `file` |
| `CACHE_STORE` | `file` |
| `QUEUE_CONNECTION` | `sync` |

Supabase documents shared session mode on port 5432 and shared pooler connectivity over IPv4. Use the session-mode host and username from the Supabase Connect dialog, not a guessed region or project hostname. See [Supabase's PostgreSQL connection guide](https://supabase.com/docs/guides/database/connecting-to-postgres) and [Render's Blueprint reference](https://render.com/docs/blueprint-spec).

## Database safety

The CI job owns an ephemeral PostgreSQL 16 database named `portgen_test` and may run `migrate:fresh` and rollback/reapply against that service only. E2E setup rejects a PostgreSQL destination unless it is explicitly marked as the local CI test database. Never point tests at Supabase. In production, run only `php artisan migrate --force`; never run `migrate:fresh` or `db:wipe`.

The demo seeder uses the fixed public path `/p/portgen001` and fictional profile details. It is idempotent and is run by the Render startup flow. The live smoke workflow checks `/up`, `/portfolios/create`, and that seeded portfolio.

## Live smoke check

After the repository is connected to GitHub and the Render service has a live URL, add the repository variable `PORTGEN_LIVE_URL` with the service's public HTTPS origin. The workflow runs after successful CI on `main` or by manual dispatch. It retries the health endpoint and checks the form and seeded public portfolio. The live URL is intentionally not recorded until a real deployment exists.

## Not yet verified

The local environment does not include Docker, so `docker build` and the container `/up` check have not run. This checkout has no Git remote or Render service URL. CI's PostgreSQL and GitHub checks therefore remain configured but unexecuted from this checkout. `composer audit --locked` reports four advisories for Laravel 11.57.0, including a medium-severity signed-URL path-confusion advisory; Laravel's support table ended Laravel 11 security fixes on 2026-03-12. Do not deploy this dependency set as production-safe.

The required local `portgen-secrets.env` file is absent. The exact missing deployment values are `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `RENDER_API_KEY`, and `PORTGEN_LIVE_URL`. The environment also has no `GITHUB_TOKEN` or `GH_TOKEN`, and `gh auth status` reports no CLI login. The requested GitHub and Supabase accounts are signed in to the browser, but no database or hosting project was changed. The visible Supabase organization contains a different existing project, which was left untouched.
