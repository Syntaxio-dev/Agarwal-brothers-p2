# Sales API (the phone app <-> the website)

Base address: `https://<site>/api/sales/v1`. JSON in, JSON out. The module is self-contained (`app/Sales`, `routes/sales-api.php`,
`config/sales.php`, tables `sales_*`), so it can move to its own sub-domain: only the base address in the app changes.

## Rules that never change
- **Every record has a UUID made by the phone.** The same record can be sent any number of times: it never exists twice.
- **Private records (expenses, meetings, calls)**: a rep can only read and write their own. An id that belongs to somebody else is answered `rejected UUID_TAKEN`.
- **Shared records (the client pool: `clients` and `contacts`, plus the `positions` list)**: every rep sees all of them and can add to them, but **only the rep who added a record can change or delete it from the phone** (`rejected NOT_ALLOWED` otherwise; managers change and delete in the admin). A rep can still add their own people to somebody else's client. Each client and person comes back with `created_by_code` and `created_by_name` (who added it). The server decides who a client is *assigned to*: a phone never sends it.
- **A phone only ever sends calls whose number is in the pool.** Calls with other numbers never leave the phone, and the server checks it too: a call whose number was never saved as a client or person (a deleted one still counts) is `rejected INVALID`.
- **One number, one place in the pool**: a client's main number or a person's number that is already saved under another client or person is `rejected INVALID` ("already saved under ..."), whatever order two offline phones sent them in.
- **The phone's clock is checked**: a change stamped more than 10 minutes ahead of the server's time is `rejected INVALID` ("this phone's clock is ahead"): it would otherwise win every later edit for ever. (A clock that is *behind* only loses to newer edits, as intended.)
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
| (in a push result) | `NOT_ALLOWED` | a shared record (client, person) can only be changed or deleted by the rep who added it; the phone then takes the office's copy back |
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
Body (every part optional; at most 300 records of each kind per push, 40 reports, 50 positions):
```json
{
  "app_version": "1.3", "device_model": "Pixel 7",
  "expenses": [{ "uuid", "category": "travel|food|fuel|parking|gifting|lodging|other", "amount_paise": 45000, "expense_date": "2026-10-24", "note": "", "work_area": "local|outstation", "bill_type": "with_bill|without_bill", "has_bill": true, "updated_at": "2026-10-24T10:00:00.000Z" }],
  "clients":  [{ "uuid", "name", "company", "phone", "city" /* the place */, "notes", "updated_at" }],
  "scripts": [{ "uuid", "product_uuid" (null = not a product of the guide), "product_name", "title", "body" (3 to 3000 letters), "updated_at" }],
  "contacts": [{ "uuid", "client_uuid" (null = a person without a company), "name", "position", "phone", "email", "notes", "updated_at" }],
  "meetings": [{ "uuid", "client_uuid", "client_name", "contact_uuids": ["uuid"], "meeting_at", "purpose", "discussion", "outcome", "next_followup_at", "updated_at" }],
  "enquiries": [{ "uuid", "client_uuid", "client_name", "contact_uuid", "contact_name", "status": "new|price_to_send|price_sent|callback|meeting_planned|negotiation|won|lost",
                  "items": [{ "product", "brands", "quantity": 1, "budget_paise": 15000000 }], "opened_at", "next_followup_at", "closed_at",
                  "timeline": [{ "at", "text", "status" }], "updated_at" }],
  "reports":  [{ "uuid", "report_date": "2026-10-27", "notes", "tomorrow_plan", "issues", "figures": { "calls", "calls_missed", "talk_seconds", "meetings", "enquiries_new", "enquiries_worked", "expenses_paise" }, "submitted_at", "updated_at" }],
  "calls":    [{ "uuid", "direction": "incoming|outgoing|missed", "number": "+919876543210", "client_uuid", "contact_uuid", "client_name", "contact_name", "started_at", "duration_seconds", "updated_at" }],
  "positions": ["Plant Chemist"],
  "deleted":  { "expenses": ["uuid"], "clients": ["uuid"], "contacts": ["uuid"], "meetings": ["uuid"], "enquiries": ["uuid"], "scripts": ["uuid"] }
}
```
- A client is sent before the people at it (same push is fine). A new client made by a rep is *assigned to that rep*; deleting a client also deletes its people.
- `phone` is any spelling of a number (the server stores the one given and matches on the `+91...` form). Positions are 2 to 60 letters, digits and `& / ( ) . , ' -`; a position typed on a person is added to the list too.
- An **enquiry** is private to the rep. It asks for 1 to 10 products at once (`items`: product 2..150, brands, quantity 1..100000, budget in paise). The first six statuses are *open*, `won` and `lost` are *closed*: a closed enquiry has no follow-up (the server clears it) and gets `closed_at`. `timeline` (at most 100 entries of 500 characters) is the rep's notes and status changes, kept in order. The reminder logic lives in the app ("overdue" = follow-up passed; "idle" = open, no follow-up date and untouched for 3 days); the website computes the same from `next_followup_at` and `client_updated_at`. A follow-up more than 2 years ahead is refused (the app refuses it first).
- A **daily report** is one per rep per day (`report_date` from 31 days back to tomorrow). Its `uuid` is made from rep + day, so the same day is always the same report; a second report for a day under another id is `rejected INVALID`. `submitted_at` null = a draft: the website lists only submitted ones. `figures` are the numbers the phone showed (non-negative whole numbers). Reports can be corrected, never deleted from the phone.
- A **call** is a log: never changed, never deleted. Its `uuid` is made from rep + number + start + direction, so importing the call log twice makes one row. `number` must already be E.164.
Answer: `{ "results": { "expenses": [...], "clients": [...], "contacts": [...], "meetings": [...], "enquiries": [...], "calls": [...], "reports": [...], "positions": [{ "name", "status" }], "deleted": { "expenses": [...], ... } }, "server_time": "..." }`.
Each result: `{ "uuid", "index", "status": "accepted" | "duplicate" | "rejected", "code"?, "message"?, "errors"? }`.
- `accepted`: new or changed. `duplicate`: nothing newer than what the server has (safe to mark as sent).
- `rejected`: `INVALID` (a rule is broken), `LOCKED` (reviewed expense), `UUID_TAKEN`, `SERVER_ERROR`.
- `updated_at` is the phone's "last changed" time. The server keeps the newest. Deleting is a wish ("make it gone"): an unknown or already deleted record is `accepted`.
- A bill photo is not part of the push: send it with `POST /uploads/bill` after the expense was accepted.

