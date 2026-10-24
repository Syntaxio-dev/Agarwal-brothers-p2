# Sales API (the phone app <-> the website)

Base address: `https://<site>/api/sales/v1`. JSON in, JSON out. The module is self-contained (`app/Sales`, `routes/sales-api.php`,
`config/sales.php`, tables `sales_*`), so it can move to its own sub-domain: only the base address in the app changes.

## Rules that never change
- **Every record has a UUID made by the phone.** The same record can be sent any number of times: it never exists twice.
- **A rep can only read and write their own records.** An id that belongs to somebody else is answered `rejected UUID_TAKEN`.
- **Only the office changes `status`** (`pending`, `approved`, `rejected`). A reviewed expense is locked (`LOCKED`).
- Money is whole paise (`amount_paise`, integer). Dates are `YYYY-MM-DD`; moments are ISO 8601 UTC (`2026-10-25T05:30:00.000Z`).
- Every field is checked again on the server with the same limits as the app. A bad record is rejected on its own; the others in the same push are still saved.
- Errors always look like `{"code": "...", "message": "...", "errors": {...}}`.

## Errors (HTTP status, `code`)
| Status | code | Meaning |
|---|---|---|
| 401 | `INVALID_CREDENTIALS` | wrong employee code / phone or password (same answer for an unknown rep) |
| 401 | `UNAUTHENTICATED` | no token, a wrong one, or an expired one: sign in again |
| 403 | `ACCOUNT_INACTIVE` | the rep was switched off; all tokens are revoked at once |
| 404 | `NOT_FOUND` | not found (also: somebody else's record) |
| 409 | `LOCKED` | the expense was already reviewed |
| 413 | `PAYLOAD_TOO_LARGE` | bill photo bigger than 5 MB |
| 422 | `VALIDATION_FAILED` / `INVALID` | a field is not valid; `errors` says which |
| 429 | `TOO_MANY_REQUESTS` | slow down (limits: login 5 per minute per login, 120 calls per minute per rep) |

## `POST /auth/login`
Body: `{ "login": "REP001" | "98765 43210", "password": "...", "app_version": "1.3", "device_model": "Pixel 7" }`
Answer: `{ "token": "...", "token_type": "Bearer", "expires_at": "...", "account": { "name", "employee_code", "phone" }, "server_time": "..." }`
One phone at a time: signing in revokes the rep's earlier tokens. The token lasts 30 days. Send it as `Authorization: Bearer <token>`.

## `POST /auth/logout`, `GET /me`
Logout deletes the token. `/me` answers `{ "account": {...}, "server_time": "..." }` (also a quick "is this token still good" check).

## `POST /sync/push`
Body (every part optional, at most 300 records of each kind per push):
```json
{
  "app_version": "1.3", "device_model": "Pixel 7",
  "expenses": [{ "uuid", "category": "travel|food|lodging|fuel|other", "amount_paise": 45000, "expense_date": "2026-10-24", "note": "", "has_bill": true, "updated_at": "2026-10-24T10:00:00.000Z" }],
  "clients":  [{ "uuid", "name", "company", "phone", "city", "notes", "updated_at" }],
  "meetings": [{ "uuid", "client_uuid", "client_name", "meeting_at", "purpose", "discussion", "outcome", "next_followup_at", "updated_at" }],
  "deleted":  { "expenses": ["uuid"], "clients": ["uuid"], "meetings": ["uuid"] }
}
```
Answer: `{ "results": { "expenses": [...], "clients": [...], "meetings": [...], "deleted": { "expenses": [...], ... } }, "server_time": "..." }`.
Each result: `{ "uuid", "index", "status": "accepted" | "duplicate" | "rejected", "code"?, "message"?, "errors"? }`.
- `accepted`: new or changed. `duplicate`: nothing newer than what the server has (safe to mark as sent).
- `rejected`: `INVALID` (a rule is broken), `LOCKED` (reviewed expense), `UUID_TAKEN`, `SERVER_ERROR`.
- `updated_at` is the phone's "last changed" time. The server keeps the newest. Deleting is a wish ("make it gone"): an unknown or already deleted record is `accepted`.
- A bill photo is not part of the push: send it with `POST /uploads/bill` after the expense was accepted.

## `GET /sync/pull?since=<ISO moment>`
Everything of this rep that changed since `since` (everything when it is left out): the office's decisions, and a full restore on a new phone.
Answer: `{ "expenses": [...], "clients": [...], "meetings": [...], "has_more": false, "cursor": "...", "server_time": "...", "account": {...} }`.
Records have the same fields as in the push plus, for expenses, `status`, `reviewed_at`, `reject_reason`, `has_bill` (a photo is stored on the server), and `deleted: true` for deleted ones.
Use `cursor` as the next `since`. While `has_more` is true, pull again at once with the new cursor. Rows can come twice (the overlap is on purpose): merging is idempotent.

## `POST /uploads/bill`  and  `GET /expenses/{uuid}/bill`
Upload: `{ "expense_uuid": "...", "data_base64": "..." }` (JPEG, PNG or WebP, at most 5 MB; judged by the picture's real bytes, not by a name or type the phone claims). Only for a pending expense of your own. Answer `{ "status": "stored" }`.
Download: `{ "mime": "image/jpeg", "data_base64": "..." }` (a new phone after a restore). Photos are stored on the private disk; managers see them through the admin panel only.

## Not yet (planned)
Minimum supported app version (forced update), call logs (needs written company approval), push notifications.
