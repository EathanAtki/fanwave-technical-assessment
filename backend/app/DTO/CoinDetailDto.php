<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\Concerns\NormalizesDtoValues;

final class CoinDetailDto
{
    use NormalizesDtoValues;

    /**
     * @param list<string> $homepageLinks
     */
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $symbol,
        public readonly ?string $name,
        public readonly ?string $image,
        public readonly int|float|null $currentPrice,
        public readonly int|float|null $marketCap,
        public readonly int|float|null $totalVolume,
        public readonly int|float|null $priceChangePercentage24h,
        public readonly ?string $shortDescription,
        public readonly array $homepageLinks,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: self::toNullableString($data['id'] ?? null),
            symbol: self::toNullableString($data['symbol'] ?? null),
            name: self::toNullableString($data['name'] ?? null),
            image: self::toNullableString($data['image'] ?? null),
            currentPrice: self::toNullableNumber($data['current_price'] ?? null),
            marketCap: self::toNullableNumber($data['market_cap'] ?? null),
            totalVolume: self::toNullableNumber($data['total_volume'] ?? null),
            priceChangePercentage24h: self::toNullableNumber($data['price_change_percentage_24h'] ?? null),
            shortDescription: self::toNullableString($data['short_description'] ?? null),
            homepageLinks: self::toHomepageLinks($data['homepage_links'] ?? []),
        );
    }

    /**
     * @return array<string, int|float|string|null|list<string>>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'symbol' => $this->symbol,
            'name' => $this->name,
            'image' => $this->image,
            'current_price' => $this->currentPrice,
            'market_cap' => $this->marketCap,
            'total_volume' => $this->totalVolume,
            'price_change_percentage_24h' => $this->priceChangePercentage24h,
            'short_description' => $this->shortDescription,
            'homepage_links' => $this->homepageLinks,
        ];
    }

    /**
     * @return list<string>
     */
    private static function toHomepageLinks(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        /** @var list<string> $links */
        $links = array_values(array_filter($value, static fn (mixed $url): bool => is_string($url) && $url !== ''));

        return $links;
    }
}
