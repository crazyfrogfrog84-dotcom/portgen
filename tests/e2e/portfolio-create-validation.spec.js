import { expect, test } from '@playwright/test';

test('collapsed required fields expand and focus with a linked error summary', async ({ page }) => {
    const consoleErrors = [];
    const pageErrors = [];
    await page.addInitScript(() => document.addEventListener('securitypolicyviolation', (event) => {
        window.__cspViolations ??= [];
        window.__cspViolations.push(`${event.violatedDirective}: ${event.blockedURI}`);
    }));
    page.on('console', (message) => { if (message.type() === 'error') consoleErrors.push(message.text()); });
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await page.goto('/portfolios/create');
    await page.locator('#full_name').fill('Avery Example');
    await page.locator('#headline').fill('Designer');

    const basics = page.locator('[data-section-required]').first();
    await basics.locator('summary').click();
    await expect(basics).not.toHaveAttribute('open', '');

    const skills = page.locator('[data-section-required]').nth(1);
    await expect(skills).not.toHaveAttribute('open', '');
    await page.getByRole('button', { name: 'Next: choose a template' }).click();

    await expect(basics).toHaveAttribute('open', '');
    await expect(page.locator('#about_me')).toBeFocused();
    await expect(page.locator('[data-form-error-summary]')).toContainText('Fix 2 things: About me is required, Add at least one skill');
    await expect(page.locator('[data-form-error-summary] a[href="#about_me"]')).toBeVisible();
    await expect(page.locator('[data-form-error-summary] a[href="#skill-0"]')).toBeVisible();
    await expect(skills.locator('[data-section-completion]')).toHaveText('Needs attention');
    expect(consoleErrors).toEqual([]);
    expect(pageErrors).toEqual([]);
    expect(await page.evaluate(() => window.__cspViolations ?? [])).toEqual([]);
});

test('create, choose Simple, and publish completes without browser errors', async ({ page }) => {
    const consoleErrors = [];
    const pageErrors = [];
    page.on('console', (message) => { if (message.type() === 'error') consoleErrors.push(message.text()); });
    page.on('pageerror', (error) => pageErrors.push(error.message));

    const name = 'Avery Create Flow';
    await page.goto('/portfolios/create');
    await page.locator('#full_name').fill(name);
    await page.locator('#headline').fill('Designer building useful tools');
    await page.locator('#about_me').fill('I make thoughtful digital products and accessible community tools.');
    const skills = page.locator('[data-section-required]').nth(1);
    await skills.locator('summary').click();
    await page.locator('#skill-0').fill('Research');

    await page.getByRole('button', { name: 'Next: choose a template' }).click();
    await expect(page.getByRole('heading', { name: 'Choose the look that fits your work' })).toBeVisible();
    await page.getByRole('radio', { name: /^Simple/ }).check();
    await page.getByRole('button', { name: 'Next: preview and share' }).click();
    await expect(page).toHaveURL(/\/p\//);
    await expect(page.getByRole('heading', { level: 1, name })).toBeVisible();
    await expect(page.locator('article.theme-simple')).toBeVisible();
    expect(consoleErrors).toEqual([]);
    expect(pageErrors).toEqual([]);
});
