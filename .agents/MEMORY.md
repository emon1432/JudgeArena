# MEMORY.md — Persistent Architectural Invariants & Memory

> **Purpose for AI Agents**: This document captures the permanent architectural invariants, non-negotiable rules, technical decisions, and design philosophy of JudgeArena. These decisions are intended to remain true for years across all AI coding assistants and environment switches.

---

## 1. Permanent Architectural Invariants (System Axioms)

### Axiom 1: JudgeArena is an Aggregator, NOT an Execution Engine
JudgeArena MUST NEVER execute user code, compile source files, or host code sandboxes (e.g. Isolate, Judge0). It is strictly an aggregation, statistic sync, and leaderboard platform for external Online Judges (OJs).

### Axiom 2: Zero Synchronous External IO in HTTP Request Cycles
No HTTP Controller, Livewire Component, or Blade view render cycle is EVER permitted to make synchronous external HTTP requests, web scraping calls, or API calls to external judges. All synchronization MUST be dispatched asynchronously to Laravel Queue Workers (`PlatformSyncJob`).

### Axiom 3: Complete Encapsulation via Adapter Protocol
All interactions with external Online Judges MUST be encapsulated within concrete implementations of `App\Core\Contracts\Platforms\PlatformAdapter`. Models, services, and controllers MUST NEVER issue raw cURL, Guzzle, or Symfony Crawler calls directly to an external judge URL.

---

## 2. Non-Negotiable Engineering Rules

1. **Strict Data Boundary via DTOs**:
   Raw API JSON payloads or scraped HTML elements MUST BE parsed and mapped into immutable Data Transfer Objects (`App\Core\DTOs\*`) immediately upon fetch. Raw external vendor structures MUST NOT leak into Eloquent models or service methods.

2. **Guaranteed Sync Idempotency**:
   Synchronization logic MUST be strictly idempotent. A submission is uniquely identified by `(platform_profile_id, external_submission_id)`. Re-running a sync job 1 time or 100 times MUST produce the exact same database state without duplicate submissions or corrupted stats.

3. **External OJ Fault Isolation**:
   External platform outages, structural HTML changes, HTTP 429 rate limits, or Cloudflare challenges MUST NOT cause application-wide 500 errors or interrupt sync operations for other platforms. Failures MUST be recorded in `PlatformSyncJob` / `PlatformSyncState` and fail gracefully.

---

## 3. Foundation Technical Decisions & Rationale

| Decision Area | Technical Choice | Strategic Rationale |
| :--- | :--- | :--- |
| **Backend & UI** | Laravel 12 + Livewire 3 + Jetstream | Single monolithic repository delivering reactive UI without SPA frontend build complexity or API duplication. |
| **Scraping Engine** | `symfony/dom-crawler` + `chrome-php/chrome` | Dual-approach allowing fast static HTML parsing for simple pages and headless Chrome rendering for JavaScript-rendered judges. |
| **State Machine** | `PlatformSyncState` checkpoints | Explicit state tracking per profile/platform prevents full historical re-crawling and enables paginated delta syncs. |
| **Testing Standard** | Pest PHP | Expressive, readable unit testing framework for validating DTO parsers, verdict mappers, and adapter contracts. |
| **Frontend Scripting** | `@push('scripts')` + `@include('web.pages.<feature>.scripts')` | Page-specific JS stored in Blade script partials allows direct access to Blade routes (`route()`), CSRF, and config without polluting `public/` or hardcoding URLs. |
| **Data Lists & UI** | **Universal Infinite Scrolling (No Pagination)** | Traditional numbered pagination is strictly forbidden across the platform. Due to massive datasets (lakhs of submissions, problems, standings), seamless Infinite Loading / Scroll Loading via IntersectionObserver and Server-Side Cursor/Simple queries guarantees superior user experience and speed. |

---

## 4. System Design Philosophy

- **Resilience over Scraping Velocity**: Respecting external judge rate limits and preventing IP bans is prioritized above aggressive polling speed.
- **Convention over Flexibility**: All platform adapters MUST strictly follow the prescribed file structure (`app/Platforms/<PlatformName>/Importers/`). Ad-hoc file placement is forbidden.
- **Normalized Internal Domain Language**: Internal domain entities (`Submission`, `Problem`, `Standing`) communicate exclusively using JudgeArena's normalized enums and schemas, insulating the domain from vendor-specific terminology.
