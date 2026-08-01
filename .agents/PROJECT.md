# PROJECT.md — JudgeArena Project Definition & Domain Boundaries

> **Purpose for AI Agents**: This document defines the identity, scope, domain boundaries, tech stack, and non-goals of JudgeArena. Use this document to ground architectural decisions and maintain domain boundaries.

---

## 1. Executive Summary

- **Project Name**: JudgeArena
- **Repository**: `emon1432/JudgeArena`
- **Core Mission**: A centralized Competitive Programming (CP) profile aggregation and leaderboard platform built with Laravel. It connects user handles across multiple Online Judges (OJs), asynchronously synchronizes profile statistics and submission histories, and computes unified, cross-platform leaderboards.

---

## 2. Core Domain Entities & Ubiquitous Language

| Domain Entity | Class / Model | Description |
| :--- | :--- | :--- |
| **User** | `App\Models\User` | Registered system user who links one or more Online Judge handles. |
| **Platform** | `App\Models\Platform` | An external Online Judge (e.g., Codeforces, AtCoder, LeetCode, CodeChef, SPOJ). |
| **PlatformProfile** | `App\Models\PlatformProfile` | Association between a `User` and a `Platform`, holding linked handle, ratings, solved counts, and raw payload cache. |
| **PlatformSyncJob** | `App\Models\PlatformSyncJob` | Record of sync job executions, tracking status, execution time, and error logs. |
| **PlatformSyncState**| `App\Models\PlatformSyncState` | Fine-grained state machine tracking sync checkpoints per platform profile (e.g. pagination offsets, last submission ID). |
| **Problem** | `App\Models\Problem` | Normalized problem metadata across online judges (title, platform-assigned ID, tags, difficulty rating). |
| **Submission** | `App\Models\Submission` | Individual submission record (verdict, language, execution time, memory used, timestamp). |
| **Contest** | `App\Models\Contest` | External or internal competitive programming contest metadata. |
| **ContestRatingChange**| `App\Models\ContestRatingChange` | Per-contest rating deltas and rank history for a platform profile. |
| **Standing** | `App\Models\Standing` | Aggregated rank and score entries for leaderboards. |
| **Institute / Country**| `App\Models\Institute`, `App\Models\Country` | Demographic metadata for institutional and national leaderboards. |

---

## 3. Technology Stack & Key Dependencies

- **Framework**: Laravel 12.x (PHP 8.2+)
- **Frontend Layer**: Livewire 3, Laravel Jetstream, Blade, Tailwind CSS, Vite
- **Data Scraping & Ingestion**:
  - `symfony/dom-crawler` (CSS/HTML parsing)
  - `symfony/css-selector`
  - `chrome-php/chrome` (Headless browser execution for JavaScript-rendered sites)
  - Laravel HTTP Client / Guzzle (REST API consumption)
- **Database Layer**: MySQL / MariaDB (Relational Eloquent models)
- **Asynchronous Execution**: Laravel Queue Workers (`php artisan queue:listen`)
- **Testing Standard**: Pest PHP (`php artisan test`)
- **Utility / Aux Packages**: `appslabke/lara-izitoast`, `orangehill/iseed`

---

## 4. Scope & Boundary Definitions

### ✅ In-Scope (What JudgeArena Does)
1. **Multi-Platform Profile Linking**: Associating multiple OJ handles with a single user account.
2. **Asynchronous Stats & History Ingestion**: Fetching profile ratings, solved problem count, submission history, and contest histories via API or headless scraping.
3. **Normalized Submissions & Metrics**: Mapping platform-specific submission verdicts (e.g., `OK`, `Accepted`, `AC`, `WA`) into standardized system enums.
4. **Leaderboard & Ranking Engine**: Computing overall, institutional, and regional rankings based on aggregated ratings and solved metrics.
5. **Sync Queue Management & Rate Control**: Managing per-platform cooldowns, retry limits, and job dispatching to prevent IP bans or API rate limit violations.

### ❌ Non-Goals (What JudgeArena Does NOT Do)
1. **Code Execution / Sandboxing**: JudgeArena is NOT an Online Judge engine; it does NOT execute code (e.g., no Isolate, Judge0, or custom execution sandbox).
2. **Problem Creation & Host Contests**: JudgeArena does NOT host original code execution contests or problem submission forms.
3. **Real-time Immediate Scraping on Request**: Scraping is never executed synchronously inside HTTP request cycles; all sync operations MUST go through background Queue Workers.

---

## 5. System Architectural Constraints

1. **Strict Non-Blocking HTTP Requests**: Web requests (controllers, Livewire actions) must never perform web scraping or external API calls directly. They MUST dispatch queue jobs (`PlatformSyncJob`).
2. **Adapter-Driven Ingestion**: Direct API/scraping calls inside models or controllers are strictly forbidden. All platform interaction MUST pass through `App\Core\Contracts\PlatformAdapterInterface` or concrete adapters in `App\Platforms\`.
3. **Resilience to External Downtime**: External OJ outages, rate-limits, or structural HTML changes must NOT break application stability. Errors must be captured in `PlatformSyncJob` / `ApplicationLog` and return structured failure results.

---

## 6. Known Unknowns & Roadmap TODOs

- [ ] **TODO: Sync State Refactoring**: Replace heuristic `problems_count` sync checks with explicit checkpoint tracking via `PlatformSyncState` (as noted in `docs/Todo.txt`).
- [ ] **TODO: Platform Adapter Expansion**: Currently, active concrete adapters exist for Codeforces and AtCoder; additional adapters (LeetCode, CodeChef, SPOJ, HackerRank, etc.) are planned for standardization under `App\Platforms\`.
