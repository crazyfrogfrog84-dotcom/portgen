import fs from 'node:fs';
import path from 'node:path';
import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

async function tabTo(page, locator, maximumTabs = 100) {
    for (let index = 0; index < maximumTabs; index++) {
        if (await locator.evaluate((element) => element === document.activeElement)) return;
        await page.keyboard.press('Tab');
    }
    throw new Error(`Could not reach ${await locator.toString()} using the keyboard.`);
}

async function captureScreenshot(page, outputPath) {
    const image = await page.screenshot({ fullPage: true, type: 'png' });
    const stagingPath = `${outputPath}.${process.pid}.${Date.now()}.tmp`;
    fs.writeFileSync(stagingPath, image);

    try {
        for (let attempt = 0; ; attempt++) {
            try {
                fs.renameSync(stagingPath, outputPath);
                return;
            } catch (error) {
                if (attempt >= 2) throw error;
                await new Promise((resolve) => setTimeout(resolve, 100 * (attempt + 1)));
            }
        }
    } finally {
        if (fs.existsSync(stagingPath)) fs.rmSync(stagingPath, { force: true });
    }
}

async function auditAndCapture(page, view, name) {
    const folder = path.resolve('docs/screenshots', view);
    fs.mkdirSync(folder, { recursive: true });
    const privateLink = page.locator('#owner-manage-link');
    const hasPrivateLink = await privateLink.count() > 0;
    const originalPrivateLink = hasPrivateLink ? await privateLink.inputValue() : null;
    if (hasPrivateLink) {
        await privateLink.evaluate((element) => { element.value = 'https://example.invalid/private-link-redacted'; });
    }

    try {
        await captureScreenshot(page, path.join(folder, `${name}.png`));

        const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
        const serious = results.violations.filter((violation) => ['serious', 'critical'].includes(violation.impact));
        expect(serious, `${view}/${name} has serious or critical axe violations`).toEqual([]);
        await assertTouchTargets(page);

        if (view === 'mobile') {
            await page.setViewportSize({ width: 320, height: 812 });
            const dimensions = await page.evaluate(() => ({ viewport: document.documentElement.clientWidth, content: document.documentElement.scrollWidth }));
            expect(dimensions.content, `${name} overflows at 320px: ${JSON.stringify(dimensions)}`).toBeLessThanOrEqual(dimensions.viewport);
            await page.setViewportSize({ width: 375, height: 812 });
        }
    } finally {
        if (hasPrivateLink) {
            await privateLink.evaluate((element, value) => { element.value = value; }, originalPrivateLink);
        }
    }
}

async function assertTouchTargets(page) {
    const controls = page.locator('a:visible, button:visible, input:visible, textarea:visible, select:visible, summary:visible, [role="button"]:visible');
    for (const control of await controls.all()) {
        const visuallyHidden = await control.evaluate((element) => element.classList.contains('sr-only') && !element.matches(':focus'));
        if (visuallyHidden) continue;
        const box = await control.boundingBox();
        if (!box) continue;
        expect(box.height, `Touch target is too short: ${await control.evaluate((element) => element.outerHTML)}`).toBeGreaterThanOrEqual(44);
        expect(box.width, `Touch target is too narrow: ${await control.evaluate((element) => element.outerHTML)}`).toBeGreaterThanOrEqual(44);
    }
}

