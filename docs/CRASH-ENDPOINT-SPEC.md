# `tracking/crashes` — Endpoint Contract (for the API team)

New endpoint receiving crash reports from the SDK crash reporter module. Sits alongside the existing `tracking/{log|events|uninstall}` family.

```
POST https://api.themeisle.com/tracking/crashes
Content-Type: application/x-www-form-urlencoded
```

The SDK treats **any 2xx** as delivered (local store cleared). Any other status or transport error triggers a 12-hour client-side backoff; reports are kept locally (aggregated, capped) and retried. Client timeout is 3 seconds — respond fast, process async.

## Request body

| Field | Type | Notes |
|-------|------|-------|
| `site` | string | `get_site_url()` — same as the Logger payload. |
| `slug` | string | Product slug. |
| `version` | string | Product version. |
| `wp_version` | string | WordPress version. |
| `php_version` | string | PHP version. |
| `sdk_version` | string | Winning SDK copy version. |
| `locale` | string | Site locale. |
| `license` | string | License status (empty for free products). |
| `reports` | string | **JSON-encoded array** of report objects (below). |

## Report object

```json
{
  "fingerprint": "md5 hash — dedup key: type|message(digits normalized)|file|line|product_version",
  "type": 1,
  "event_type": "fatal_error | uncaught_exception",
  "message": "sanitized, ≤2000 chars (≤500 if size-trimmed client-side)",
  "file": "product:inc/broken.php",
  "line": 42,
  "trace": [ { "file": "product:inc/caller.php", "line": 3, "function": "Cls->method" } ],
  "in_sdk": false,
  "request_context": "cron|ajax|rest|cli|admin|frontend",
  "product_version": "1.2.3",
  "sdk_version": "3.4.0",
  "count": 7,
  "first_seen": 1784000000,
  "last_seen": 1784600000,
  "time_since_update": 3600
}
```

Path prefixes: `product:` (the crashing product), `sdk:` (ThemeIsle SDK), `plugin:<slug>/`, `theme:<slug>/` (third-party), `wp:` (core), `.../basename` (anything else). No raw absolute paths, emails or tokens ever arrive — redacted client-side before storage. `time_since_update` (seconds since the product's last version change) is present only when known; a spike of low values across sites for one `version` = bad-release early warning.

The uninstall channel: the existing `tracking/uninstall` POST may now also carry a `crashes` field — JSON array of at most 5 compact report objects (same shape, `message` ≤200 chars, trace limited to `product:`/`sdk:` frames).

## Server-side recommendations

- Rate-limit per `site`+`slug` at the gateway with its own bucket, so crash floods never starve the other tracking endpoints (clients cap at ~1 send / 12 h on failure, and aggregate client-side, but assume hostile input).
- Max body size: reports are client-capped at 16 KB serialized; reject larger payloads.
- Aggregate by `fingerprint` × `version`; alert on new fingerprints spiking shortly after a release (`time_since_update` low).
