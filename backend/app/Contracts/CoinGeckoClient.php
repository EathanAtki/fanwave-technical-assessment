<?php

declare(strict_types=1);

namespace App\Contracts;

interface CoinGeckoClient
{
    /**
     * @return list<array<string, mixed>>
     */
    public function markets(int $perPage): array;

    /**
     * @return array<string, mixed>
     */
    public function coin(string $id): array;
}
