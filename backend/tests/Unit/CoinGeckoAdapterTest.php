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
}