async function openAndChoose(page, manageUrl, name, view) {
    await page.goto(manageUrl);
    await page.getByRole('link', { name: 'Change template' }).click();
    await page.getByRole('radio', { name: new RegExp(`^${name}`) }).check();
    await page.getByRole('button', { name: /Next: preview and share/ }).click();
    await expect(page.getByRole('heading', { name: /Manage portfolio for/ })).toBeVisible();
    const publicUrl = await page.getByRole('link', { name: 'View the public portfolio' }).getAttribute('href');
    await page.goto(publicUrl);
    await expect(page.locator(`article.theme-${name.toLowerCase()}`)).toBeVisible();
    await auditAndCapture(page, view, `template-${name.toLowerCase()}`);
    if (name === 'Simple') {
        await page.locator('[data-theme-toggle] input[name="portfolio-theme"][value="light"]').check();
        await auditAndCapture(page, view, 'template-simple-light');
        await page.locator('[data-theme-toggle] input[name="portfolio-theme"][value="dark"]').check();
        await auditAndCapture(page, view, 'template-simple-dark');
        await page.locator('#skills').evaluate((element) => window.scrollTo(0, element.getBoundingClientRect().top + window.scrollY - 120));
        await expect(page.locator('[data-section-nav] a[href="#skills"]')).toHaveAttribute('aria-current', 'true');
        await page.locator('[data-theme-toggle] input[name="portfolio-theme"][value="system"]').check();
    }
    return publicUrl;
}

