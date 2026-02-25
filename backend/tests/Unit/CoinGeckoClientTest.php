<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\UpstreamServiceException;
use App\Services\CoinGecko\CoinGeckoClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class CoinGeckoClientTest extends TestCase
{
    public function test_markets_throws_upstream_exception_for_http_failure(): void
    {
        Http::fake([
            '*/coins/markets*' => Http::response([], 500),
        ]);

        $client = $this->app->make(CoinGeckoClient::class);

        $this->expectException(UpstreamServiceException::class);
        $this->expectExceptionMessage('CoinGecko markets request failed');

        $client->markets(10);
    }

    public function test_markets_throws_upstream_exception_for_connection_failure(): void
    {
        Http::fake([
            '*/coins/markets*' => Http::failedConnection('Connection refused'),
        ]);

        $client = $this->app->make(CoinGeckoClient::class);

        $this->expectException(UpstreamServiceException::class);
        $this->expectExceptionMessage('CoinGecko markets request failed');

        $client->markets(10);
    }

    public function test_coin_maps_404_to_not_found_upstream_exception(): void
    {
        Http::fake([
            '*/coins/missing*' => Http::response([], 404),
        ]);

        $client = $this->app->make(CoinGeckoClient::class);

        try {
            $client->coin('missing');
            $this->fail('Expected UpstreamServiceException was not thrown.');
        } catch (UpstreamServiceException $exception) {
            $this->assertSame(404, $exception->statusCode());
            $this->assertSame('Coin not found', $exception->getMessage());
        }
    }
}
