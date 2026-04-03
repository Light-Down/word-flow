# Update Endpoint Upload

Your app currently checks these URLs:
- https://word-flow.store/update/version.json
- https://word-flow.store/update.json

## Option A (recommended): static JSON

Upload these files:
- `Deployment/update/version.json` -> `/update/version.json`
- `Deployment/update.json` -> `/update.json`

## Option B: PHP endpoint

If your host does not serve `.json` with JSON content-type reliably, upload:
- `Deployment/version.php` -> `/update/version.php`

Then point app endpoint to `/update/version.php`.

## Test

Run:

```bash
curl -i https://word-flow.store/update/version.json
```

Expected:
- Status: `200`
- Header includes `Content-Type: application/json`
- Body is valid JSON with `version` and `updateURL`

## Notes

- If app version is `1.0` and server says `1.0.1`, update is shown.
- If versions are equal, app shows up-to-date message.
