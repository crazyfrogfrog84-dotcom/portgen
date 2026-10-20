import fs from 'node:fs';
import path from 'node:path';
import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

async function capture(page, filename) {
    const destination = path.resolve('docs/screenshots/creative', filename);
    fs.mkdirSync(path.dirname(destination), { recursive: true });
    const staging = `${destination}.${process.pid}.${Date.now()}.tmp`;
    fs.writeFileSync(staging, await page.screenshot({ fullPage: true, type: 'png' }));
    fs.renameSync(staging, destination);
}

async function assertAxe(page, label) {
    const result = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
    expect(result.violations.filter(({ impact }) => ['serious', 'critical'].includes(impact)), label).toEqual([]);
}

async function assertNoHorizontalScroll(page, width) {
    await page.setViewportSize({ width, height: 812 });
    const sizes = await page.evaluate(() => ({ viewport: document.documentElement.clientWidth, content: document.documentElement.scrollWidth }));
    expect(sizes.content, `Creative template overflow at ${width}px`).toBeLessThanOrEqual(sizes.viewport);
}

test('Creative paper surfaces, SVG labels, and responsive layout remain accessible', async ({ page }) => {
    for (const fixture of ['demo-minimal', 'demo-rich']) {
        await page.setViewportSize({ width: 1280, height: 900 });
        await page.goto(`/__local/simple-fixture/${fixture}`);
        const publicUrl = await page.getByRole('link', { name: 'View the public portfolio' }).getAttribute('href');
        await page.goto(publicUrl);
        await expect(page.locator('[data-owner-toolbar]')).toBeVisible();
        await page.locator('.pf-template-control__desktop [data-template-option]').filter({ hasText: 'Creative' }).click();
        await expect(page.locator('.pf-page')).toHaveAttribute('data-template', 'creative');
        await expect(page.locator('.pf-icon').first()).toBeVisible();

        for (const [width, viewport] of [['1280', { width: 1280, height: 900 }], ['375', { width: 375, height: 812 }]]) {
            await page.setViewportSize(viewport);
            for (const theme of ['light', 'dark']) {
                await page.locator(`[data-theme-toggle] input[name="portfolio-theme"][value="${theme}"]`).check();
                const filename = `${fixture}-${width}-${theme}.png`;
                await assertAxe(page, filename);
                await capture(page, filename);
            }
            if (width === '375') await assertNoHorizontalScroll(page, 320);
        }
    }

    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/__local/simple-fixture/demo-rich');
    const publicUrl = await page.getByRole('link', { name: 'View the public portfolio' }).getAttribute('href');
    await page.goto(publicUrl);
    await page.locator('.pf-template-control__desktop [data-template-option]').filter({ hasText: 'Creative' }).click();
    await page.emulateMedia({ contrast: 'more' });
    for (const selector of ['.pf-hero__actions .button-primary', '.pf-skill-chip', '.pf-project-card--creative']) {
        const borderWidth = await page.locator(selector).first().evaluate((element) => getComputedStyle(element).borderTopWidth);
        expect(borderWidth, `${selector} should remain outlined in increased contrast`).toBe('2px');
    }
    await capture(page, 'demo-rich-1280-contrast-more.png');
});
