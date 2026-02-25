import type { ApiSuccess, CoinDetail } from '~/types/coin';
import type { Ref } from 'vue';
import { onMounted, ref, watch } from 'vue';
import { ApiClientError } from '~/composables/useApiClient';
import { useApiClient } from '~/composables/useApiClient';

export function useCoinDetail(id: Ref<string>) {
  const api = useApiClient();
  const coin = ref<CoinDetail | null>(null);
  const loading = ref(false);
  const errorMessage = ref('');
  const isNotFound = ref(false);

  async function load(): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    isNotFound.value = false;

    try {
      const response = await api.get<ApiSuccess<CoinDetail>>(`/api/coins/${id.value}`);
      coin.value = response.data;
    } catch (error) {
      coin.value = null;
      if (error instanceof ApiClientError && error.status === 404) {
        isNotFound.value = true;
      } else {
        errorMessage.value = error instanceof Error ? error.message : 'Unable to load coin details';
      }
    } finally {
      loading.value = false;
    }
  }

  onMounted(() => {
    void load();
  });

  watch(id, () => {
    void load();
  });

  return {
    coin,
    loading,
    errorMessage,
    isNotFound,
    retry: load,
  };
}
