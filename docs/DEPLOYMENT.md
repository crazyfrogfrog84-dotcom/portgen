# Deployment configuration

## Current status

The Render service is named `portgen-m1uh` and is configured to deploy from `main`. It is live at [https://portgen-m1uh-if1e.onrender.com](https://portgen-m1uh-if1e.onrender.com). Render assigned this public URL; its `-if1e` suffix is part of the working address.

The Laravel 12.70.0 dependency set passes the Composer audit recorded for this checkout. The previous Laravel 11 advisories were addressed before preparing this release. The workstation has no Docker installation, so the production image will be built by GitHub CI. The PostgreSQL tests and image build must pass before Render auto-deploys the branch.

## Render service

`render.yaml` describes a Free Docker web service and a Free PostgreSQL 16 database in Singapore. It builds `Dockerfile`, checks `/up`, and waits for GitHub CI checks. Render generates a 256-bit `APP_KEY` secret; Laravel normalizes it into a stable 32-byte AES-256 key. Laravel trusts only Render's forwarded protocol so pages generate HTTPS asset URLs after Render terminates TLS. The Blueprint derives `APP_URL` from the assigned service URL and supplies the database connection string through `DB_URL`. The container binds Apache to Render's `PORT`, runs additive migrations and the fictional demo seeder at startup, and builds Laravel's configuration, route, and view caches. The start script supervises both Apache and `php artisan schedule:work`; the Laravel schedule runs expired portfolio cleanup every ten seconds so the undo window completes even if the owner closes the browser.

For the existing service, keep **Settings → Auto-Deploy** set to **After CI Checks Pass**, matching `autoDeployTrigger: checksPass` in the Blueprint. Render can disable automatic deploys when a specific commit is deployed manually. If a Blueprint has Auto Sync disabled, open its page and select **Manual Sync** to apply committed `render.yaml` changes. For an immediate deploy of the latest linked `main` commit, use the service's **Deploys → Manual Deploy → Deploy latest commit** action; avoid **Deploy a specific commit** when automatic deploys should stay enabled.

Render's free web services have an ephemeral filesystem and sleep after inactivity. PortGen stores records, sessions, cache, and optimized portfolio image bytes in PostgreSQL; uploaded images are not written to the container. Images accept JPEG, PNG, and WebP uploads up to 2 MB and are re-encoded to at most 1200 px on the long edge and about 300 KB. They are public portfolio content and their EXIF metadata is stripped during re-encoding. Free services are suitable for a demonstration and may take about a minute to wake after sleeping. Render Free Postgres expires 30 days after creation. After expiration, it is inaccessible for a 14-day grace period; Render deletes the database and its data if it is not upgraded during that period. This deployment is for demo use and must not collect data that needs long-term retention. See [Render's free instance limits](https://render.com/docs/free), [Render Postgres](https://render.com/docs/postgresql), and [Render web services](https://render.com/docs/web-services).

Configure these values in the Render dashboard. Secret values must only be stored in Render's environment settings.

| Variable | Configuration |
|---|---|
| `APP_KEY` | Render-generated secret, normalized to a stable 32-byte AES-256 key. |
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

## Live verification

The live service responded successfully to direct checks: `/up`, `/portfolios/create`, and `/p/portgen001` returned HTTP 200. The root URL redirects to the creation form. The GitHub live-smoke workflow still requires the public repository variable `PORTGEN_LIVE_URL` to run automatically.

## Verification notes

GitHub CI passed for the deployed change. Render built the Docker image, applied additive migrations, seeded the fictional demo portfolio, and marked the service Live. This workstation has no Docker installation, so the production container was built by Render rather than locally. No destructive production database command was run.

The Blueprint binds the dedicated Render database and generated secrets. Keep the Free Postgres expiration date visible and export any required data before the 30-day expiration and 14-day grace period end.
