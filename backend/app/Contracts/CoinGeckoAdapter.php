<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTO\CoinDetailDto;
use App\DTO\CoinMarketDto;

interface CoinGeckoAdapter
{
    /**
     * @param array<string, mixed> $row
     */
    public function mapMarket(array $row): CoinMarketDto;

    /**
     * @param array<string, mixed> $coin
     */
    public function mapDetail(array $coin): CoinDetailDto;
}
