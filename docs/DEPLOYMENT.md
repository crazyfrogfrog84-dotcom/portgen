# Deployment configuration

## Current status

The repository has a public GitHub remote and this checkout is prepared on branch `codex/render-deployment`. The Render Blueprint and live smoke workflow are configured, but the branch has not been pushed and no Render service or dedicated PortGen Supabase database has been created. The requested Render service name is `portgen-m1uh`; its expected address is `https://portgen-m1uh.onrender.com`, subject to Render confirming the name when the service is created.

The Laravel 12.70.0 dependency set passes the Composer audit recorded for this checkout. The previous Laravel 11 advisories were addressed before preparing this release. Local unit, browser, and asset-build checks passed earlier; this machine has no Docker installation, so the production image and Render health endpoint have not been verified here. CI is configured to build the image and run PostgreSQL-backed checks after the branch is pushed.

## Render service

`render.yaml` describes one free Docker web service in Singapore. It builds `Dockerfile`, checks `/up`, and waits for linked GitHub CI checks before automatically deploying. The container binds Apache to Render's `PORT`, runs additive migrations and the fictional demo seeder at startup, and builds Laravel's configuration, route, and view caches. The start script supervises both Apache and `php artisan schedule:work`; the Laravel schedule runs expired portfolio cleanup every ten seconds so the undo window completes even if the owner closes the browser.

Render's free web services have an ephemeral filesystem and sleep after inactivity. PortGen therefore stores records, sessions, and cache in PostgreSQL; files required after a restart must not be written to the container. Free services are suitable for a demonstration and may take about a minute to wake after sleeping. See [Render's free instance limits](https://render.com/docs/free) and [Render web services](https://render.com/docs/web-services).

Configure these values in the Render dashboard. Secret values must only be stored in Render's environment settings.

| Variable | Configuration |
|---|---|
| `APP_KEY` | Generate for this deployment and store as a Render secret. |
| `APP_URL` | The confirmed deployed HTTPS service URL. |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | Supabase shared session-pooler hostname copied from the dashboard. |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `postgres` unless the project uses another database name. |
| `DB_USERNAME` | The shared-pooler username format shown by Supabase, typically `postgres.[PROJECT_REF]`. |
| `DB_PASSWORD` | The database password, stored as a Render secret. |
| `DB_SSLMODE` | `require` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |

Supabase documents shared session mode on port 5432 and shared pooler connectivity over IPv4. Use the session-mode host and username from the Supabase Connect dialog, not a guessed region or project hostname. See [Supabase's PostgreSQL connection guide](https://supabase.com/docs/guides/database/connecting-to-postgres) and [Render's Blueprint reference](https://render.com/docs/blueprint-spec).

## Database safety

The CI job owns an ephemeral PostgreSQL 16 database named `portgen_test` and may run `migrate:fresh` and rollback/reapply against that service only. E2E setup rejects a PostgreSQL destination unless it is explicitly marked as the local CI test database. Never point tests at Supabase. In production, run only `php artisan migrate --force`; never run `migrate:fresh` or `db:wipe`.

The demo seeder uses the fixed public path `/p/portgen001` and fictional profile details. It is idempotent and runs in the Render startup flow. The live smoke workflow checks `/up`, `/portfolios/create`, and that seeded portfolio.

## Live smoke check

After the Render service is created, add repository variable `PORTGEN_LIVE_URL` with its confirmed public HTTPS origin. The workflow runs after successful CI on `main` or by manual dispatch and retries the health endpoint before checking the form and seeded public portfolio. The expected Render URL is not considered live until a real deployment responds successfully.

## Not yet verified

The local environment does not include Docker, so `docker build` and the container `/up` check have not run. No Render service, production database, or live URL is configured yet. The GitHub CI and smoke workflows have not run for this branch. The deploy requires the Render and Supabase account connections, a dedicated Supabase database, secret environment values, the branch push, and a successful Render build. The existing Supabase project is not PortGen's database and must remain untouched.

No production database migration has been attempted. Do not deploy until the Render service is connected to the dedicated database and its required environment variables are set.
