# SecureThreatShield (SecureTS)

![SecureTS Platform](src/gif/landing%20page%20view.gif)

A high-speed, monorepo-based security command center and active session management platform protecting Target Apps with MFA, biometric gates, and device security controls.

[![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-Passing-22c55e?style=for-the-badge&logo=githubactions&logoColor=white)](.github/workflows/tests.yml)
[![Vercel](https://img.shields.io/badge/Vercel-Deployment-000000?style=for-the-badge&logo=vercel&logoColor=white)](https://vercel.com)
[![Expo EAS](https://img.shields.io/badge/Expo_EAS-SDK_58-000020?style=for-the-badge&logo=expo&logoColor=white)](https://expo.dev)
[![Laravel 13](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![React Native](https://img.shields.io/badge/React_Native-0.88-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://reactnative.dev)

---

## Core Features

### Web Platform (The Hub)

![Web Integration](src/images/Web%20integration.jpg)
![Waiting Approval State](src/images/Waiting.jpg)

- **Integration Hub:** Register third-party Target Apps with dedicated Integration IDs and securely hashed API Secrets, supporting instant credential revocation and rotation.
- **Security Audit Trail:** Complete, immutable logging of all access requests, verification results (`approved`, `denied`, `expired`), IP addresses, and device fingerprints.
- **Active Session Management:** Centralized visibility over active user sessions across all connected Target Apps with immediate remote termination.
- **Stripe Subscriptions:** Tiered developer and organization billing (Pro, Ultra, Ultra Plus) managing integration caps, device limits, and team quotas.

### Mobile App (The Command Center)

![Mobile Layout](src/gif/Full%20mobile%20layout%20%28buggy%29.gif) 

- **Push Login Approvals:** Instant, interactive approve/deny prompts dispatched via high-throughput Laravel Reverb WebSockets and Expo Push Notifications (target p95 latency < 2s).
- **Device Security Controls:** Remote device locking, token revocation, and account recovery workflows for compromised or lost devices.
- **Biometric Admin Gates:** Local FaceID / TouchID / biometric authentication guarding high-security operations, token rotations, and sensitive approval confirmations.
- **TOTP Fallback:** Built-in RFC 6238 time-based one-time password generator (30-second rotating codes with visual countdown indicator) guaranteeing uninterrupted offline authentication. 

---

## Architecture & Monorepo Map

### Monorepo Structure

```
securets/
├── web/                             # Web Dashboard, REST API & WebSocket Server
│   ├── app/                         # Controllers, Models, Events, Notifications
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   └── Events/
│   ├── bootstrap/                   # App initialization & provider bindings
│   ├── config/                      # Laravel environment configuration
│   ├── database/                    # Migrations, seeders, and factories
│   │   ├── factories/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── public/                      # Static assets & entry index.php
│   ├── resources/                   # Livewire 4 components, Blade, Tailwind CSS
│   │   ├── css/
│   │   ├── js/
│   │   └── views/
│   ├── routes/                      # API, Web, Console, and Channels routes
│   │   ├── api.php
│   │   ├── channels.php
│   │   ├── console.php
│   │   └── web.php
│   ├── tests/                       # Pest 5 feature and unit test suite
│   │   ├── Feature/
│   │   └── Unit/
│   ├── composer.json                # PHP dependencies & dev scripts
│   ├── package.json                 # Web frontend dependencies & Vite scripts
│   ├── phpstan.neon                 # Larastan static analysis configuration
│   └── vite.config.js               # Vite asset bundling pipeline
│
└── mobile/                          # Command Center Authenticator App
    ├── components/                  # UI components, icons & biometric modals
    ├── public/                      # Mobile application branding & icons
    ├── scripts/                     # Network utilities (LAN / Tailscale detector)
    │   └── detect-lan-ip.js
    ├── src/
    │   ├── api/                     # Sanctum HTTP client (axios wrapper)
    │   │   └── client.js
    │   ├── lib/                     # Secure store & crypto utilities
    │   └── theme/                   # WorkOS-inspired dark mode theme tokens
    ├── App.jsx                      # Main application root & screen routing
    ├── app.json                     # Expo SDK 58 project configuration
    └── package.json                 # React Native & Expo dependencies
```

### Communication & Security Flow

```mermaid
flowchart TD
    subgraph TargetApp["External Ecosystem"]
        TA["Target App (Third-Party)"]
    end

    subgraph WebCore["SecureTS Web Hub (/web)"]
        API["Laravel 13 API (Sanctum Auth)"]
        Reverb["Laravel Reverb (Port 8085)"]
        DB[("Database (SQLite / Supabase)")]
    end

    subgraph MobileDevice["SecureTS Mobile Command Center (/mobile)"]
        RN["React Native Client (Expo SDK 58)"]
        Bio["Biometric Admin Gate"]
        Store["expo-secure-store (Instance Tokens)"]
    end

    %% Access Request Flow
    TA -->|"1. HTTPS POST /api/v1/access-requests (API Secret)"| API
    API -->|"2. Log Request & Persist State"| DB
    API -->|"3. Broadcast Push / WebSocket Event"| Reverb
    Reverb -->|"4. Real-Time Notification"| RN

    %% User Decision & Biometrics
    RN -->|"5. Gate Trigger & Local Authentication"| Bio
    Bio -->|"6. Authorization Confirmed"| RN
    RN -->|"7. HTTPS POST /api/v1/access-requests/{id}/decision (Instance Token)"| API
    Store -.->|"Hardware-Backed Secret Storage"| RN

    %% Target Resolution
    API -->|"8. Push Real-Time Decision"| Reverb
    API -->|"9. Return Status: Approved / Denied / Expired"| TA
    Reverb -.->|"State Synchronization"| TA
```

---

## Tech Stack Ecosystem

| Layer | Technologies | Role & Purpose |
| :--- | :--- | :--- |
| **Web API & Hub** | **Laravel 13** & **PHP 8.3+** | High-performance core backend framework and REST API engine. |
| **Server-Driven UI** | **Livewire 4** & **Blaze** | Real-time reactive web dashboard and integration management interface. |
| **Styling & Design** | **Tailwind CSS** (Vite) | WorkOS-inspired minimalist dark mode UI implementation. |
| **Real-Time Engine** | **Laravel Reverb** (Port 8085) | Ultra-low latency WebSocket broadcasting server for instant mobile approvals. |
| **Mobile Core** | **React Native** & **Expo** (SDK 58) | Cross-platform Command Center authenticator client. |
| **Mobile Security** | **`expo-secure-store`** | Encrypted hardware-backed storage for device Instance Tokens and secrets. |
| **Database (Dev)** | **SQLite** | Zero-configuration local database for frictionless development and CI tests. |
| **Database (Prod)** | **Supabase (PostgreSQL)** | Distributed cloud PostgreSQL for production persistence and team environments. |

---

## Quick Start (Zero to Hero)

> [!IMPORTANT]
> Physical mobile devices and the host development workstation must reside on the same Local Area Network (LAN) or be interconnected via a **Tailscale** mesh network.

### 1. Web Platform Setup

Navigate into the `/web` directory to configure and start the backend ecosystem:

```bash
cd web

# 1. Automated initialization (installs dependencies, copies .env, generates keys, runs migrations)
composer run setup

# 2. Start all services concurrently (Laravel API on 0.0.0.0:8000, Reverb on 8085, queue worker, Vite)
composer run dev
```

The web dashboard is now accessible at `http://localhost:8000`.

### 2. Mobile App Setup

In a separate terminal session, configure and launch the mobile Command Center:

```bash
cd mobile

# 1. Install dependencies and prepare environment configuration
npm install
cp .env.example .env

# 2. Launch the development server
# Recommended for cross-network access (Tailscale):
npm run start:tailscale

# Standard Local Area Network (LAN) mode:
npm start

# Target-specific emulator / simulator runs:
npm run android
npm run ios
```

Scan the terminal QR code using **Expo Go** on your physical mobile device.

---

## Rigorous CI/CD Standards

SecureTS enforces a strict continuous integration gate on all pull requests and branch merges into `main` using GitHub Actions:

- **Formatting Validation:** Code style consistency enforced strictly via `Laravel Pint` (`pint --parallel --test`).
- **Static Type Analysis:** Highest-tier strict static analysis with `Larastan` / `PHPStan` at **Level 9**.
- **Automated Test Suite:** Comprehensive feature and unit verification powered by `Pest 5` executed against an isolated in-memory SQLite database (`:memory:`).

To execute the identical verification suite locally prior to opening a PR:

```bash
cd web
composer run test
```

---

## Deployment Targets

- **Web API & Dashboard:** Optimized for serverless edge execution on **Vercel** utilizing the `vercel-php` runtime with optimized asset caching and environment-level database pooling.
- **Mobile Authenticator:** Production builds compiled and distributed across iOS (App Store / TestFlight) and Android (Google Play / APK) via **Expo Application Services (EAS)**.

---

## Documentation & License

### Documentation

- [Product Requirements Document (PRD)](PRD.md)
- [Contributing Guidelines](CONTRIBUTING.md)
- [Security Policy](SECURITY.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [AI Agent Operating Guide](AGENTS.md)

### License

Distributed under the [MIT License](LICENSE).  
Copyright &copy; 2026 Secure ThreatShield Organization. All rights reserved.
