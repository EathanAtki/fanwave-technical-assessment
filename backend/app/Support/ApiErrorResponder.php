<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;

final class ApiErrorResponder
{
    /**
     * @param array<string, mixed>|null $details
     */
    public static function make(
        string $code,
        string $message,
        int $status,
        ?array $details,
        string $requestId,
    ): JsonResponse {
        return response()->json([
            'error' => array_filter([
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ], static fn (mixed $value): bool => $value !== null),
            'request_id' => $requestId,
        ], $status);
    }
}
