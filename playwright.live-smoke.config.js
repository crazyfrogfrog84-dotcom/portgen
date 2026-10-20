import { defineConfig } from '@playwright/test';

if (!process.env.PORTGEN_BASE_URL) throw new Error('PORTGEN_BASE_URL is required for the live smoke test.');

export default defineConfig({
    testDir: './tests/e2e',
    testMatch: 'live-create-smoke.spec.js',
    fullyParallel: false,
    workers: 1,
    timeout: 120_000,
    expect: { timeout: 20_000 },
    reporter: [['list']],
    use: {
        baseURL: process.env.PORTGEN_BASE_URL.replace(/\/$/, ''),
        browserName: 'chromium',
        headless: true,
        trace: 'off',
        actionTimeout: 10_000,
        navigationTimeout: 30_000,
    },
});
