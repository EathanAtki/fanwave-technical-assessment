<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTO\CoinMarketDto;
use App\Exceptions\UpstreamServiceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\MarketIndexRequest;
use App\Services\CryptoMarketService;
use App\Support\ApiErrorResponder;
use Illuminate\Http\JsonResponse;

final class MarketController extends Controller
{
    public function __construct(private readonly CryptoMarketService $service)
    {
    }

    public function index(MarketIndexRequest $request): JsonResponse
    {
        try {
            $items = $this->service->markets($request->queryValue());

            return response()->json([
                'data' => array_map(
                    static fn (CoinMarketDto $item): array => $item->toArray(),
                    $items
                ),
                'request_id' => (string) $request->attributes->get('request_id'),
            ]);
        } catch (UpstreamServiceException $exception) {
            return ApiErrorResponder::make(
                'UPSTREAM_UNAVAILABLE',
                'Unable to load markets at this time.',
                $exception->statusCode(),
                $exception->details(),
                (string) $request->attributes->get('request_id')
            );
        }
    }
}
