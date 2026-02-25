<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class UpstreamServiceException extends RuntimeException
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(string $message, private readonly int $statusCode = 502, private readonly array $details = [])
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->details;
    }
}
