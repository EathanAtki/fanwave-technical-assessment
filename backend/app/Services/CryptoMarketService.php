<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CoinGeckoAdapter as CoinGeckoAdapterContract;
use App\Contracts\CoinGeckoClient as CoinGeckoClientContract;
use App\DTO\CoinDetailDto;
use App\DTO\CoinMarketDto;
use App\Services\CoinGecko\CoinMarketFilter;
use Illuminate\Support\Facades\Cache;

final class CryptoMarketService
{
    public function __construct(
        private readonly CoinGeckoClientContract $client,
        private readonly CoinGeckoAdapterContract $adapter,
        private readonly CoinMarketFilter $filter,
    ) {
    }

    /**
     * @return list<CoinMarketDto>
     */
    public function markets(?string $query): array
    {
        $ttl = now()->addSeconds((int) config('services.coingecko.cache_ttl_markets'));

        /** @var list<array<string, mixed>> $mapped */
        $mapped = Cache::remember('coingecko:markets:top10', $ttl, function (): array {
            $rows = $this->client->markets(10);

            return array_map(
                fn (array $row): array => $this->adapter->mapMarket($row)->toArray(),
                $rows
            );
        });

        /** @var list<CoinMarketDto> $markets */
        $markets = array_map(
            static fn (array $item): CoinMarketDto => CoinMarketDto::fromArray($item),
            $mapped
        );

        return $this->filter->apply($markets, $query);
    }

    /**
     * @return CoinDetailDto
     */
    public function coin(string $id): CoinDetailDto
    {
        $ttl = now()->addSeconds((int) config('services.coingecko.cache_ttl_coin'));

        /** @var array<string, mixed> $mapped */
        $mapped = Cache::remember('coingecko:coin:'.$id, $ttl, function () use ($id): array {
            $coin = $this->client->coin($id);

            return $this->adapter->mapDetail($coin)->toArray();
        });

        return CoinDetailDto::fromArray($mapped);
    }
}
