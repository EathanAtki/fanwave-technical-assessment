<?php

declare(strict_types=1);

namespace App\Services\CoinGecko;

use App\Contracts\CoinGeckoClient as CoinGeckoClientContract;
use App\Exceptions\UpstreamServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;

final class CoinGeckoClient implements CoinGeckoClientContract
{
    public function __construct(private readonly Factory $http)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function markets(int $perPage): array
    {
        try {
            /** @var list<array<string, mixed>> $payload */
            $payload = $this->request()->get('/coins/markets', [
                'vs_currency' => 'usd',
                'order' => 'market_cap_desc',
                'per_page' => $perPage,
                'page' => 1,
                'sparkline' => 'false',
                'price_change_percentage' => '24h',
            ])->throw()->json();

            return $payload;
        } catch (RequestException|ConnectionException $exception) {
            $status = $exception instanceof RequestException
                ? ($exception->response?->status() ?? 502)
                : 502;

            throw new UpstreamServiceException('CoinGecko markets request failed', 502, [
                'status' => $status,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function coin(string $id): array
    {
        try {
            /** @var array<string, mixed> $payload */
            $payload = $this->request()->get('/coins/'.$id, [
                'localization' => 'false',
                'tickers' => 'false',
                'market_data' => 'true',
                'community_data' => 'false',
                'developer_data' => 'false',
                'sparkline' => 'false',
            ])->throw()->json();

            return $payload;
        } catch (RequestException|ConnectionException $exception) {
            $status = $exception instanceof RequestException
                ? ($exception->response?->status() ?? 502)
                : 502;
            if ($status === 404) {
                throw new UpstreamServiceException('Coin not found', 404);
            }

            throw new UpstreamServiceException('CoinGecko request failed', 502, [
                'status' => $status,
            ]);
        }
    }

    private function request(): PendingRequest
    {
        return $this->http
            ->baseUrl((string) config('services.coingecko.base_url'))
            ->timeout((float) config('services.coingecko.timeout_seconds'))
            ->retry(
                (int) config('services.coingecko.retry_times'),
                (int) config('services.coingecko.retry_sleep_ms')
            )
            ->acceptJson();
    }
}
