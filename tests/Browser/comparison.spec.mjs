import { test, expect } from '@playwright/test';
import { readFileSync, existsSync } from 'node:fs';
import { resolve, extname } from 'node:path';

const types = { '.css': 'text/css', '.js': 'text/javascript', '.woff2': 'font/woff2', '.woff': 'font/woff', '.ttf': 'font/ttf' };

test.beforeAll(() => {
    expect(process.env.DBCMS_COMPARISON_FIXTURE_DIR, 'Export the real public comparison response first').toBeTruthy();
    expect(process.env.DBCMS_COMPARISON_ASSET_ROOT, 'Supply the real candidate Vite build').toBeTruthy();
});

test.beforeEach(async ({ page }) => {
    await page.route('**/*', async route => {
        const pathname = new URL(route.request().url()).pathname;
        if (pathname === '/__comparison') {
            const path = resolve(process.env.DBCMS_COMPARISON_FIXTURE_DIR, 'comparison.html');
            expect(existsSync(path)).toBeTruthy();
            return route.fulfill({ contentType: 'text/html', body: readFileSync(path) });
        }
        if (pathname.startsWith('/build/')) {
            const path = resolve(process.env.DBCMS_COMPARISON_ASSET_ROOT, `.${pathname}`);
            expect(existsSync(path), `Missing built asset ${path}`).toBeTruthy();
            return route.fulfill({ contentType: types[extname(path)] || 'application/octet-stream', body: readFileSync(path) });
        }
        if (pathname === '/storage/before.webp' || pathname === '/storage/after.webp') {
            const color = pathname.includes('before') ? '#2563eb' : '#16a34a';
            return route.fulfill({ contentType: 'image/svg+xml', body: `<svg xmlns="http://www.w3.org/2000/svg" width="900" height="500"><rect width="900" height="500" fill="${color}"/></svg>` });
        }
        return route.fulfill({ status: 204, body: '' });
    });
});

for (const width of [390, 800, 1480]) {
    test(`the comparison responds to keyboard input at ${width}px`, async ({ page }) => {
        await page.setViewportSize({ width, height: 900 });
        await page.goto('http://dbcms-comparison.test/__comparison');
        const slider = page.getByRole('slider', { name: 'Antes / Después' });
        const comparison = slider.locator('..');
        const beforeLayer = comparison.locator('div[\\:style]').first();
        await expect(slider).toBeVisible();
        await expect.poll(() => beforeLayer.evaluate(node => getComputedStyle(node).clipPath)).toContain('50%');
        await slider.focus();
        await slider.press('Home');
        await expect(slider).toHaveValue('0');
        await expect.poll(() => beforeLayer.evaluate(node => getComputedStyle(node).clipPath)).toContain('100%');
        await slider.press('End');
        await expect(slider).toHaveValue('100');
        await expect.poll(() => beforeLayer.evaluate(node => getComputedStyle(node).clipPath)).not.toContain('100%');
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth)).toBe(true);
    });
}
