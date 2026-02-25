<template>
  <MarketList
    :items="items"
    :loading="loading"
    :error="errorMessage"
    :query="query"
    @search="setQuery"
    @retry="retry"
  />
</template>

<script setup lang="ts">
const { items, loading, errorMessage, query, setQuery, retry } = useMarkets();
</script>
