# Deployment configuration

## Current status

The public repository now contains branch `codex/render-deployment`. Its latest PostgreSQL CI run is pending. The Render Blueprint defines the requested Docker web service and a dedicated Render Free Postgres database. Render has not created either resource yet. The requested service name is `portgen-m1uh`; the expected address is `https://portgen-m1uh.onrender.com`, subject to Render confirming the name when the service is created.

The Laravel 12.70.0 dependency set passes the Composer audit recorded for this checkout. The previous Laravel 11 advisories were addressed before preparing this release. The workstation has no Docker installation, so the production image will be built by GitHub CI. The PostgreSQL tests and image build must pass before Render auto-deploys the branch.

## Render service

`render.yaml` describes a free Docker web service and a free PostgreSQL 16 database in Singapore. It builds `Dockerfile`, checks `/up`, and waits for linked GitHub CI checks before automatically deploying. Render generates a 256-bit `APP_KEY` secret. Laravel's config adds the `base64:` marker when the generated value decodes to 32 bytes, which is the AES-256-CBC key length. The Blueprint derives `APP_URL` from the service's assigned URL and supplies the database connection string through `DB_URL`. The container binds Apache to Render's `PORT`, runs additive migrations and the fictional demo seeder at startup, and builds Laravel's configuration, route, and view caches. The start script supervises both Apache and `php artisan schedule:work`; the Laravel schedule runs expired portfolio cleanup every ten seconds so the undo window completes even if the owner closes the browser.

Render's free web services have an ephemeral filesystem and sleep after inactivity. PortGen therefore stores records, sessions, and cache in PostgreSQL; files required after a restart must not be written to the container. Free services are suitable for a demonstration and may take about a minute to wake after sleeping. Render Free Postgres expires 30 days after creation. After expiration, it is inaccessible for a 14-day grace period; Render deletes the database and its data if it is not upgraded during that period. This deployment is for demo use and must not collect data that needs long-term retention. See [Render's free instance limits](https://render.com/docs/free), [Render Postgres](https://render.com/docs/postgresql), and [Render web services](https://render.com/docs/web-services).

Configure these values in the Render dashboard. Secret values must only be stored in Render's environment settings.

| Variable | Configuration |
|---|---|
| `APP_KEY` | Render-generated 256-bit base64 secret, normalized to Laravel's `base64:` form when needed. |
| `APP_URL` | Render's assigned HTTPS service URL, referenced through `RENDER_EXTERNAL_URL`. |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | Render Postgres private connection string, supplied through the Blueprint database reference. |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |

Render's Blueprint references the Postgres connection string without placing the generated username or password in Git. The application uses Laravel's `DB_URL` setting to parse that connection string. See [Render's Blueprint reference](https://render.com/docs/blueprint-spec) and [Render's default environment variables](https://render.com/docs/environment-variables).

## Database safety

The CI job owns an ephemeral local PostgreSQL 16 database named `portgen_test` and may run `migrate:fresh` and rollback/reapply against that service only. Tests must never use the Render production database. In production, run only additive migrations with `php artisan migrate --force`; never run `migrate:fresh` or `db:wipe`.

The demo seeder uses the fixed public path `/p/portgen001` and fictional profile details. It is idempotent and runs in the Render startup flow. The live smoke workflow checks `/up`, `/portfolios/create`, and that seeded portfolio.

## Live smoke check

After the Render service is created, add repository variable `PORTGEN_LIVE_URL` with its confirmed public HTTPS origin. The workflow runs after successful CI on `main` or `codex/render-deployment`, or by manual dispatch, and retries the health endpoint before checking the form and seeded public portfolio. The expected Render URL is not considered live until a real deployment responds successfully.

## Not yet verified

The local environment does not include Docker, so `docker build` and the container `/up` check have not run locally. No PortGen Render service, database, or live URL exists yet. The latest GitHub CI run is pending. Creating the Render resources and passing the build, migration, and live smoke checks remain required.

No production database migration has been attempted. The Blueprint binds the dedicated Render database and generated secrets when the resources are created. Keep the Free Postgres expiration date visible and upgrade or export required data before the 30-day expiration and 14-day grace period end.