test('keyboard-first create, publish, manage, template, and delete-undo flows stay accessible', async ({ page }) => {
    const viewports = [
        ['mobile', { width: 375, height: 812 }],
        ['desktop', { width: 1280, height: 800 }],
    ];

    for (const [view, viewport] of viewports) {
        await page.setViewportSize(viewport);
        await page.goto('/portfolios/create');
        await auditAndCapture(page, view, 'step-1-your-info');

        await page.keyboard.press('Tab');
        await expect(page.locator('.skip-link')).toBeFocused();
        await page.keyboard.press('Enter');
        await expect(page.locator('#main')).toBeFocused();

        await tabTo(page, page.locator('#full_name'));
        await page.keyboard.press('Tab');
        await expect(page.locator('#full_name')).toHaveAttribute('aria-invalid', 'true');
        await expect(page.locator('#full_name-client-error')).toHaveText('Name is required.');
        await page.keyboard.press('Shift+Tab');
        await page.keyboard.type('Avery Example');
        await tabTo(page, page.locator('#headline'));
        await page.keyboard.type('Designer building useful tools');
        await tabTo(page, page.locator('#about_me'));
        await page.keyboard.type('I make thoughtful digital products and accessible community tools.');
        await page.locator('#location').fill('Taipei, Taiwan');
        await page.locator('#contact_email').fill('avery@example.test');

        const skillSection = page.locator('[data-section]').filter({ has: page.locator('[data-repeat-group="skills"]') });
        const skillSummary = skillSection.locator('summary');
        await tabTo(page, skillSummary);
        await page.keyboard.press('Enter');
        const firstSkill = page.locator('[data-repeat-group="skills"] input').first();
        await tabTo(page, firstSkill);
        await page.keyboard.type('Research');
        await tabTo(page, page.locator('[data-add-row="skills"]'));
        await page.keyboard.press('Enter');
        await page.keyboard.type('Accessibility');
        await tabTo(page, page.locator('[data-add-row="skills"]'));
        await page.keyboard.press('Enter');
        await page.keyboard.type('Design');
        await page.keyboard.press('Shift+Tab');
        await expect(page.locator('[data-repeat-group="skills"] [data-remove-row]').nth(1)).toBeFocused();
        await page.keyboard.press('Enter');
        await expect(page.locator('[data-repeat-group="skills"] [data-repeat-row]')).toHaveCount(2);
        expect(await page.locator('[data-repeat-group="skills"] input').evaluateAll((fields) => fields.map((field) => [field.id, field.value]))).toEqual([
            ['skill-0', 'Research'],
            ['skill-1', 'Design'],
        ]);

        const projectSection = page.locator('[data-section]').filter({ has: page.locator('[data-repeat-group="projects"]') });
        await tabTo(page, projectSection.locator('summary'));
        await page.keyboard.press('Enter');
        await page.locator('#project-0-name').fill('Community Guide');
        await page.locator('#project-0-description').fill('An accessible guide to local services and community resources.');
        await page.locator('#project-0-url').fill('https://example.test/community-guide');
        await page.locator('#project-0-tags').fill('Accessibility, Research');

        await page.keyboard.press('Control+s');
        await expect(page.locator('[data-draft-status]')).toContainText('Draft saved on this device');
        expect(await page.evaluate(() => localStorage.getItem('portgen:portfolio-draft'))).toContain('Avery Example');
        await page.reload();
        const restoreDialog = page.locator('[data-draft-restore-dialog]');
        await expect(restoreDialog).toBeVisible();
        await page.keyboard.press('Enter');
        await expect(page.locator('#full_name')).toHaveValue('Avery Example');
        await expect(page.locator('[data-repeat-group="skills"] input')).toHaveCount(2);

        await page.keyboard.press('Alt+ArrowRight');
        await page.waitForURL(/select-template/);
        await expect(page.locator('body')).toHaveAttribute('data-clear-draft', 'true');
        expect(await page.evaluate(() => localStorage.getItem('portgen:portfolio-draft'))).toBeNull();
        await auditAndCapture(page, view, 'step-2-choose-template');

        await page.keyboard.press('Alt+ArrowRight');
        await expect(page.locator('[data-template-error]')).toHaveText('Choose a template to continue.');
        await tabTo(page, page.locator('input[name="template_choice"]').first());
        await page.keyboard.press('ArrowRight');
        await expect(page.locator('input[name="template_choice"]').nth(1)).toBeChecked();
        await page.keyboard.press('Alt+ArrowRight');
        await page.waitForURL(/\/p\//);
        const readyBanner = page.locator('[data-ready-banner]');
        await expect(readyBanner).toBeVisible();
        await expect(readyBanner).toContainText('Your portfolio is ready. Save your private link to manage it later.');
        const manageUrl = await readyBanner.locator('[data-copy-link]').getAttribute('data-copy-link');
        const publicUrl = page.url();
        await page.context().grantPermissions(['clipboard-read', 'clipboard-write'], { origin: new URL(publicUrl).origin });
        await readyBanner.getByRole('button', { name: 'Copy private link' }).click();
        await expect(readyBanner.locator('[data-copy-status]')).toContainText('Link copied');
        expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(manageUrl);
        await auditAndCapture(page, view, 'step-3-done-share');
        await readyBanner.getByRole('button', { name: 'Dismiss portfolio ready message' }).click();
        await expect(readyBanner).toBeHidden();
        await page.reload();
        await expect(page.locator('[data-ready-banner]')).toHaveCount(0);
        await page.goto(publicUrl);
        await expect(page.locator('article.theme-modern')).toBeVisible();
        await auditAndCapture(page, view, 'template-modern');

        await test.step('public theme choice persists after reload', async () => {
            const modernPortfolio = page.locator('[data-template="modern"]');
            await modernPortfolio.locator('input[name="portfolio-theme"][value="dark"]').check();
            await expect(modernPortfolio).toHaveAttribute('data-theme', 'dark');
            expect(await page.evaluate(() => localStorage.getItem('portgen:theme'))).toBe('dark');
            await page.reload();
            await expect(page.locator('[data-template="modern"]')).toHaveAttribute('data-theme', 'dark');
            await page.locator('[data-template="modern"] input[name="portfolio-theme"][value="system"]').check();
        });

        await page.goto(manageUrl);
        await expect(page.getByRole('heading', { name: /Manage portfolio for/ })).toBeVisible();
        await auditAndCapture(page, view, 'manage');
        await page.getByRole('link', { name: 'Edit portfolio' }).click();
        await expect(page.getByRole('heading', { name: 'Edit your portfolio' })).toBeVisible();
        await auditAndCapture(page, view, 'edit');
        await page.keyboard.press('Alt+ArrowLeft');
        await expect(page.getByRole('heading', { name: /Manage portfolio for/ })).toBeVisible();

        await openAndChoose(page, manageUrl, 'Simple', view);
        await openAndChoose(page, manageUrl, 'Creative', view);
        await page.goto(publicUrl);

        const invalidManageUrl = new URL(manageUrl);
        invalidManageUrl.searchParams.delete('signature');
        for (const status of ['403', '404', '419', '429', '500']) {
            const pathName = status === '403' ? `${invalidManageUrl.pathname}${invalidManageUrl.search}` : status === '404' ? '/p/doesnotexist' : `/__local-errors/${status}`;
            await page.goto(pathName);
            await expect(page.getByRole('heading', { name: new RegExp(status === '404' ? 'could not find' : status === '403' ? 'private link is not valid' : status === '419' ? 'form has expired' : status === '429' ? 'too many requests' : 'something went wrong', 'i') })).toBeVisible();
            await auditAndCapture(page, view, `error-${status}`);
        }

        await page.setViewportSize({ width: 320, height: 812 });
        await page.goto('/portfolios/create');
        const mobileWidth = await page.evaluate(() => ({ viewport: document.documentElement.clientWidth, content: document.documentElement.scrollWidth }));
        expect(mobileWidth.content, `Horizontal overflow at 320px: ${JSON.stringify(mobileWidth)}`).toBeLessThanOrEqual(mobileWidth.viewport);
        await expect(page.getByRole('heading', { name: 'Create your portfolio' })).toBeVisible();

        await page.setViewportSize({ width: 640, height: 900 });
        await page.evaluate(() => { document.documentElement.style.zoom = '200%'; });
        await expect(page.getByRole('heading', { name: 'Create your portfolio' })).toBeVisible();
        await page.evaluate(() => { document.documentElement.style.zoom = '100%'; });

        await page.goto(manageUrl);
        const deleteButton = page.getByRole('button', { name: 'Delete portfolio' });
        await tabTo(page, deleteButton);
        await page.keyboard.press('Enter');
        const deleteDialog = page.locator('[data-delete-confirmation]');
        await expect(deleteDialog).toBeVisible();
        await expect(deleteDialog.getByRole('heading', { name: /Delete Avery Example's portfolio/ })).toBeVisible();
        await page.keyboard.press('Tab');
        await page.keyboard.press('Enter');
        await page.waitForURL(/\/deleted\?/);
        await expect(page.getByRole('heading', { name: /Avery Example's portfolio is deleted/ })).toBeVisible();
        await auditAndCapture(page, view, 'delete-undo');
        const undoButton = page.getByRole('button', { name: 'Undo deletion' });
        await tabTo(page, undoButton);
        await page.keyboard.press('Enter');
        await page.waitForURL(/\/manage\//);
        await expect(page.getByRole('heading', { name: /Manage portfolio for Avery Example/ })).toBeVisible();

        await page.goto('/help');
        await auditAndCapture(page, view, 'help-faq');
        await page.getByRole('button', { name: /Shortcuts/ }).click();
        await expect(page.locator('[data-help-dialog]')).toBeVisible();
        await auditAndCapture(page, view, 'keyboard-shortcuts');
        await page.keyboard.press('Escape');

        await page.emulateMedia({ colorScheme: 'dark' });
        await page.goto(publicUrl);
        const creativeDarkPalette = await page.locator('[data-template="creative"]').evaluate((element) => {
            const probe = document.createElement('span');
            probe.style.backgroundColor = 'var(--bg)';
            element.append(probe);
            const tokenBackground = getComputedStyle(probe).backgroundColor;
            probe.remove();

            return {
                theme: element.dataset.theme,
                background: getComputedStyle(element).backgroundColor,
                tokenBackground,
            };
        });
        expect(creativeDarkPalette.theme).toBe('system');
        expect(creativeDarkPalette.background).toBe(creativeDarkPalette.tokenBackground);
        await auditAndCapture(page, view, 'dark-mode-creative');
        await page.emulateMedia({ colorScheme: 'light' });
    }
});
