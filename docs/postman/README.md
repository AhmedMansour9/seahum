# Seahum API documentation & Postman collections

Generated, reproducible API reference for the three client apps:

| Collection | Base URL variable | Routes |
|---|---|---|
| Seahum - Customer API | `{{api_host}}/api/v1` | `api/v1/*` |
| Seahum - Vendor API | `{{api_host}}/api` (folders `v3 - Current`, `v2 - Legacy`) | `api/v3/seller/*`, `api/v2/seller/*` |
| Seahum - Delivery API | `{{api_host}}/api/v2/delivery-man` | `api/v2/delivery-man/*` |

Admin routes are web-session routes, not a REST API, and are not documented here.

## Layout

```text
docs/postman/
├── collections/    curated Postman v2.1 collections (commit these)
├── environments/   Seahum Local / Seahum Staging (no secrets)
├── openapi/        OpenAPI 3.0.3 per client
├── reports/        route-inventory.json, coverage-report.md, unresolved-endpoints.md
├── scribe/         raw Scribe output + intermediate cache (git-ignored, regenerable)
└── tools/          generation scripts
```

## Regenerate

Run from the project root, in the **local** environment only:

```bash
php docs/postman/tools/export-routes.php       # 1. live route inventory
php docs/postman/tools/generate-scribe.php     # 2. Scribe static extraction (all three configs)
php docs/postman/tools/build-collections.php   # 3. collections, environments, OpenAPI, reports
```

`build-collections.php` exits non-zero if any effective route is missing from the collections, and fails outright if a route matches no folder rule. Add a rule to `tools/collection-definitions.php` when that happens.

The output is deterministic: stable ids and sorted items, so identical inputs give byte-identical files. Review changes with `git diff docs/postman`.

## Safety guarantees

- `config/scribe.php` removes `ResponseCalls` and every model/factory-instantiating response strategy. Several GET routes mutate state (order placement, account deletion, ticket close), so no endpoint is ever executed.
- `examples.models_source` is empty, so no factory or `create()` calls run.
- `try_it_out` is disabled, `type` is `static`, and output lives outside `public/`. No `/docs` routes are registered.
- `generate-scribe.php` refuses to run outside `local`/`testing`. It gives FormRequests a **non-persisted** placeholder user, because some `rules()` read `$this->user()->id`.
- `export-routes.php` never instantiates controllers. `route:list` crashes on `FirebaseServiceProvider` when Firebase is not configured.
- Request examples use placeholders only. Tokens come from the environment and are typed `secret`.

## Adding or changing a route

1. Add the route and controller as usual. Prefer a FormRequest (or `Validator::make` / `$request->validate`) so the parameters are extracted with their rules.
2. Run the three commands above.
3. Check `reports/coverage-report.md` (must stay at 100%) and `reports/unresolved-endpoints.md`.
4. Review `git diff docs/postman`.
5. Sync the changed collection to the `seahum` Postman workspace (see below).

## Syncing to Postman

The repo is bound to workspace `seahum` (`507c286f-5186-4261-86c5-b0e2ec2eff9f`) through `.postman/resources.yaml`. `postman/collections/` is the git-native mirror that the Postman CLI pushes, converted from the curated v2.1 files:

| Collection | Postman id |
|---|---|
| Seahum - Customer API | `5338765-a4f5f0a2-7d24-4970-8f20-e429a5ce4b76` |
| Seahum - Vendor API | `5338765-0247be22-6d5d-45e6-bc8f-802f2372b5fa` |
| Seahum - Delivery API | `5338765-43e1f722-065e-4946-bfe2-8322617ade65` |

After regenerating, run this (requires `postman login`):

```bash
bash docs/postman/tools/sync-postman.sh          # re-mirror, lint, show workspace diff (read-only)
bash docs/postman/tools/sync-postman.sh --push   # same, then push (create/update, never delete)
```

**Known issue:** after re-mirroring, `postman workspace diff` (CLI 1.73.0) reports changes for some requests whose names contain `/`, even when the cloud content is identical. Review the diff before `--push`, and verify a publish by reading the collections back (Postman MCP `getCollection`, `model=full`). For small incremental changes, prefer the `mam-sync-postman` skill, which updates individual requests by id.

Never use `--push-strategy force-sync` unless you intend to delete cloud entities that have no local copy. Never run these collections with a collection runner or monitor against shared data. See the warnings in each request description.

## Auth model

| Requirement | How the collection sends it |
|---|---|
| Customer `auth:api` (Passport) | collection bearer `{{customer_token}}` |
| Customer `apiGuestCheck` | bearer token **or** `guest_id={{guest_id}}` query (token wins when present) |
| Vendor v3 `seller_api_auth` | collection bearer `{{seller_token}}` |
| Vendor v2 (checked in handler via `Helpers::get_seller_by_token`) | collection bearer `{{seller_token}}` |
| Delivery `delivery_man_auth` | collection bearer `{{deliveryman_token}}` |
| Public routes | request-level `noauth` |

The login requests (`auth/login` in each collection) save the returned `token` to the matching environment variable.
