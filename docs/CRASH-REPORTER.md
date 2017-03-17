# Crash Reporter

Captures fatal errors and uncaught exceptions **originating from registered ThemeIsle products**, stores sanitized, deduplicated aggregates locally, and:

- sends them to `https://api.themeisle.com/tracking/crashes` on a jittered schedule **only when the logging consent is granted** (`{key}_logger_flag`, same consent as the [Logger](LOGGER.md));
- attaches a compact summary to the existing [uninstall feedback](UNINSTALL-FEEDBACK.md) request (same call, no extra request), regardless of the logging consent — disclosed in the survey's data-collection notice.

One set of PHP handlers serves every registered product on the site: the winning (newest) SDK copy captures crashes for **all** ThemeIsle products, including those bundling older SDKs.

## What is captured

| Source | Mechanism | Trace |
|--------|-----------|-------|
| Fatal errors (`E_ERROR`, `E_PARSE`, `E_CORE_ERROR`, `E_COMPILE_ERROR`) | `register_shutdown_function` + `error_get_last()` | None (PHP limitation) — file, line and message only. Trace text PHP embeds in uncaught-exception fatal messages is preserved up to 2000 chars. |
| Uncaught exceptions / Throwables | `set_exception_handler` (chained to any previously registered handler) | Full structured trace, all frames, arguments always dropped. |
| Bootstrap fatals (before the SDK loads on `init`) | Minimal PHP 5.4-safe sentinel in `load.php`, active only when the full module never registered | Raw record stored locally; sanitized and adopted by the module on the next normal load. |

Not captured: warnings/notices (`set_error_handler` is never used), crashes during `WP_SANDBOX_SCRAPING` requests (mirrors WordPress core's own silence during plugin/theme editor loopback tests).

## Attribution — "only our code"

A crash is stored **only when the crashing file lives inside a registered product's directory** (longest path-prefix match). Everything else is dropped before storage. Crashes inside the SDK itself attribute to the product bundling it, flagged `in_sdk`.

## Sanitization (before storage, not before send)

- Emails → `[email]`; bearer tokens, JWTs, 32+ char hex, 40+ char base64 → `[token]`.
- Known roots rewritten: `product:<rel>`, `sdk:<rel>`, `plugin:<slug>/<rel>`, `theme:<slug>/<rel>`, `wp:<rel>`; any other absolute path reduced to `.../basename`.
- Messages capped at 2000 chars, trace arguments never stored.

## Storage

Per-product option `{key}_crash_data` (never autoloaded):

```php
[
    'reports' => [ '<fingerprint>' => [
        'type', 'event_type', 'message', 'file', 'line', 'trace',
        'in_sdk', 'request_context', 'product_version', 'sdk_version',
        'count', 'first_seen', 'last_seen', 'time_since_update',
    ] ],
    'meta'    => [ 'last_update' => 1234567890 ],
    'raw'     => [ /* sentinel records awaiting adoption */ ],
]
```

Fingerprint = `md5(type|message(digits normalized)|file|line|product_version)`; duplicates bump `count`/`last_seen`. Caps: **15 distinct fingerprints** per product (lowest count, then oldest, evicted first) and **16 KB serialized** (traces trimmed to head+tail, then lowest-value reports dropped).

## Sending

On capture, with consent granted and no backoff pending, a single cron event `{key}_crash_flush` is scheduled with a random 1–6 h jitter (mirrors the Logger's pattern, avoids fleet-wide synchronized bursts). The flush POSTs to `tracking/crashes` (see [CRASH-ENDPOINT-SPEC.md](CRASH-ENDPOINT-SPEC.md)), clears the stored reports on any 2xx, and sets a 12 h backoff transient (`{key}_crash_backoff`) on failure — data stays local and is retried later. A product version change lifts the backoff immediately so a fixed release reports its health right away.

## Site Health

A short-form section lists each product with stored crashes: distinct/total counts, last-crash date, and the top fingerprint as one truncated line. No messages or traces — full detail stays in the option (`wp option get {key}_crash_data`).

## Filters & surface

| Hook | Purpose |
|------|---------|
| `{slug}_sdk_enable_crash_reporter` | Disable the module per product (default `true`). |
| `themeisle_sdk_crash_report_data` | Filter the outgoing scheduled payload (`$body`, `$product`). |
| `themeisle_sdk_uninstall_feedback_data` | Filter the uninstall feedback body (crash summary included) before POST. |
| `themeisle_sdk_disable_telemetry` | Global kill-switch shared with the Logger — blocks scheduled sending. |

Constant `THEMEISLE_SDK_CRASH_HANDLER` is defined once the full handler is active (the `load.php` sentinel stands down when it is present). With `WP_DEBUG` enabled, the reporter logs `[TISDK_CRASH] <reason>` breadcrumbs (`no_attribution`, `cap_evicted`, `payload_trimmed`, `backoff`, `send_failed`, `store_failed`) so field issues with the reporter itself are debuggable.

## Compatibility notes

- WordPress core's fatal handler runs first and intentionally does not exit (`wp_die( ..., [ 'exit' => false ] )`), so the reporter always runs after it; the reporter itself never outputs and never exits, and recovery mode / the "critical error" page behave exactly as without it.
- The exception handler chains to any previously registered handler; if a later plugin replaces ours without chaining, the resulting fatal is still recorded via the shutdown path.
- A ~16 KB memory buffer is reserved at registration and freed first thing at shutdown so out-of-memory fatals can still be recorded.

## Backlog (deliberately out of scope in v1)

Handled-error capture via the `themeisle_log_event` action, JS error capture via `tracking.js`, crash-loop advisory notices with one-click Rollback, opt-in safe mode.
