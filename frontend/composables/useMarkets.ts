import type { ApiSuccess, CoinMarketItem } from '~/types/coin';
import { onMounted, onUnmounted, ref } from 'vue';
import { useApiClient } from '~/composables/useApiClient';

const DEBOUNCE_MS = 300;

export function useMarkets() {
  const api = useApiClient();
  const items = ref<CoinMarketItem[]>([]);
  const query = ref('');
  const loading = ref(false);
  const errorMessage = ref('');
  const pendingTimer = ref<ReturnType<typeof setTimeout> | null>(null);
  let requestSequence = 0;

  async function load(): Promise<void> {
    const currentRequest = ++requestSequence;
    loading.value = true;
    errorMessage.value = '';

    try {
      const response = await api.get<ApiSuccess<CoinMarketItem[]>>('/api/markets', query.value ? { q: query.value } : undefined);
      if (currentRequest !== requestSequence) {
        return;
      }

      items.value = response.data;
    } catch (error) {
      if (currentRequest !== requestSequence) {
        return;
      }

      errorMessage.value = error instanceof Error ? error.message : 'Failed to load markets';
    } finally {
      if (currentRequest === requestSequence) {
        loading.value = false;
      }
    }
  }

  function setQuery(value: string): void {
    query.value = value;

    if (pendingTimer.value) {
      clearTimeout(pendingTimer.value);
    }

    pendingTimer.value = setTimeout(() => {
      void load();
    }, DEBOUNCE_MS);
  }

  function retry(): void {
    void load();
  }

  onMounted(() => {
    void load();
  });

  onUnmounted(() => {
    if (pendingTimer.value) {
      clearTimeout(pendingTimer.value);
    }
  });

  return {
    items,
    query,
    loading,
    errorMessage,
    setQuery,
    retry,
    load,
  };
}
