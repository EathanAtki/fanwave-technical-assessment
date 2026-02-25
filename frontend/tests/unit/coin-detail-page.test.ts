import { render, screen } from '@testing-library/vue';
import CoinDetailPage from '~/pages/coins/[id].vue';

vi.mock('vue-router', () => ({
  useRoute: () => ({ params: { id: 'bitcoin' } }),
}));

function jsonResponse(payload: unknown, status = 200): Response {
  return new Response(JSON.stringify(payload), {
    status,
    headers: { 'Content-Type': 'application/json' },
  });
}

describe('Coin detail page', () => {
  const fetchMock = vi.fn();

  beforeEach(() => {
    vi.restoreAllMocks();
    vi.stubGlobal('fetch', fetchMock);
    vi.stubGlobal('useRuntimeConfig', () => ({
      public: { apiBaseUrl: 'http://localhost' },
    }));

    fetchMock.mockResolvedValue(
      jsonResponse({
        data: {
          id: 'bitcoin',
          symbol: 'btc',
          name: 'Bitcoin',
          image: '',
          current_price: 100,
          market_cap: 1000,
          total_volume: 200,
          price_change_percentage_24h: 2,
          short_description: 'Bitcoin detail',
          homepage_links: ['https://bitcoin.org', 'javascript:alert(1)'],
        },
        request_id: 'req-1',
      })
    );
  });

  it('renders expected coin data and only safe links', async () => {
    render(CoinDetailPage, {
      global: {
        stubs: {
          NuxtLink: { template: '<a><slot /></a>' },
        },
      },
    });

    expect(await screen.findByRole('heading', { name: /Bitcoin \(BTC\)/ })).toBeTruthy();
    expect(screen.getByText('Price: $100')).toBeTruthy();
    expect(screen.getByText('Market cap: $1,000')).toBeTruthy();
    expect(screen.getByText('Volume: $200')).toBeTruthy();
    expect(screen.getByRole('link', { name: 'https://bitcoin.org' })).toBeTruthy();
    expect(screen.queryByText('javascript:alert(1)')).toBeNull();
  });
});
