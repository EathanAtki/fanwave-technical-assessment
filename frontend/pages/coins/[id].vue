<template>
  <section>
    <div v-if="loading" class="space-y-2" role="status" aria-label="Loading coin details">
      <div class="h-7 w-1/3 animate-pulse rounded bg-slate-200" />
      <div class="h-20 animate-pulse rounded bg-slate-200" />
    </div>

    <div v-else-if="isNotFound" class="rounded border border-amber-300 bg-amber-50 p-4">
      <p class="mb-3">Coin not found.</p>
      <NuxtLink to="/" class="text-brand-700 underline">Back to homepage</NuxtLink>
    </div>

    <div v-else-if="errorMessage" class="rounded border border-rose-300 bg-rose-50 p-4" role="alert">
      <p class="mb-3">{{ errorMessage }}</p>
      <button type="button" class="rounded bg-rose-700 px-3 py-1 text-white" @click="retry">Retry</button>
    </div>

    <article v-else-if="coin" class="rounded-md border border-slate-200 bg-white p-4">
      <h1 class="mb-2 text-2xl font-bold">{{ coin.name ?? 'Unknown Coin' }} ({{ formatSymbol(coin.symbol) }})</h1>
      <p class="mb-1">Price: {{ formatCurrency(coin.current_price) }}</p>
      <p class="mb-1">Market cap: {{ formatCurrency(coin.market_cap) }}</p>
      <p class="mb-1">Volume: {{ formatCurrency(coin.total_volume) }}</p>
      <p class="mb-3">24h change: {{ formatPercent(coin.price_change_percentage_24h) }}</p>
      <p v-if="coin.short_description" class="mb-2 text-sm text-slate-700">{{ coin.short_description }}</p>
      <ul class="list-disc pl-5">
        <li v-for="link in safeHomepageLinks" :key="link">
          <a :href="link" target="_blank" rel="noopener noreferrer" class="text-brand-700 underline">{{ link }}</a>
        </li>
      </ul>
    </article>
  </section>
</template>

<script setup lang="ts">
const route = useRoute();
const coinId = computed(() => String(route.params.id));
const { coin, loading, errorMessage, isNotFound, retry } = useCoinDetail(coinId);
const safeHomepageLinks = computed(() => (coin.value?.homepage_links ?? []).filter(isSafeExternalLink));

function formatSymbol(symbol: string | null): string {
  return symbol ? symbol.toUpperCase() : 'N/A';
}

function formatCurrency(value: number | null): string {
  return value === null ? 'N/A' : `$${value.toLocaleString()}`;
}

function formatPercent(value: number | null): string {
  return value === null ? 'N/A' : `${value.toFixed(2)}%`;
}

function isSafeExternalLink(link: string): boolean {
  try {
    const parsed = new URL(link);
    return parsed.protocol === 'http:' || parsed.protocol === 'https:';
  } catch {
    return false;
  }
}
</script>
