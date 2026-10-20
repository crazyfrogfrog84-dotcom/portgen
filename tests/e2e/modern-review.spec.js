import fs from 'node:fs';
import path from 'node:path';
import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

async function capture(page, filename) {
    const destination = path.resolve('docs/screenshots/modern', filename);
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

async function assertNoHorizontalScroll(page, width) {
    await page.setViewportSize({ width, height: 812 });
    const dimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        content: document.documentElement.scrollWidth,
    }));
    expect(dimensions.content, `Horizontal scroll at ${width}px: ${JSON.stringify(dimensions)}`).toBeLessThanOrEqual(dimensions.viewport);
}

async function assertHorizontalHeaderNav(page) {
    const tops = await page.locator('.pf-nav__link').evaluateAll((links) => links.map((link) => link.getBoundingClientRect().top));
    expect(Math.max(...tops) - Math.min(...tops), 'Modern header navigation should remain on one row at desktop width').toBeLessThan(4);
}

test('Modern glass surfaces keep parity, contrast, and responsive accessibility', async ({ page }) => {
    for (const fixture of ['demo-minimal', 'demo-rich']) {
        await page.setViewportSize({ width: 1280, height: 900 });
        await page.goto(`/__local/simple-fixture/${fixture}`);
        const publicUrl = await page.getByRole('link', { name: 'View the public portfolio' }).getAttribute('href');
        await page.goto(publicUrl);
        await expect(page.locator('[data-owner-toolbar]')).toBeVisible();
        await page.locator('.pf-template-control__desktop [data-template-option]').filter({ hasText: 'Modern' }).click();
        await expect(page.locator('.pf-page')).toHaveAttribute('data-template', 'modern');
        await expect(page.getByText('Previewing Modern. Not saved yet')).toBeVisible();

        for (const [width, viewport] of [['1280', { width: 1280, height: 900 }], ['375', { width: 375, height: 812 }]]) {
            await page.setViewportSize(viewport);
            if (width === '1280') await assertHorizontalHeaderNav(page);
            if (width === '375') {
                const templateButton = page.locator('.pf-template-control__mobile summary');
                await expect(templateButton).toBeVisible();
                await templateButton.click();
                await expect(templateButton).toHaveAttribute('aria-expanded', 'true');
                await page.keyboard.press('Escape');
                await expect(templateButton).toHaveAttribute('aria-expanded', 'false');
                await expect(templateButton).toBeFocused();
            }

            for (const theme of ['light', 'dark']) {
                await page.locator(`[data-theme-toggle] input[name="portfolio-theme"][value="${theme}"]`).check();
                const screenshot = `${fixture}-${width}-${theme}.png`;
                await assertAxe(page, screenshot);
                await capture(page, screenshot);
            }

            if (width === '375') await assertNoHorizontalScroll(page, 320);
        }
    }

    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/__local/simple-fixture/demo-rich');
    const publicUrl = await page.getByRole('link', { name: 'View the public portfolio' }).getAttribute('href');
    await page.goto(publicUrl);
    await expect(page.locator('[data-owner-toolbar]')).toBeVisible();
    await page.locator('.pf-template-control__desktop [data-template-option]').filter({ hasText: 'Modern' }).click();
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.emulateMedia({ contrast: 'more' });
    for (const selector of ['.button-primary', '.pf-skill-chip', '.pf-project-card']) {
        const borderWidth = await page.locator(selector).first().evaluate((element) => getComputedStyle(element).borderTopWidth);
        expect(borderWidth, `${selector} should keep a strong border in increased contrast`).toBe('2px');
    }
    await capture(page, 'demo-rich-1280-contrast-more.png');
});