## `GET /sync/pull?since=<ISO moment>`
Everything that changed since `since` (everything when it is left out): this rep's own records (expenses, meetings, calls), the whole shared pool (clients, contacts) and the position list. It is also how the office's decisions and a full restore on a new phone arrive.
Answer: `{ "expenses": [...], "clients": [...], "contacts": [...], "meetings": [...], "enquiries": [...], "calls": [...], "reports": [...], "positions": ["..."], "has_more": false, "cursor": "...", "server_time": "...", "account": {...} }`.
Records have the same fields as in the push plus: expenses (`work_area` and `bill_type` may be null for an expense an older phone sent; a phone that does not send them leaves the stored answers as they are) `status`, `reviewed_at`, `reject_reason`, `has_bill` (a photo is stored on the server); clients `assigned_to_code`, `assigned_to_name`, `created_by_code`, `created_by_name` (the office's: the phone shows them and never sends them back); contacts `created_by_code`; and `deleted: true` for deleted ones. `positions` is the whole list on every pull (it is short).
A change made in the admin (a client given to another rep, a corrected place) gets a new `updated_at` on the server, so it wins on every phone.
Use `cursor` as the next `since`. While `has_more` is true, pull again at once with the new cursor. A page always includes every row of the last second it reached (so a page can be longer than the limit) and the cursor then moves past that second: thousands of rows with one timestamp (an import) cannot stall the paging. Rows can come twice (the overlap is on purpose): merging is idempotent.

## `POST /uploads/bill`  and  `GET /expenses/{uuid}/bill`
Upload: `{ "expense_uuid": "...", "data_base64": "..." }` (JPEG, PNG or WebP, at most 5 MB; judged by the picture's real bytes, not by a name or type the phone claims). Only for a pending expense of your own. Answer `{ "status": "stored" }`.
Download: `{ "mime": "image/jpeg", "data_base64": "..." }` (a new phone after a restore). Photos are stored on the private disk; managers see them through the admin panel only.

## Not yet (planned)
Minimum supported app version (forced update), push notifications. (Call matching is built: the phone reads its own call log, keeps only calls with pool numbers and sends those; the rep must turn it on in the app.)

## Product guide additions (29 Oct)

`GET /products` also returns, per product: `client_types` (ids: `pharma_qc`, `hospital`, `university`, `food_dairy`, `industrial_qc`, `water_environment`), `pitch`, `key_notes` (max 5), `formula`, `do` and `avoid` (max 3 each), `match_rules` (`[{ "if", "then" }]`, max 4). All are empty when the manager has not written them. Words between two stars (`**like this**`) are highlighted by the phone.

`scripts` are a rep's own pitches: private to the rep (pull returns only this rep's), changed only by that rep (a foreign uuid is `UUID_TAKEN`), pulled with `deleted: true` when removed. A manager can read and remove them in the admin.
