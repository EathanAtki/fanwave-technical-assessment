<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CoinGecko\CoinGeckoAdapter;
use PHPUnit\Framework\TestCase;

final class CoinGeckoAdapterTest extends TestCase
{
    public function test_it_maps_market_rows_to_stable_shape(): void
    {
        $adapter = new CoinGeckoAdapter();
        $mapped = $adapter->mapMarket([
            'id' => 'bitcoin',
            'symbol' => 'btc',
            'name' => 'Bitcoin',
            'image' => 'img',
            'current_price' => 1,
            'market_cap' => 2,
            'total_volume' => 3,
            'price_change_percentage_24h' => 4,
            'extra' => 'ignored',
        ]);

        $this->assertSame([
            'id' => 'bitcoin',
            'symbol' => 'btc',
            'name' => 'Bitcoin',
            'image' => 'img',
            'current_price' => 1,
            'market_cap' => 2,
            'total_volume' => 3,
            'price_change_percentage_24h' => 4,
        ], $mapped->toArray());
    }

    public function test_it_maps_detail_rows_with_missing_nested_keys_and_filters_homepages(): void
    {
        $adapter = new CoinGeckoAdapter();

        $mapped = $adapter->mapDetail([
            'id' => 'bitcoin',
            'symbol' => 'btc',
            'name' => 'Bitcoin',
            'image' => [],
            'description' => ['en' => 'Bitcoin description'],
            'links' => [
                'homepage' => [
                    'https://bitcoin.org',
                    '',
                    123,
                    'https://example.com',
                ],
            ],
            'market_data' => [
                'current_price' => [],
                // missing market_cap and total_volume on purpose
                'price_change_percentage_24h' => null,
            ],
        ]);

        $this->assertSame([
            'id' => 'bitcoin',
            'symbol' => 'btc',
            'name' => 'Bitcoin',
            'image' => null,
            'current_price' => null,
            'market_cap' => null,
            'total_volume' => null,
            'price_change_percentage_24h' => null,
            'short_description' => 'Bitcoin description',
            'homepage_links' => [
                'https://bitcoin.org',
                'https://example.com',
            ],
        ], $mapped->toArray());
    }
}
