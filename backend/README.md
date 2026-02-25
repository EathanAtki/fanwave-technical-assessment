# Backend API (Laravel + Sail)

Internal API for cryptocurrency market data. The browser calls this API only; this service calls CoinGecko.

## Endpoints

- `GET /api/markets?q=`
- `GET /api/coins/{id}`

## Response Shape

Success:

```json
{
  "data": {},
  "request_id": "uuid"
}
```

Error:

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

Source file: `backend/docs/openapi.yaml`

## Environment

Configure in `.env` (or keep defaults):

- `COINGECKO_BASE_URL=https://api.coingecko.com/api/v3`
- `COINGECKO_TIMEOUT_SECONDS=3`
- `COINGECKO_RETRY_TIMES=2`
- `COINGECKO_RETRY_SLEEP_MS=150`
- `COINGECKO_CACHE_TTL_MARKETS=30`
- `COINGECKO_CACHE_TTL_COIN=30`

## Run With Sail

### Windows PowerShell

Use `sail.bat` (do not use Git Bash for Sail):

```powershell
cd backend
Copy-Item .env.example .env -ErrorAction SilentlyContinue
.\vendor\bin\sail.bat up -d
.\vendor\bin\sail.bat artisan key:generate
.\vendor\bin\sail.bat artisan migrate
.\vendor\bin\sail.bat artisan route:list
```

### macOS / Linux / WSL

```bash
cd backend
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan route:list
```

## Tests

### Windows PowerShell

```powershell
.\vendor\bin\sail.bat artisan test
```

### macOS / Linux / WSL

```bash
./vendor/bin/sail artisan test
```

## URLs

- App: `http://localhost`
- API: `http://localhost/api`
- Docs UI: `http://localhost/docs`
- OpenAPI: `http://localhost/docs/openapi.yaml`
