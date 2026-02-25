import type { ApiError } from '~/types/coin';

export class ApiClientError extends Error {
  public readonly status: number;
  public readonly code: string;

  constructor(message: string, status: number, code: string) {
    super(message);
    this.status = status;
    this.code = code;
  }
}

export function useApiClient() {
  const config = useRuntimeConfig();

  async function get<T>(path: string, query?: Record<string, string>): Promise<T> {
    const url = new URL(path, config.public.apiBaseUrl);
    if (query) {
      Object.entries(query).forEach(([key, value]) => {
        if (value !== '') {
          url.searchParams.set(key, value);
        }
      });
    }

    const response = await fetch(url.toString());
    const json = (await response.json()) as T | ApiError;

    if (!response.ok) {
      const err = json as ApiError;
      throw new ApiClientError(err.error?.message ?? 'Request failed', response.status, err.error?.code ?? 'UNKNOWN');
    }

    return json as T;
  }

  return { get };
}
