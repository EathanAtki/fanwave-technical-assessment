<template>
  <section>
    <h1 class="mb-4 text-2xl font-bold">Top Cryptocurrencies</h1>

    <label for="search" class="mb-2 block text-sm font-medium text-slate-700">Search by name or symbol</label>
    <input
      id="search"
      :value="query"
      type="text"
      class="mb-4 w-full rounded-md border border-slate-300 px-3 py-2"
      placeholder="Try btc or bitcoin"
      @input="$emit('search', ($event.target as HTMLInputElement).value)"
    />

    <div v-if="loading" role="status" aria-label="Loading markets" class="space-y-2">
      <div v-for="index in 10" :key="index" class="h-12 animate-pulse rounded bg-slate-200" />
    </div>

    <div v-else-if="error" class="mb-4 rounded border border-rose-300 bg-rose-50 p-3 text-rose-800" role="alert">
      <p class="mb-2">{{ error }}</p>
      <button type="button" class="rounded bg-rose-700 px-3 py-1 text-white" @click="$emit('retry')">Retry</button>
    </div>

    <ul v-else class="space-y-2" aria-label="Cryptocurrency list">
      <li v-for="(coin, index) in items" :key="coin.id ?? coin.symbol ?? `coin-${index}`" class="rounded-md border border-slate-200 bg-white p-3">
        <NuxtLink
          v-if="coin.id"
          :to="`/coins/${coin.id}`"
          class="grid grid-cols-2 gap-2 md:grid-cols-6"
          :aria-label="`View ${coin.name ?? 'coin'}`"
        >
          <span class="font-semibold">{{ coin.name ?? 'Unknown' }} ({{ formatSymbol(coin.symbol) }})</span>
          <span>{{ formatCurrency(coin.current_price) }}</span>
          <span>{{ formatCurrency(coin.market_cap) }}</span>
          <span>{{ formatCurrency(coin.total_volume) }}</span>
          <span :class="(coin.price_change_percentage_24h ?? 0) >= 0 ? 'text-emerald-700' : 'text-rose-700'">
            {{ formatPercent(coin.price_change_percentage_24h) }}
          </span>
        </NuxtLink>
        <div v-else class="grid grid-cols-2 gap-2 md:grid-cols-6">
          <span class="font-semibold">{{ coin.name ?? 'Unknown' }} ({{ formatSymbol(coin.symbol) }})</span>
          <span>{{ formatCurrency(coin.current_price) }}</span>
          <span>{{ formatCurrency(coin.market_cap) }}</span>
          <span>{{ formatCurrency(coin.total_volume) }}</span>
          <span :class="(coin.price_change_percentage_24h ?? 0) >= 0 ? 'text-emerald-700' : 'text-rose-700'">
            {{ formatPercent(coin.price_change_percentage_24h) }}
          </span>
        </div>
      </li>
    </ul>
  </section>
</template>

<script setup lang="ts">
import type { CoinMarketItem } from '~/types/coin';

defineProps<{
  items: CoinMarketItem[];
  loading: boolean;
  error: string;
  query: string;
}>();

defineEmits<{
  (event: 'search', value: string): void;
  (event: 'retry'): void;
}>();

function formatSymbol(symbol: string | null): string {
  return symbol ? symbol.toUpperCase() : 'N/A';
}

function formatCurrency(value: number | null): string {
  return value === null ? 'N/A' : `$${value.toLocaleString()}`;
}

function formatPercent(value: number | null): string {
  return value === null ? 'N/A' : `${value.toFixed(2)}%`;
}
</script>
