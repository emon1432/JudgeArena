# JudgeArena

JudgeArena is a competitive programming profile aggregation platform. It connects user handles from multiple online judges, syncs profile/submission statistics, and builds a unified leaderboard.

This repository currently contains a Laravel-based implementation, with platform integrations organized under `app/Platforms`.

## Core Highlights

- Multi-platform profile linking per user.
- Background sync jobs for platform profiles.
- Unified metrics (rating, solved count, raw platform payload).
- Leaderboard based on aggregated user performance.
- Platform adapter architecture for extensibility.

## Supported Platforms

- Codeforces
- LeetCode
- AtCoder
- CodeChef
- HackerRank
- HackerEarth
- SPOJ
- UVA
- Timus

## Tech Stack (Current)

- Backend: Laravel (PHP)
- Database: MySQL-compatible relational database
- Queue: Laravel queue workers
- Frontend assets: Vite / Node.js toolchain

## Project Structure (Important Areas)

- `app/Platforms` — platform-specific clients and adapters
- `app/Contracts/Platforms` — adapter interface
- `app/Actions/SyncPlatformProfileAction.php` — core sync orchestration logic
- `app/Jobs/SyncPlatformProfileJob.php` — queue job for per-profile sync
- `config/platforms.php` — platform capabilities and sync config
- `docs/PLATFORM_CORE_DOCUMENTATION.md` — detailed platform implementation documentation

## Local Setup

### 1) Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL (or compatible)

### 2) Install Dependencies

```bash
composer install
npm install
```

### 3) Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update database/mail/queue variables in `.env`.

### 4) Database Setup

```bash
php artisan migrate:fresh --seed
```

### 5) Run Application

Use separate terminals:

```bash
php artisan serve
```

```bash
npm run dev
```

```bash
php artisan queue:work
```

## Sync Flow (High Level)

1. User triggers sync.
2. System validates cooldown and active linked profiles.
3. One queue job is dispatched per active platform profile.
4. Adapter fetches profile/submission data.
5. Profile metrics are updated and sync logs are recorded.

## Platform Development

To add a new online judge integration:

1. Implement the adapter contract in `app/Contracts/Platforms/PlatformAdapter.php`.
2. Add a platform client + adapter in `app/Platforms/<PlatformName>/`.
3. Register/route the adapter in sync dispatch logic.
4. Add platform metadata in `config/platforms.php`.

## Testing

Platform-specific command examples (if available):

```bash
php artisan test:spoj {handle} --sync
php artisan test:codeforces {handle} --sync
```

## Documentation

- Platform core and per-platform fetch details: `docs/PLATFORM_CORE_DOCUMENTATION.md`

## Scope Note

Current documentation in this repository focuses on core platform functionality (profile linking, sync pipeline, adapter/client behavior, and aggregation logic).
