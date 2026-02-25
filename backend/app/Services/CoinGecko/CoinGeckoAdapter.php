<?php

declare(strict_types=1);

namespace App\Services\CoinGecko;

use App\Contracts\CoinGeckoAdapter as CoinGeckoAdapterContract;
use App\DTO\CoinDetailDto;
use App\DTO\CoinMarketDto;

final class CoinGeckoAdapter implements CoinGeckoAdapterContract
{
    /**
     * @param array<string, mixed> $row
     */
    public function mapMarket(array $row): CoinMarketDto
    {
        return CoinMarketDto::fromArray([
            'id' => $row['id'] ?? null,
            'symbol' => $row['symbol'] ?? null,
            'name' => $row['name'] ?? null,
            'image' => $row['image'] ?? null,
            'current_price' => $row['current_price'] ?? null,
            'market_cap' => $row['market_cap'] ?? null,
            'total_volume' => $row['total_volume'] ?? null,
            'price_change_percentage_24h' => $row['price_change_percentage_24h'] ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $coin
     */
    public function mapDetail(array $coin): CoinDetailDto
    {
        $marketData = $coin['market_data'] ?? [];
        $description = is_array($coin['description'] ?? null) ? $coin['description'] : [];
        $links = is_array($coin['links'] ?? null) ? $coin['links'] : [];

        return CoinDetailDto::fromArray([
            'id' => $coin['id'] ?? null,
            'symbol' => $coin['symbol'] ?? null,
            'name' => $coin['name'] ?? null,
            'image' => $coin['image']['large'] ?? $coin['image']['small'] ?? null,
            'current_price' => $marketData['current_price']['usd'] ?? null,
            'market_cap' => $marketData['market_cap']['usd'] ?? null,
            'total_volume' => $marketData['total_volume']['usd'] ?? null,
            'price_change_percentage_24h' => $marketData['price_change_percentage_24h'] ?? null,
            'short_description' => $description['en'] ?? null,
            'homepage_links' => array_values(array_filter($links['homepage'] ?? [], static fn (mixed $url): bool => is_string($url) && $url !== '')),
        ]);
    }
}
