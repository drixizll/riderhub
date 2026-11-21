# RiderHub Decoupled Frontend & Backend Architecture (Phase 1 Ready)

This repository now includes a **100% free, zero-downtime** decoupled architecture foundation so the **Backend API** and **Frontend Client** can work independently without breaking the live Laravel system on XAMPP or Hostinger (`riderhub.online`).

## Current Structure
- **Backend REST API (Laravel 12):** `routes/api.php` → `app/Http/Controllers/Api/V1/PlatformApiController.php`
  - `GET /api/v1/health` — Backend & database health check
  - `GET /api/v1/summary` — Platform metadata, fare rates, service region, payment methods
  - `POST /api/v1/fare-estimate` — Distance / coordinate fare estimator
  - `GET /api/v1/weather` — Live Misamis Oriental weather proxy
  - `POST /api/v1/assistant/chat` — RiderHub AI Assistant API
- **CORS Ready:** `config/cors.php` allows local dev & `https://riderhub.online` frontend requests.
- **Standalone Frontend Folder (`frontend/`):**
  - `frontend/js/api.js` — Reusable ES Module API client (`RiderHubApi`)
  - `frontend/index.html` — Live standalone Royal Violet frontend connected to `/api/v1/*`

## Phase 2 (When Capstone Features Are 100% Finalized)
Once all features are complete, the existing Laravel files can be moved cleanly into `backend/` while keeping `frontend/` at the root:
1. Move Laravel PHP directories (`app/`, `bootstrap/`, `config/`, `database/`, `public/`, `routes/`, `storage/`, `artisan`, `composer.json`) into `backend/`.
2. Move UI assets (`resources/views`, `resources/css`, `resources/js`, `vite.config.js`, `tailwind.config.js`, `package.json`) into `frontend/`.
3. Point `backend/config/view.php` to `../frontend/views` and `frontend/vite.config.js` output to `../backend/public/build`.
4. Point Hostinger document root to `backend/public`.
