import { ApiClientError, useApiClient } from '~/composables/useApiClient';

function jsonResponse(payload: unknown, status = 200): Response {
  return new Response(JSON.stringify(payload), {
    status,
    headers: { 'Content-Type': 'application/json' },
  });
}

describe('useApiClient', () => {
  const fetchMock = vi.fn();

  beforeEach(() => {
    vi.restoreAllMocks();
    vi.stubGlobal('fetch', fetchMock);
    vi.stubGlobal('useRuntimeConfig', () => ({
      public: { apiBaseUrl: 'http://localhost' },
    }));
  });

  it('builds request URL with query params and returns parsed payload', async () => {
    fetchMock.mockResolvedValue(jsonResponse({ data: [{ id: 'bitcoin' }], request_id: 'req-1' }));

    const { get } = useApiClient();
    const response = await get<{ data: Array<{ id: string }>; request_id: string }>('/markets', { q: 'bit' });

    expect(fetchMock).toHaveBeenCalledTimes(1);
    expect(String(fetchMock.mock.calls[0][0])).toContain('/markets?q=bit');
    expect(response.data[0].id).toBe('bitcoin');
  });

  it('maps non-2xx responses to ApiClientError', async () => {
    fetchMock.mockResolvedValue(
      jsonResponse({ error: { code: 'RATE_LIMITED', message: 'Too many requests' }, request_id: 'req-2' }, 429)
    );

    const { get } = useApiClient();

    try {
      await get('/markets');
      throw new Error('Expected ApiClientError to be thrown');
    } catch (error) {
      expect(error).toBeInstanceOf(ApiClientError);
      expect(error).toMatchObject({
        status: 429,
        code: 'RATE_LIMITED',
        message: 'Too many requests',
      });
    }
  });
});
