# HEMA Scorecard API (v1)

Read-only, API-key-authenticated HTTP JSON API at `/api/v1/*`. Built on
[Flight PHP](https://docs.flightphp.com/). Isolated from the web app's
session and `ALLOW` permission system.

## Dev workflow

1. **One-time setup.** `composer install` at the repo root (host-side —
   Composer isn't required inside the Docker container). This populates
   `vendor/`, which is committed, so pulling the branch alone is usually
   enough.

2. **Mint a local API key.**
   ```bash
   docker-compose exec web php /hemaScorecard/api/bin/generate-key.php "local-dev"
   ```
   The generator appends the new key to `data/api_keys.json` (creating
   the file if needed). Copy the printed key for use in requests.

3. **Run the app as usual.**
   ```bash
   docker-compose up
   ```

4. **Smoke test.**
   ```bash
   curl http://localhost:8000/api/v1/health
   # {"data":{"status":"ok","version":"v1"}}

   curl -H "X-API-Key: $KEY" http://localhost:8000/api/v1/any-real-route
   ```

## Request contract

- Method: `GET` only. Anything else returns `405`.
- Auth: `X-API-Key: <key>` header OR `Authorization: Bearer <key>`
  header. Query-string keys are not accepted.
- `/api/v1/health` is the only unauthenticated route.

## Response contract

Every response sets `Content-Type: application/json; charset=utf-8` and
`X-Api-Version: v1`.

**Success (single resource):**
```json
{ "data": { "...": "..." } }
```

**Success (collection):**
```json
{ "data": [ ... ], "meta": { "count": 42 } }
```

**Error:**
```json
{ "error": { "code": "not_found", "message": "Event 123 not found" } }
```

Error codes: `bad_request` (400), `unauthorized` (401), `forbidden`
(403), `not_found` (404), `method_not_allowed` (405),
`internal_error` (500).

## Adding an endpoint

1. Create a controller in `api/controllers/` under the
   `HemaScorecard\Api\Controllers` namespace.
2. Register the route in `api/routes/v1.php`:
   ```php
   use HemaScorecard\Api\Controllers\EventsController;
   Flight::route('GET /api/v1/events', [EventsController::class, 'index']);
   ```
3. In the controller, call `JsonResponse::success($payload)` on the
   happy path, or throw `new ApiException('not_found', 404, '...')`
   on expected error states.
4. For response-shaping conventions (naming, time rendering, block shape),
   see the **Conventions** section below.

## Conventions

**Time-of-day rendering.** The DB stores daily times as minutes since midnight
(int). API responses emit `HH:MM` 24-hour strings via
`HemaScorecard\Api\Lib\TimeFormat::minutesToHhmm(int): string`. Use this helper
for any new schedule-adjacent endpoint. Clamps negatives to `00:00`; wraps
past-24h values modulo 1440 (organizers should use `dayNum+1` for past-midnight
blocks).

**Schedule-block response shape.** `HemaScorecard\Api\Lib\ScheduleBlocks::shape(array $row)`
produces the canonical block shape. Use it for any endpoint that exposes a
`logisticsScheduleBlocks` row. The shape:

```json
{
  "blockID": 4401,
  "blockType": "workshop",
  "dayNum": 2,
  "startTime": "13:00",
  "endTime": "14:30",
  "title": "...",
  "subtitle": "...",
  "description": "...",
  "link": "...",
  "linkDescription": "...",
  "tournamentID": null,
  "locations": [{ "locationID": 3, "name": "...", "shortName": "..." }]
}
```

`blockType` maps `blockTypeID` 1/2/3/4 → `"tournament" / "workshop" / "staffing" / "misc"`.
Unknown IDs fall back to `"misc"`.

**Location batch-fetch.** `ScheduleBlocks::enrichWithLocations(array $rows)` runs
one batched query over `blockID IN (...)` to attach `locations` arrays. Never
fetch per-block in a loop.

**Shape-method naming.** New response-shaping helpers in controllers should
follow:

- `shapeListItem(array $row): array` — converts one row for a list response.
- `shapeSingle(array $row, ...): array` — converts the full single-resource shape.

Inline anonymous functions are fine for small one-off shapes (e.g. the
tournaments list inside a ruleset detail). Avoid `shapeItem` as a generic name
— prefer the list/single distinction.

## Key management

Keys live in `data/api_keys.json` (gitignored). Each entry:
```json
{ "key": "hsc_...", "label": "who this is for", "revoked": false }
```

Revoke by flipping `revoked` to `true`. Rotation means adding a new
entry and revoking the old one. No restart required — the middleware
reads the file per request.

## Files

| Path | Responsibility |
|---|---|
| `router.php` | PHP built-in server router; hands `/api/*` to `api/index.php`. |
| `api/index.php` | Flight front controller — middleware + error handlers + route include. |
| `api/bootstrap.php` | MySQL init (no session, no `ALLOW`). |
| `api/lib/JsonResponse.php` | Enforces the `{data \| error}` envelope + headers. |
| `api/lib/ApiException.php` | Controlled error type caught by Flight's error handler. |
| `api/middleware/ApiKeyAuth.php` | Validates `X-API-Key` / `Bearer` against `data/api_keys.json`. |
| `api/controllers/` | One class per resource. |
| `api/routes/v1.php` | Route registrations. |
| `api/bin/generate-key.php` | CLI helper to append a new key. |
