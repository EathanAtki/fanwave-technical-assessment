import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { vi } from 'vitest';
import { useMarkets } from '~/composables/useMarkets';

interface Deferred<T> {
  promise: Promise<T>;
  resolve: (value: T) => void;
}

function deferred<T>(): Deferred<T> {
  let resolve!: (value: T) => void;
  const promise = new Promise<T>((res) => {
    resolve = res;
  });

  return { promise, resolve };
}

function market(name: string) {
  return {
    id: name.toLowerCase(),
    symbol: name.slice(0, 3).toLowerCase(),
    name,
    image: '',
    current_price: 1,
    market_cap: 1,
    total_volume: 1,
    price_change_percentage_24h: 1,
  };
}

function jsonResponse(payload: unknown, status = 200): Response {
  return new Response(JSON.stringify(payload), {
    status,
    headers: { 'Content-Type': 'application/json' },
  });
}

describe('useMarkets', () => {
  const fetchMock = vi.fn();

  beforeEach(() => {
    vi.restoreAllMocks();
    vi.stubGlobal('fetch', fetchMock);
    vi.stubGlobal('useRuntimeConfig', () => ({
      public: { apiBaseUrl: 'http://localhost' },
    }));

    fetchMock.mockReset();
    fetchMock.mockResolvedValue(jsonResponse({ data: [] }));
  });

  it('debounces search requests', async () => {
    vi.useFakeTimers();

    let marketsRef: ReturnType<typeof useMarkets> | undefined;

    const wrapper = mount({
      template: '<div />',
      setup() {
        marketsRef = useMarkets();
        return {};
      },
    });

    await nextTick();
    await Promise.resolve();
    fetchMock.mockClear();

    marketsRef!.setQuery('b');
    marketsRef!.setQuery('bi');
    marketsRef!.setQuery('bit');

    vi.advanceTimersByTime(299);
    expect(fetchMock).toHaveBeenCalledTimes(0);

    vi.advanceTimersByTime(1);
    await Promise.resolve();

    expect(fetchMock).toHaveBeenCalledTimes(1);

    wrapper.unmount();
    vi.useRealTimers();
  });

  it('clears debounce timer on unmount to prevent post-unmount requests', async () => {
    vi.useFakeTimers();

    let marketsRef: ReturnType<typeof useMarkets> | undefined;

    const wrapper = mount({
      template: '<div />',
      setup() {
        marketsRef = useMarkets();
        return {};
      },
    });

    await nextTick();
    await Promise.resolve();
    fetchMock.mockClear();

    marketsRef!.setQuery('bitcoin');
    wrapper.unmount();

    vi.advanceTimersByTime(300);
    await Promise.resolve();

    expect(fetchMock).toHaveBeenCalledTimes(0);
    vi.useRealTimers();
  });

  it('ignores stale responses and keeps latest query results', async () => {
    const oldRequest = deferred<Response>();
    const newRequest = deferred<Response>();

    fetchMock.mockImplementation((input: RequestInfo | URL) => {
      const url = String(input);
      if (url.includes('q=old')) {
        return oldRequest.promise;
      }

      if (url.includes('q=new')) {
        return newRequest.promise;
      }

      return Promise.resolve(jsonResponse({ data: [] }));
    });

    let marketsRef: ReturnType<typeof useMarkets> | undefined;

    mount({
      template: '<div />',
      setup() {
        marketsRef = useMarkets();
        return {};
      },
    });

    await nextTick();
    await Promise.resolve();

    marketsRef!.query.value = 'old';
    const staleLoad = marketsRef!.load();

    marketsRef!.query.value = 'new';
    const latestLoad = marketsRef!.load();

    newRequest.resolve(jsonResponse({ data: [market('Latest')] }));
    await latestLoad;

    oldRequest.resolve(jsonResponse({ data: [market('Stale')] }));
    await staleLoad;

    expect(marketsRef!.items.value).toHaveLength(1);
    expect(marketsRef!.items.value[0].name).toBe('Latest');
  });
});
