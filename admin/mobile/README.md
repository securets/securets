# SecureTS Admin Mobile

React Native + Expo emergency keypad for the admin system.

The system mobile Expo server uses port `8081`. The admin mobile Expo server
uses port `8082` so both apps can run at the same time.

```powershell
npm install
npx expo start --port 8082
```

If the app is still showing stale data or loading placeholders, restart Expo
with its cache cleared:

```powershell
npx expo start --port 8082 -c
```

The Expo web development URL is `http://localhost:8082`. The Laravel admin
web/API service remains the separate service configured by `admin/web/.env`
(`APP_URL=http://localhost:8001`) and its routes are under `/admin`.
