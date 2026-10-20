import { randomUUID } from 'node:crypto';
import { expect, test } from '@playwright/test';

test('live guest can create and render a throwaway portfolio', async ({ page }) => {
    test.skip(!process.env.PORTGEN_LIVE_SMOKE, 'This test runs only in the live deployment smoke workflow.');

    const name = `PortGen Smoke ${randomUUID().slice(0, 8)}`;
    let manageUrl = null;
    let flowError = null;

    try {
        await page.goto('/portfolios/create');
        await expect(page.locator('[data-portfolio-form]')).toHaveAttribute('novalidate', '');
        await page.locator('#full_name').fill(name);
        await page.locator('#headline').fill('Deployment smoke check');
        await page.locator('#about_me').fill('A temporary portfolio used to verify the live create and render flow.');
        await page.locator('[data-section-required]').nth(1).locator('summary').click();
        await page.locator('#skill-0').fill('Smoke testing');

        await page.getByRole('button', { name: 'Next: choose a template' }).click();
        await expect(page.getByRole('heading', { name: 'Choose the look that fits your work' })).toBeVisible();
        await page.getByRole('radio', { name: /^Simple/ }).check();
        await page.getByRole('button', { name: 'Next: preview and share' }).click();
        await expect(page).toHaveURL(/\/p\//);
        const publicUrl = page.url();
        manageUrl = await page.locator('[data-ready-banner] [data-copy-link]').getAttribute('data-copy-link');
        await page.goto(publicUrl);
        await expect(page.getByRole('heading', { level: 1, name })).toBeVisible();
    } catch (error) {
        flowError = error;
    } finally {
        if (manageUrl) {
            try {
                await page.goto(manageUrl);
                await page.getByRole('button', { name: 'Delete portfolio' }).click();
                await page.locator('[data-delete-confirmation]').getByRole('button', { name: 'Yes, delete this portfolio' }).click();
                await page.waitForURL(/\/manage\/[^/]+\/deleted/);
            } catch (cleanupError) {
                if (!flowError) flowError = cleanupError;
            }
        }
    }

    if (flowError) throw new Error(`Live portfolio create/render smoke failed: ${flowError.message}`);
});
