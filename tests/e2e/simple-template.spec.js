import fs from 'node:fs';
import path from 'node:path';
import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

async function capture(page, filename) {
    const destination = path.resolve('docs/screenshots/simple', filename);
    fs.mkdirSync(path.dirname(destination), { recursive: true });
    const staging = `${destination}.${process.pid}.${Date.now()}.tmp`;
    fs.writeFileSync(staging, await page.screenshot({ fullPage: true, type: 'png' }));
    fs.renameSync(staging, destination);
}

async function assertAxe(page, name) {
    const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
    const serious = results.violations.filter(({ impact }) => ['serious', 'critical'].includes(impact));
    expect(serious, `${name}: serious or critical axe violations`).toEqual([]);
}

async function noHorizontalScroll(page, width) {
    await page.setViewportSize({ width, height: 812 });
    const dimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        content: document.documentElement.scrollWidth,
    }));
    expect(dimensions.content, `Horizontal scroll at ${width}px: ${JSON.stringify(dimensions)}`).toBeLessThanOrEqual(dimensions.viewport);
}

test('Simple owner controls, contrast modes, and minimal/rich screenshots remain accessible', async ({ page, context }) => {
    const fixtures = ['demo-minimal', 'demo-rich'];
    const sizes = [
        ['375', { width: 375, height: 812 }],
        ['1280', { width: 1280, height: 900 }],
    ];

    await context.grantPermissions(['clipboard-read', 'clipboard-write']);

    for (const fixture of fixtures) {
        await page.goto(`/__local/simple-fixture/${fixture}`);
        await expect(page.getByRole('heading', { name: `Manage portfolio for ${fixture === 'demo-rich' ? 'Avery Chen' : 'Morgan Lin'}` })).toBeVisible();
        const publicUrl = await page.getByRole('link', { name: 'View the public portfolio' }).getAttribute('href');

        for (const [widthLabel, viewport] of sizes) {
            await page.setViewportSize(viewport);
            await page.goto(publicUrl);
            await expect(page.locator('[data-owner-toolbar]')).toBeVisible();

            if (widthLabel === '375') {
                const templateButton = page.getByText('Template: Simple', { exact: true });
                await expect(templateButton).toBeVisible();
                await templateButton.click();
                await expect(page.getByRole('radiogroup', { name: 'Template' })).toBeVisible();
                await expect(templateButton).toHaveAttribute('aria-expanded', 'true');
                await page.keyboard.press('Escape');
                await expect(templateButton).toHaveAttribute('aria-expanded', 'false');
                await expect(templateButton).toBeFocused();
            } else {
                await expect(page.getByRole('radiogroup', { name: 'Template' })).toBeVisible();
                const simpleChoice = page.getByRole('radio', { name: 'Simple' }).first();
                await simpleChoice.focus();
                await page.keyboard.press('Tab');
                await expect(page.locator('[data-owner-disclosure]').last().locator('summary')).toBeFocused();
                await page.keyboard.press('Tab');
                await expect(page.locator('[data-theme-toggle] input[name="portfolio-theme"]:checked')).toBeFocused();
                await simpleChoice.focus();
                await page.keyboard.press('ArrowRight');
                await expect(page).toHaveURL(/preview=Modern/);
                await expect(page.getByText('Previewing Modern. Not saved yet')).toBeVisible();
                await page.getByRole('link', { name: 'Cancel' }).click();
                await expect(page).not.toHaveURL(/preview=/);
            }

            const manageSummary = page.locator('[data-owner-disclosure]').filter({ has: page.getByText('Manage', { exact: true }) }).locator('summary');
            await manageSummary.click();
            await expect(page.getByRole('link', { name: 'Edit portfolio' })).toBeVisible();
            await expect(page.getByRole('link', { name: 'Manage page' })).toBeVisible();
            for (const control of [
                page.getByRole('link', { name: 'Edit portfolio' }),
                page.getByRole('link', { name: 'Manage page' }),
                page.getByRole('button', { name: 'Copy private link' }),
            ]) {
                await expect(control).toHaveCSS('border-top-width', '1px');
            }
            await page.getByRole('button', { name: 'Copy private link' }).click();
            await expect(page.getByRole('status').filter({ hasText: 'Link copied' })).toBeVisible();
            await manageSummary.click();

            for (const theme of ['light', 'dark']) {
                await page.locator(`[data-theme-toggle] input[name="portfolio-theme"][value="${theme}"]`).check();
                await expect(page.locator('.pf-page')).toHaveAttribute('data-theme', theme);
                const screenshotName = `${fixture}-${widthLabel}-${theme}.png`;
                await assertAxe(page, screenshotName);
                await capture(page, screenshotName);
            }

            if (widthLabel === '375') await noHorizontalScroll(page, 320);
        }
    }

    await page.goto('/__local/simple-fixture/demo-rich');
    const publicUrl = await page.getByRole('link', { name: 'View the public portfolio' }).getAttribute('href');
    await page.goto(publicUrl);
    await page.setViewportSize({ width: 1280, height: 900 });

    await page.emulateMedia({ contrast: 'more' });
    for (const selector of ['.button-primary', '.pf-skill-chip', '.pf-project-card']) {
        const borderWidth = await page.locator(selector).first().evaluate((element) => getComputedStyle(element).borderTopWidth);
        expect(borderWidth, `${selector} should keep a strong border in increased contrast`).toBe('2px');
    }
    await capture(page, 'demo-rich-1280-contrast-more.png');
    await page.emulateMedia({ forcedColors: 'active' });
    for (const selector of ['.button-primary', '.pf-skill-chip', '.pf-project-card']) {
        const borderWidth = await page.locator(selector).first().evaluate((element) => getComputedStyle(element).borderTopWidth);
        expect(borderWidth, `${selector} should keep a strong border in forced colors`).toBe('2px');
    }
});
