<?php

namespace App\Providers;

use App\Contracts\CoinGeckoAdapter as CoinGeckoAdapterContract;
use App\Contracts\CoinGeckoClient as CoinGeckoClientContract;
use App\Services\CoinGecko\CoinGeckoAdapter;
use App\Services\CoinGecko\CoinGeckoClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CoinGeckoClientContract::class, CoinGeckoClient::class);
        $this->app->bind(CoinGeckoAdapterContract::class, CoinGeckoAdapter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('crypto-api', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
