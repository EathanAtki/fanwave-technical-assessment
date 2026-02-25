<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class CoinDetailsApiTest extends TestCase
{
    public function test_coin_detail_returns_expected_shape(): void
    {
        Http::fake([
            '*/coins/bitcoin*' => Http::response([
                'id' => 'bitcoin',
                'symbol' => 'btc',
                'name' => 'Bitcoin',
                'image' => ['large' => 'https://cdn.example.com/bitcoin.png'],
                'description' => ['en' => 'Bitcoin detail'],
                'links' => ['homepage' => ['https://bitcoin.org']],
                'market_data' => [
                    'current_price' => ['usd' => 100],
                    'market_cap' => ['usd' => 1000],
                    'total_volume' => ['usd' => 300],
                    'price_change_percentage_24h' => 1.5,
                ],
            ], 200),
        ]);

        $this->getJson('/api/coins/bitcoin')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'symbol',
                    'name',
                    'image',
                    'current_price',
                    'market_cap',
                    'total_volume',
                    'price_change_percentage_24h',
                    'short_description',
                    'homepage_links',
                ],
                'request_id',
            ]);
    }

    public function test_unknown_coin_returns_404_with_error_shape(): void
    {
        Http::fake([
            '*/coins/missing*' => Http::response([], 404),
        ]);

        $this->getJson('/api/coins/missing')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'COIN_NOT_FOUND')
            ->assertJsonStructure(['error' => ['code', 'message'], 'request_id']);
    }

    public function test_upstream_failure_returns_502(): void
    {
        Http::fake([
            '*/coins/bitcoin*' => Http::response([], 500),
        ]);

        $this->getJson('/api/coins/bitcoin')
            ->assertStatus(502)
            ->assertJsonPath('error.code', 'UPSTREAM_UNAVAILABLE');
    }

    public function test_detail_connection_failure_returns_502_with_consistent_error_shape(): void
    {
        Http::fake([
            '*/coins/bitcoin*' => Http::failedConnection('Connection refused'),
        ]);

        $this->getJson('/api/coins/bitcoin')
            ->assertStatus(502)
            ->assertJsonPath('error.code', 'UPSTREAM_UNAVAILABLE')
            ->assertJsonStructure([
                'error' => ['code', 'message'],
                'request_id',
            ]);
    }
}
