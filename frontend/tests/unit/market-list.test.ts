import { render, screen, fireEvent, waitFor } from '@testing-library/vue';
import MarketList from '~/components/MarketList.vue';

describe('MarketList', () => {
  const items = [
    {
      id: 'bitcoin',
      symbol: 'btc',
      name: 'Bitcoin',
      image: '',
      current_price: 1,
      market_cap: 2,
      total_volume: 3,
      price_change_percentage_24h: 4,
    },
  ];

  it('renders market row data', () => {
    render(MarketList, {
      props: { items, loading: false, error: '', query: '' },
      global: { stubs: { NuxtLink: { template: '<a><slot /></a>' } } },
    });

    expect(screen.getByText(/Bitcoin/)).toBeInTheDocument();
    expect(screen.getByText('$1')).toBeInTheDocument();
  });

  it('renders loading skeleton', () => {
    render(MarketList, {
      props: { items: [], loading: true, error: '', query: '' },
      global: { stubs: { NuxtLink: { template: '<a><slot /></a>' } } },
    });

    expect(screen.getByRole('status', { name: 'Loading markets' })).toBeInTheDocument();
  });

  it('shows error banner and emits retry', async () => {
    const { emitted } = render(MarketList, {
      props: { items: [], loading: false, error: 'Network error', query: '' },
      global: { stubs: { NuxtLink: { template: '<a><slot /></a>' } } },
    });

    await fireEvent.click(screen.getByRole('button', { name: 'Retry' }));
    await waitFor(() => expect(emitted().retry).toBeTruthy());
  });
});
