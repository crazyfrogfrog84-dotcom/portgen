import fs from 'node:fs';
import path from 'node:path';
import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

const fixtures = ['demo-minimal', 'demo-rich', 'demo-stress'];
const widths = [320, 375, 768, 1280, 1920];

async function screenshotAndAudit(page, phase, fixture, width, theme, audience) {
    await page.evaluate(() => document.fonts.ready);
    const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();
    expect(results.violations.filter(({ impact }) => ['serious', 'critical'].includes(impact)), `${fixture} ${width}px ${theme} ${audience}`)
        .toEqual([]);

    const dimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        content: document.documentElement.scrollWidth,
    }));
    expect(dimensions.content, `${fixture} ${width}px ${theme} ${audience} horizontal overflow`)
        .toBeLessThanOrEqual(dimensions.viewport);

    const folder = path.resolve('docs/screenshots/simple', phase);
    fs.mkdirSync(folder, { recursive: true });
    const filename = `${fixture}-${width}-${theme}-${audience}.png`;
    const destination = path.join(folder, filename);
    const staging = `${destination}.${process.pid}.tmp`;
    const screenshot = await page.screenshot({ fullPage: true, animations: 'disabled' });
    fs.writeFileSync(staging, screenshot);
    try {
        fs.renameSync(staging, destination);
    } catch (error) {
        if (!['EPERM', 'EEXIST'].includes(error.code)) throw error;
        fs.writeFileSync(destination, screenshot);
        fs.unlinkSync(staging);
    }
}

async function captureView(page, phase, fixture, audience) {
    for (const width of widths) {
        await page.setViewportSize({ width, height: 900 });
        for (const theme of ['light', 'dark']) {
            await page.emulateMedia({ colorScheme: theme });
            await page.evaluate(() => localStorage.removeItem('portgen:theme'));
            await page.reload();
            await expect(page.locator('.pf-page')).toHaveAttribute('data-theme', 'system');
            if (audience === 'owner') await expect(page.locator('[data-owner-toolbar]')).toBeVisible();
            else await expect(page.locator('[data-owner-toolbar]')).toHaveCount(0);
            await screenshotAndAudit(page, phase, fixture, width, theme, audience);
        }
    }
}

test('Simple visual review captures minimal, rich, and stress fixtures across sizes and modes', async ({ page, browser }) => {
    test.setTimeout(600_000);
    const phase = process.env.PORTGEN_SCREENSHOT_PHASE || 'after';

    for (const fixture of fixtures) {
        await page.goto(`/__local/simple-fixture/${fixture}`);
        const publicUrl = await page.getByRole('link', { name: 'View the public portfolio' }).getAttribute('href');
        await page.goto(publicUrl);
        await expect(page.locator('[data-owner-toolbar]')).toBeVisible();
        await captureView(page, phase, fixture, 'owner');

        const visitorContext = await browser.newContext({ viewport: { width: 1280, height: 900 } });
        const visitorPage = await visitorContext.newPage();
        await visitorPage.goto(publicUrl);
        await expect(visitorPage.locator('[data-owner-toolbar]')).toHaveCount(0);
        await captureView(visitorPage, phase, fixture, 'visitor');
        await visitorContext.close();
    }
});
