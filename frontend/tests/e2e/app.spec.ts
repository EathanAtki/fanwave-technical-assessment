import { test, expect } from '@playwright/test';

const marketsPayload = {
  data: Array.from({ length: 10 }).map((_, i) => ({
    id: `coin-${i}`,
    symbol: `c${i}`,
    name: `Coin ${i}`,
    image: '',
    current_price: 100 - i,
    market_cap: 1000 - i,
    total_volume: 500 - i,
    price_change_percentage_24h: 1.2,
  })),
  request_id: 'req-1',
};

test.beforeEach(async ({ page }) => {
  await page.route('**/api/markets**', async (route) => {
    const url = new URL(route.request().url());
    const query = (url.searchParams.get('q') || '').toLowerCase();

    const filtered = query
      ? marketsPayload.data.filter((coin) => coin.name.toLowerCase().includes(query) || coin.symbol.toLowerCase().includes(query))
      : marketsPayload.data;

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ data: filtered, request_id: 'req-1' }),
    });
  });

  await page.route('**/api/coins/**', async (route) => {
    const id = route.request().url().split('/').pop();

    if (id === 'unknown') {
      await route.fulfill({
        status: 404,
        contentType: 'application/json',
        body: JSON.stringify({ error: { code: 'COIN_NOT_FOUND', message: 'not found' }, request_id: 'req-2' }),
      });

      return;
    }

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        data: {
          id,
          symbol: 'btc',
          name: 'Bitcoin',
          image: '',
          current_price: 100,
          market_cap: 1000,
          total_volume: 200,
          price_change_percentage_24h: 2,
          short_description: 'detail',
          homepage_links: ['https://example.com'],
        },
        request_id: 'req-3',
      }),
    });
  });
});

test('homepage loads and shows market list', async ({ page }) => {
  await page.goto('/');
  await expect(page.getByRole('heading', { name: 'Top Cryptocurrencies' })).toBeVisible();
  await expect(page.getByLabel('Cryptocurrency list').locator('li')).toHaveCount(10);
});

test('search filters results', async ({ page }) => {
  await page.goto('/');
  await page.getByLabel('Search by name or symbol').fill('coin 1');
  await expect(page.getByLabel('Cryptocurrency list').locator('li')).toHaveCount(1);
});

test('navigation to detail page works', async ({ page }) => {
  await page.goto('/');
  await page.getByLabel('View Coin 0').click();
  await expect(page).toHaveURL(/\/coins\/coin-0/);
  await expect(page.getByText('Price: $100')).toBeVisible();
});

test('unknown coin shows not found state', async ({ page }) => {
  await page.goto('/coins/unknown');
  await expect(page.getByText('Coin not found.')).toBeVisible();
  await expect(page.getByRole('link', { name: 'Back to homepage' })).toBeVisible();
});

test('detail failure shows retry action', async ({ page }) => {
  await page.route('**/api/coins/coin-0', async (route) => {
    await route.fulfill({
      status: 502,
      contentType: 'application/json',
      body: JSON.stringify({ error: { code: 'UPSTREAM_UNAVAILABLE', message: 'Detail failed' }, request_id: 'req-5' }),
    });
  });

  await page.goto('/coins/coin-0');
  await expect(page.getByRole('alert')).toContainText('Detail failed');
  await expect(page.getByRole('button', { name: 'Retry' })).toBeVisible();
});

test('error states render correctly', async ({ page }) => {
  await page.route('**/api/markets**', async (route) => {
    await route.fulfill({
      status: 429,
      contentType: 'application/json',
      body: JSON.stringify({ error: { code: 'RATE_LIMITED', message: 'Too many requests' }, request_id: 'req-4' }),
    });
  });

  await page.goto('/');
  await expect(page.getByRole('alert')).toContainText('Too many requests');
  await expect(page.getByRole('button', { name: 'Retry' })).toBeVisible();
});

test('mobile viewport smoke test', async ({ browser }) => {
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();

  await page.route('**/api/markets**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(marketsPayload),
    });
  });

  await page.goto('/');
  await expect(page.getByRole('heading', { name: 'Top Cryptocurrencies' })).toBeVisible();
  await context.close();
});
