export interface CoinMarketItem {
  id: string | null;
  symbol: string | null;
  name: string | null;
  image: string | null;
  current_price: number | null;
  market_cap: number | null;
  total_volume: number | null;
  price_change_percentage_24h: number | null;
}

export interface CoinDetail extends CoinMarketItem {
  short_description: string | null;
  homepage_links: string[];
}

export interface ApiError {
  error: {
    code: string;
    message: string;
    details?: Record<string, unknown>;
  };
  request_id: string;
}

export interface ApiSuccess<T> {
  data: T;
  request_id: string;
}
