<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CoinMarketDto;
use App\Services\CoinGecko\CoinMarketFilter;
use PHPUnit\Framework\TestCase;

final class CoinMarketFilterTest extends TestCase
{
    public function test_it_filters_by_name_or_symbol(): void
    {
        $filter = new CoinMarketFilter();
        $items = [
            CoinMarketDto::fromArray(['name' => 'Bitcoin', 'symbol' => 'btc']),
            CoinMarketDto::fromArray(['name' => 'Ripple', 'symbol' => 'xrp']),
        ];

        $byName = $filter->apply($items, 'Bitcoin');
        $bySymbol = $filter->apply($items, 'xrp');

        $this->assertCount(1, $byName);
        $this->assertSame('Bitcoin', $byName[0]->name);
        $this->assertCount(1, $bySymbol);
        $this->assertSame('Ripple', $bySymbol[0]->name);
    }

    public function test_it_returns_all_items_when_query_is_null(): void
    {
        $filter = new CoinMarketFilter();
        $items = [
            CoinMarketDto::fromArray(['name' => 'Bitcoin', 'symbol' => 'btc']),
            CoinMarketDto::fromArray(['name' => 'Ethereum', 'symbol' => 'eth']),
        ];

        $result = $filter->apply($items, null);

        $this->assertCount(2, $result);
    }
}
