<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class MarketsApiTest extends TestCase
{
    public function test_markets_endpoint_returns_10_items_sorted_by_market_cap_descending(): void
    {
        Http::fake([
            '*/coins/markets*' => Http::response($this->fakeMarkets(), 200),
        ]);

        $response = $this->getJson('/api/markets');

        $response->assertOk()->assertJsonCount(10, 'data');
        $response->assertJsonPath('data.0.market_cap', 1000)
            ->assertJsonPath('data.1.market_cap', 990);
    }

    public function test_search_filters_by_name_and_symbol(): void
    {
        Http::fake([
            '*/coins/markets*' => Http::response($this->fakeMarkets(), 200),
        ]);

        $this->getJson('/api/markets?q=coin 1')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/markets?q=s3')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_empty_search_query_returns_default_top_10_list(): void
    {
        Http::fake([
            '*/coins/markets*' => Http::response($this->fakeMarkets(), 200),
        ]);

        $this->getJson('/api/markets?q=')
            ->assertOk()
            ->assertJsonCount(10, 'data');
    }

    public function test_validation_errors_return_422_in_consistent_shape(): void
    {
        $response = $this->getJson('/api/markets?q='.(str_repeat('x', 200)));

        $response->assertStatus(422)->assertJsonStructure([
            'error' => ['code', 'message', 'details'],
            'request_id',
        ]);
    }

    public function test_caching_prevents_duplicate_upstream_calls_within_ttl(): void
    {
        Cache::flush();

        Http::fake([
            '*/coins/markets*' => Http::response($this->fakeMarkets(), 200),
        ]);

        $this->getJson('/api/markets')->assertOk();
        $this->getJson('/api/markets')->assertOk();

        Http::assertSentCount(1);
    }

    public function test_rate_limiting_returns_429(): void
    {
        Http::fake([
            '*/coins/markets*' => Http::response($this->fakeMarkets(), 200),
        ]);

        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/markets');
        }

        $this->getJson('/api/markets')
            ->assertStatus(429);
    }

    public function test_markets_upstream_http_failure_returns_502_with_consistent_error_shape(): void
    {
        Http::fake([
            '*/coins/markets*' => Http::response([], 500),
        ]);

        $this->getJson('/api/markets')
            ->assertStatus(502)
            ->assertJsonPath('error.code', 'UPSTREAM_UNAVAILABLE')
            ->assertJsonStructure([
                'error' => ['code', 'message'],
                'request_id',
            ]);
    }

    public function test_markets_upstream_connection_failure_returns_502_with_consistent_error_shape(): void
    {
        Http::fake([
            '*/coins/markets*' => Http::failedConnection('Connection refused'),
        ]);

        $this->getJson('/api/markets')
            ->assertStatus(502)
            ->assertJsonPath('error.code', 'UPSTREAM_UNAVAILABLE')
            ->assertJsonStructure([
                'error' => ['code', 'message'],
                'request_id',
            ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fakeMarkets(): array
    {
        $rows = [];
        for ($i = 0; $i < 10; $i++) {
            $rows[] = [
                'id' => 'coin-'.$i,
                'symbol' => 's'.$i,
                'name' => 'Coin '.$i,
                'image' => 'https://cdn.example.com/'.$i.'.png',
                'current_price' => 100 - $i,
                'market_cap' => 1000 - ($i * 10),
                'total_volume' => 500 - ($i * 5),
                'price_change_percentage_24h' => 1.2,
            ];
        }

        return $rows;
    }
}
