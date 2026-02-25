<?php

declare(strict_types=1);

namespace App\Services\CoinGecko;

use App\DTO\CoinMarketDto;
use Illuminate\Support\Str;

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

        $needle = Str::lower($query);

        return array_values(array_filter($items, static function (CoinMarketDto $item) use ($needle): bool {
            $name = Str::lower($item->name ?? '');
            $symbol = Str::lower($item->symbol ?? '');

            return str_contains($name, $needle) || str_contains($symbol, $needle);
        }));
    }
}
