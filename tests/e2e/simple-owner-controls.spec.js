import { expect, test } from '@playwright/test';

function watch(page, issues) {
    page.addInitScript(() => {
        window.__portgenCspViolations = [];
        document.addEventListener('securitypolicyviolation', (event) => {
            window.__portgenCspViolations.push({ directive: event.violatedDirective, blocked: event.blockedURI });
        });
    });
    page.on('console', (message) => {
        if (message.type() === 'error') issues.console.push(message.text());
    });
    page.on('pageerror', (error) => issues.page.push(error.message));
    page.on('requestfailed', (request) => {
        issues.failed.push(`${request.method()} ${new URL(request.url()).pathname}`);
    });
    page.on('response', (response) => {
        if (response.status() >= 400) issues.badResponses.push(`${response.status()} ${new URL(response.url()).pathname}`);
    });
}

async function collectCsp(page, issues) {
    issues.csp.push(...await page.evaluate(() => window.__portgenCspViolations ?? []));
}

async function createPortfolio(page, name) {
    await page.goto('/portfolios/create');
    await page.locator('#full_name').fill(name);
    await page.locator('#headline').fill('Designer building useful public tools');
    await page.locator('#about_me').fill('I make accessible products and useful community services.');
    const skills = page.locator('[data-section-required]').nth(1);
    await skills.locator('summary').click();
    await page.locator('#skill-0').fill('Accessible design');
    await page.getByRole('button', { name: 'Next: choose a template' }).click();
    await expect(page.getByRole('heading', { name: 'Choose the look that fits your work' })).toBeVisible();
    await page.getByRole('radio', { name: /^Simple/ }).check();
    await page.getByRole('button', { name: 'Next: preview and share' }).click();
    await expect(page).toHaveURL(/\/p\//);

    return page.url();
}

async function register(page, label) {
    const email = `simple-owner-${label}-${Date.now()}@example.test`;
    await page.goto('/register');
    await page.getByLabel('Your name').fill(`Simple ${label} Owner`);
    await page.getByLabel('Email address').fill(email);
    await page.getByLabel('Password', { exact: true }).fill('Example-Demo-Password-8');
    await page.getByLabel('Confirm password').fill('Example-Demo-Password-8');
    await page.getByRole('button', { name: 'Create account' }).click();
    await expect(page).toHaveURL(/my-portfolios/);
}

test('Simple owner controls work only for the current owner without browser or CSP errors', async ({ page, browser }) => {
    test.setTimeout(240_000);
    const issues = { console: [], page: [], csp: [], failed: [], badResponses: [] };
    watch(page, issues);

    // Guest creation exercises the creation banner and sets the manage session in this browser.
    const publicUrl = await createPortfolio(page, 'Guest Simple Owner');
    const privateUrl = await page.locator('[data-ready-banner] [data-copy-link]').getAttribute('data-copy-link');
    expect(privateUrl).toBeTruthy();
    await expect(page.locator('[data-ready-banner]')).toContainText('Your portfolio is ready. Save your private link to manage it later.');
    await expect(page.locator('[data-ready-banner]')).toContainText('Copy private link');
    await page.locator('[data-ready-banner] [data-dismiss-ready]').click();
    await page.reload();
    await expect(page.locator('[data-ready-banner]')).toHaveCount(0);
    await expect(page.locator('[data-owner-toolbar]')).toBeVisible();

    // Preview and cancel leave the saved choice alone; Save persists the selected template.
    await page.getByRole('radio', { name: 'Modern' }).click();
    await expect(page).toHaveURL(/preview=Modern/);
    await expect(page.getByText('Previewing Modern. Not saved yet')).toBeVisible();
    await page.getByRole('link', { name: 'Cancel' }).click();
    await expect(page.locator('.pf-page')).toHaveAttribute('data-template', 'simple');
    await page.getByRole('radio', { name: 'Creative' }).click();
    await page.getByRole('button', { name: 'Save template' }).click();
    await expect(page.locator('.pf-page')).toHaveAttribute('data-template', 'creative');
    await expect(page.getByRole('status').filter({ hasText: 'Template saved' })).toBeVisible();
    await page.getByRole('radio', { name: 'Simple' }).click();
    await page.getByRole('link', { name: 'Cancel' }).click();
    await expect(page.locator('.pf-page')).toHaveAttribute('data-template', 'creative');
    await page.getByRole('radio', { name: 'Simple' }).click();
    await page.getByRole('button', { name: 'Save template' }).click();
    await expect(page.locator('.pf-page')).toHaveAttribute('data-template', 'simple');

    // Manage menu destinations and private-link copy feedback.
    const manageMenu = page.locator('.pf-manage-control');
    await manageMenu.locator('summary').click();
    await expect(manageMenu.getByRole('link', { name: 'Edit portfolio' })).toHaveAttribute('href', /\/edit/);
    await expect(manageMenu.getByRole('link', { name: 'Manage page' })).toHaveAttribute('href', /\/manage\//);
    await expect(manageMenu.getByRole('link', { name: 'My portfolios' })).toHaveCount(0);
    await page.context().grantPermissions(['clipboard-read', 'clipboard-write'], { origin: new URL(publicUrl).origin });
    await manageMenu.getByRole('button', { name: 'Copy private link' }).click();
    await expect(manageMenu.getByRole('status').filter({ hasText: 'Link copied' })).toBeVisible();
    expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(privateUrl);
    await manageMenu.locator('summary').click();

    // Theme selection survives a reload for all three settings.
    for (const theme of ['light', 'dark', 'system']) {
        await page.locator(`[data-theme-toggle] input[name="portfolio-theme"][value="${theme}"]`).check();
        await expect(page.locator('.pf-page')).toHaveAttribute('data-theme', theme);
        await page.reload();
        await expect(page.locator('.pf-page')).toHaveAttribute('data-theme', theme);
        expect(await page.evaluate(() => localStorage.getItem('portgen:theme'))).toBe(theme);
    }

    // A new browser without the owner session can open the signed manage link and become its guest owner.
    const freshGuestContext = await browser.newContext();
    const freshGuestPage = await freshGuestContext.newPage();
    watch(freshGuestPage, issues);
    await freshGuestPage.goto(privateUrl);
    await freshGuestPage.getByRole('link', { name: 'View the public portfolio' }).click();
    await expect(freshGuestPage.locator('[data-owner-toolbar]')).toBeVisible();
    await collectCsp(freshGuestPage, issues);
    await freshGuestContext.close();

    // A visitor and a different account both ignore preview requests and see no owner actions.
    const visitorContext = await browser.newContext();
    const visitorPage = await visitorContext.newPage();
    watch(visitorPage, issues);
    await visitorPage.goto(`${publicUrl}?preview=Modern`);
    await expect(visitorPage.locator('.pf-page')).toHaveAttribute('data-template', 'simple');
    await expect(visitorPage.locator('[data-owner-toolbar]')).toHaveCount(0);
    await expect(visitorPage.locator('[data-theme-toggle]')).toBeVisible();
    await collectCsp(visitorPage, issues);
    await visitorContext.close();

    const accountOwnerContext = await browser.newContext();
    const accountOwnerPage = await accountOwnerContext.newPage();
    watch(accountOwnerPage, issues);
    await register(accountOwnerPage, 'account');
    const accountPublicUrl = await createPortfolio(accountOwnerPage, 'Account Simple Owner');
    await expect(accountOwnerPage.locator('[data-owner-toolbar]')).toBeVisible();
    const accountMenu = accountOwnerPage.locator('.pf-manage-control');
    await accountMenu.locator('summary').click();
    await expect(accountMenu.getByRole('link', { name: 'My portfolios' })).toBeVisible();
    await expect(accountMenu.getByRole('button', { name: 'Copy private link' })).toHaveCount(0);
    await collectCsp(accountOwnerPage, issues);
    await accountOwnerContext.close();

    const otherContext = await browser.newContext();
    const otherPage = await otherContext.newPage();
    watch(otherPage, issues);
    await register(otherPage, 'other');
    await otherPage.goto(`${accountPublicUrl}?preview=Creative`);
    await expect(otherPage.locator('.pf-page')).toHaveAttribute('data-template', 'simple');
    await expect(otherPage.locator('[data-owner-toolbar]')).toHaveCount(0);
    await collectCsp(otherPage, issues);
    await otherContext.close();

    await collectCsp(page, issues);
    expect(issues, 'Browser console, CSP, and network diagnostics').toEqual({ console: [], page: [], csp: [], failed: [], badResponses: [] });
});
