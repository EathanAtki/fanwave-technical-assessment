<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\UpstreamServiceException;
use App\Http\Controllers\Controller;
use App\Services\CryptoMarketService;
use App\Support\ApiErrorResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CoinController extends Controller
{
    public function __construct(private readonly CryptoMarketService $service)
    {
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $coin = $this->service->coin($id);

            return response()->json([
                'data' => $coin->toArray(),
                'request_id' => (string) $request->attributes->get('request_id'),
            ]);
        } catch (UpstreamServiceException $exception) {
            if ($exception->statusCode() === 404) {
                return ApiErrorResponder::make(
                    'COIN_NOT_FOUND',
                    'Requested cryptocurrency was not found.',
                    404,
                    null,
                    (string) $request->attributes->get('request_id')
                );
            }

            return ApiErrorResponder::make(
                'UPSTREAM_UNAVAILABLE',
                'Unable to load coin details at this time.',
                502,
                $exception->details(),
                (string) $request->attributes->get('request_id')
            );
        }
    }
}
