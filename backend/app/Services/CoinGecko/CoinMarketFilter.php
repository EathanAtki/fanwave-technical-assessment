<?php

declare(strict_types=1);

namespace App\Services\CoinGecko;

use App\DTO\CoinMarketDto;

final class CoinMarketFilter
{
    /**
     * @param list<CoinMarketDto> $items
     * @return list<CoinMarketDto>
     */
    public function apply(array $items, ?string $query): array
    {
        if ($query === null) {
            return $items;
        }

        return array_values(array_filter($items, static function (CoinMarketDto $item) use ($query): bool {
            $name = mb_strtolower($item->name ?? '');
            $symbol = mb_strtolower($item->symbol ?? '');

            return str_contains($name, $query) || str_contains($symbol, $query);
        }));
    }
}
