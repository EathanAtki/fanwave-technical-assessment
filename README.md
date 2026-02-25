# Fanwave Technical Assessment

Production-oriented full-stack cryptocurrency viewer built for a greenfield four-hour assessment.

## Final Repository Structure

```text
.
+- backend/              # Laravel internal API
+- frontend/             # Nuxt 3 client app
+- README.md
```

## Architecture

- Backend: Laravel 11-style code organization with service/adapters, validation, caching, retries, and rate limiting.
- Frontend: Nuxt 3 + Vue 3 + Tailwind using composables for API state and debounced searching.
- Integration: Browser only calls Laravel (`/api/markets`, `/api/coins/{id}`); Laravel alone calls CoinGecko.

## API Contract

### `GET /api/markets?q=`
- Returns top 10 by market cap (descending)
- Optional `q` filters by `name` or `symbol`

### `GET /api/coins/{id}`
- Returns detail for a single coin

### Success envelope

```json
{
  "data": [],
  "request_id": "uuid"
}
```

### Error envelope

```json
{
  "error": {
    "code": "RATE_LIMITED",
    "message": "Too many requests. Please retry shortly.",
    "details": {}
  },
  "request_id": "uuid"
}
```

## API Documentation (Swagger/OpenAPI)

- Swagger UI: `http://localhost/docs`
- OpenAPI spec (YAML): `http://localhost/docs/openapi.yaml`

The docs are served by Laravel web routes and document:
- `GET /api/markets`
- `GET /api/coins/{id}`

## Backend Highlights

- CoinGecko adapter isolates upstream schema (`app/Services/CoinGecko/CoinGeckoAdapter.php`)
- CoinGecko client applies timeout + retry (`app/Services/CoinGecko/CoinGeckoClient.php`)
- Independent caching for list/detail (`app/Services/CryptoMarketService.php`)
- Request validation (`app/Http/Requests/MarketIndexRequest.php`)
- Consistent JSON errors for validation/429/upstream issues (`bootstrap/app.php`, `app/Support/ApiErrorResponder.php`)
- Request IDs added via middleware (`app/Http/Middleware/AttachRequestId.php`)

## Frontend Highlights

- Debounced search via composable (`frontend/composables/useMarkets.ts`)
- Loading skeletons and retry banners (`frontend/components/MarketList.vue`, `frontend/pages/coins/[id].vue`)
- Runtime backend base URL (`frontend/nuxt.config.ts` via `NUXT_PUBLIC_API_BASE_URL`)
- Keyboard-accessible links/buttons and visible focus styles (`frontend/assets/css/main.css`)

## Testing

### Backend (Pest/PHPUnit style)
- `backend/tests/Feature/MarketsApiTest.php`
- `backend/tests/Feature/CoinDetailsApiTest.php`
- `backend/tests/Unit/CoinGeckoAdapterTest.php`

### Frontend (Vitest + Vue Testing Library)
- `frontend/tests/unit/market-list.test.ts`
- `frontend/tests/unit/use-markets.test.ts`
- `frontend/tests/unit/coin-detail-page.test.ts`

### End-to-end (Playwright)
- `frontend/tests/e2e/app.spec.ts`

## Gherkin to Test Mapping

- Homepage shows top 10: backend `MarketsApiTest::test_markets_endpoint_returns_10_items_sorted_by_market_cap_descending`, e2e `homepage loads and shows market list`
- Homepage handles API failure: e2e `error states render correctly`
- Homepage loading state: unit `market-list.test.ts` loading skeleton case
- Search by name/symbol: backend `test_search_filters_by_name_and_symbol`, e2e `search filters results`
- Search debounce: unit `use-markets.test.ts`
- Clear search: composable behavior covered in `useMarkets` flow (empty query triggers default list request)
- Search validation: backend `test_validation_errors_return_422_in_consistent_shape`
- Navigate to details: e2e `navigation to detail page works`
- Unknown coin: backend `test_unknown_coin_returns_404_with_error_shape`, e2e `unknown coin shows not found state`
- Detail API failure: backend `test_upstream_failure_returns_502`, e2e `detail failure shows retry action`
- Mobile layout: e2e `mobile viewport smoke test`
- Keyboard navigation/focus: implemented in UI and global CSS focus-visible rules
- Cached markets: backend `test_caching_prevents_duplicate_upstream_calls_within_ttl`
- Rate limiting: backend `test_rate_limiting_returns_429`, e2e `error states render correctly`

## Run Locally (Sail)

### Backend (Laravel Sail, Windows PowerShell)

Prerequisites:
- Docker Desktop running
- Do not use Git Bash for Sail commands (`MINGW64` is unsupported)

```powershell
cd backend
Copy-Item .env.example .env -ErrorAction SilentlyContinue
docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
.\vendor\bin\sail.bat up -d
.\vendor\bin\sail.bat artisan key:generate
.\vendor\bin\sail.bat artisan migrate
.\vendor\bin\sail.bat artisan route:list
.\vendor\bin\sail.bat artisan test
```

Backend base URL: `http://localhost`  
API base URL: `http://localhost/api`

### Backend (Laravel Sail, macOS / Linux / WSL)

```bash
cd backend
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan route:list
./vendor/bin/sail artisan test
```

### Frontend (Windows PowerShell)

```powershell
cd frontend
npm install
$env:NUXT_PUBLIC_API_BASE_URL="http://localhost"
# runs Nuxt prepare automatically; no need to start dev first
npm run test:unit
npm run dev
```

### Frontend (macOS / Linux / WSL)

```bash
cd frontend
npm install
export NUXT_PUBLIC_API_BASE_URL="http://localhost"
# runs Nuxt prepare automatically; no need to start dev first
npm run test:unit
npm run dev
```

Convention: `NUXT_PUBLIC_API_BASE_URL` should be host-only (no `/api`). Frontend requests use `/api/...` paths.

### E2E

Playwright starts the Nuxt dev server automatically via `playwright.config.ts`.

```bash
cd frontend
npm run test:e2e
```

## Quality Checks

### Backend quality checks (Windows PowerShell)

```powershell
cd backend
.\vendor\bin\sail.bat artisan test
.\vendor\bin\sail.bat php ./vendor/bin/pint --test
```

Additional improvement to consider (not currently implemented in this repo): static analysis with PHPStan/Larastan.

```powershell
cd backend
.\vendor\bin\sail.bat composer require --dev larastan/larastan phpstan/phpstan
.\vendor\bin\sail.bat php ./vendor/bin/phpstan analyse
```

### Backend quality checks (macOS / Linux / WSL)

```bash
cd backend
./vendor/bin/sail artisan test
./vendor/bin/sail php ./vendor/bin/pint --test
```

Additional improvement to consider (not currently implemented in this repo): static analysis with PHPStan/Larastan.

```bash
cd backend
./vendor/bin/sail composer require --dev larastan/larastan phpstan/phpstan
./vendor/bin/sail php ./vendor/bin/phpstan analyse
```

### Frontend quality checks

```bash
cd frontend
npm run test:unit
npm run test:e2e
```

## Additional Improvements To Consider

- Add Laravel Pint as an enforced formatting gate in CI.
- Add PHPStan + Larastan with a baseline and fail-on-new-errors policy.
- Add CI workflow that blocks merges on tests + lint + static analysis.

