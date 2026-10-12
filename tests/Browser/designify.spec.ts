import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { resolve, sep } from 'node:path';
import { expect, test } from '@playwright/test';

test('canceling the leave warning preserves an edited Designify setting', async ({ page }) => {
    const html = execFileSync('php', ['tests/Fixtures/designify-page.php'], {
        env: {
            ...process.env,
            APP_ENV: 'testing',
            APP_ENVIRONMENT_ONLY: 'false',
            APP_URL: 'http://panel.test',
            APP_KEY: 'SomeRandomString3232RandomString',
            CACHE_STORE: 'array',
            SESSION_DRIVER: 'array',
            REDIS_CLIENT: 'predis',
        },
        encoding: 'utf8',
        maxBuffer: 10 * 1024 * 1024,
    });
    const publicPath = resolve('public');
    await page.route('**/*', async (route) => {
        const url = new URL(route.request().url());
        if (url.origin !== 'http://panel.test') return route.abort();
        if (url.pathname === '/admin/designify') return route.fulfill({ contentType: 'text/html', body: html });
        if (url.pathname === '/admin') {
            return route.fulfill({ contentType: 'text/html', body: '<h1>Dashboard</h1>' });
        }
        if (/\/livewire(?:\.min)?\.js$/.test(url.pathname)) {
            return route.fulfill({
                contentType: 'application/javascript',
                body: readFileSync('vendor/livewire/livewire/dist/livewire.js'),
            });
        }
        const path = resolve(publicPath, `.${url.pathname}`);
        if (!path.startsWith(publicPath + sep)) return route.abort();
        try {
            await route.fulfill({ path });
        } catch {
            await route.abort();
        }
    });

    await page.goto('http://panel.test/admin/designify');
    const field = page.getByRole('textbox', { name: 'Copyright Text' });
    await field.fill('Unsaved copyright');
    const back = page.getByRole('link', { name: 'Return to administration' });
    const warning = page.waitForEvent('dialog').then(async (dialog) => {
        expect(dialog.type()).toBe('beforeunload');
        await dialog.dismiss();
    });
    await back.click();
    await warning;
    await expect(page).toHaveURL('http://panel.test/admin/designify');
    await expect(field).toHaveValue('Unsaved copyright');

    const leaveWarning = page.waitForEvent('dialog').then((dialog) => dialog.accept());
    await back.click();
    await leaveWarning;
    await expect(page).toHaveURL('http://panel.test/admin');
});
