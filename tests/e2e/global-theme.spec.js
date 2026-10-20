import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

async function audit(page) {
    const dimensions = await page.evaluate(() => ({
        width: document.documentElement.clientWidth,
        scrollWidth: document.documentElement.scrollWidth,
        overflowing: [...document.body.querySelectorAll('*')]
            .map((element) => ({ tag: element.tagName.toLowerCase(), id: element.id, className: element.className, right: Math.round(element.getBoundingClientRect().right) }))
            .filter(({ right }) => right > document.documentElement.clientWidth + 1)
            .slice(0, 8),
    }));
    expect(dimensions.scrollWidth, `the page should not scroll horizontally: ${JSON.stringify(dimensions.overflowing)}`).toBeLessThanOrEqual(dimensions.width);

    const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
    expect(results.violations.filter(({ impact }) => ['serious', 'critical'].includes(impact))).toEqual([]);
}

test('theme selection persists across application, portfolio, and error pages', async ({ page }) => {
    test.setTimeout(120_000);
    await page.setViewportSize({ width: 320, height: 800 });
    await page.goto('/login');
    await page.evaluate(() => localStorage.removeItem('portgen:theme'));
    await page.reload();
    const appTheme = page.locator('[data-theme-scope="app"]');
    await expect(appTheme).toBeVisible();
    await appTheme.getByLabel('Use dark theme').check();
    await expect(page.locator('html')).toHaveAttribute('data-app-theme', 'dark');
    await expect(page.locator('body')).toHaveCSS('background-color', 'rgb(16, 24, 32)');
    await audit(page);

    for (const path of ['/help', '/portfolios/create']) {
        await page.goto(path);
        await expect(page.locator('[data-theme-scope="app"] input[value="dark"]')).toBeChecked();
        await expect(page.locator('html')).toHaveAttribute('data-app-theme', 'dark');
        await audit(page);
    }

    await page.goto('/p/demo-min');
    await expect(page.locator('[data-theme-scope="portfolio"] input[value="dark"]')).toBeChecked();
    await expect(page.locator('.pf-page')).toHaveAttribute('data-theme', 'dark');
    await expect(page.locator('html')).toHaveAttribute('data-app-theme', 'dark');
    await page.locator('[data-theme-scope="portfolio"] input[value="light"]').check();
    await expect(page.locator('html')).toHaveAttribute('data-app-theme', 'light');
    await expect(page.locator('.pf-page')).toHaveAttribute('data-theme', 'light');
    await audit(page);

    await page.goto('/missing-theme-page');
    await expect(page.locator('[data-theme-scope="app"] input[value="light"]')).toBeChecked();
    await expect(page.locator('html')).toHaveAttribute('data-app-theme', 'light');
    await expect(page.locator('body')).toHaveCSS('background-color', 'rgb(244, 246, 248)');
    await audit(page);
});
