import path from 'node:path';
import os from 'node:os';
import { createHash } from 'node:crypto';
import { defineConfig } from '@playwright/test';

const baseURL = process.env.PORTGEN_BASE_URL || 'http://127.0.0.1:8000';
const databaseConnection = process.env.PORTGEN_E2E_DB_CONNECTION || 'sqlite';
const workspaceKey = createHash('sha256').update(process.cwd()).digest('hex').slice(0, 12);
const sqliteDatabase = path.join(os.tmpdir(), `portgen-browser-${workspaceKey}.sqlite`);
const database = databaseConnection === 'sqlite' ? sqliteDatabase : process.env.DB_DATABASE;

export default defineConfig({
    testDir: './tests/e2e',
    globalSetup: './tests/e2e/global-setup.js',
    fullyParallel: false,
    workers: 1,
    timeout: 120_000,
    expect: { timeout: 15_000 },
    reporter: [['list']],
    use: {
        baseURL,
        browserName: 'chromium',
        headless: true,
        trace: 'retain-on-failure',
        actionTimeout: 10_000,
        navigationTimeout: 20_000,
    },
    webServer: {
        command: `php artisan serve --host=127.0.0.1 --port=${new URL(baseURL).port || 80}`,
        url: `${baseURL}/up`,
        reuseExistingServer: false,
        timeout: 45_000,
        env: {
            ...process.env,
            APP_ENV: 'local',
            APP_DEBUG: 'false',
            DB_CONNECTION: databaseConnection,
            DB_DATABASE: database,
            DB_URL: '',
            DB_HOST: databaseConnection === 'sqlite' ? '127.0.0.1' : process.env.DB_HOST,
            SESSION_DRIVER: 'file',
        },
    },
});
