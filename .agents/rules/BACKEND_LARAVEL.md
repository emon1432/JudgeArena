# BACKEND_LARAVEL.md — Backend Engineering Rules

> **Purpose for AI Agents**: This document specifies strict coding standards, architectural rules, and safety constraints for JudgeArena backend code. Every code modification MUST adhere to these rules with zero exceptions.

---

## 1. Laravel & PHP Coding Standards

### Rule 1.1: Use Enums for All Statuses and Entity Types
- **Instruction**: Hardcoded strings for sync statuses, verdicts, or job entities are strictly prohibited. Use backed PHP Enums located in `App\Enums\*` (e.g., `PlatformSyncStatus`, `SyncRunStatus`, `PlatformSyncEntityType`).
- **Why**: Eliminates typo-based bugs, enables IDE autocomplete, and ensures type-safety across 10+ platform adapters.

### Rule 1.2: Enforce Strict Type Declarations
- **Instruction**: Every PHP file MUST include `declare(strict_types=1);` at the top. All class methods MUST specify parameter types and explicit return types.
- **Why**: Prevents silent type coercion errors during DTO conversions and external payload parsing.

### Rule 1.3: Controller & Livewire Component Slimness
- **Instruction**: Livewire components and HTTP Controllers MUST NOT contain business logic, web scraping routines, or multi-step database transactions. They must delegate directly to Services (`App\Services\*`) or dispatch Queue Jobs.
- **Why**: Keeps presentation decoupled from logic, allowing the same sync and aggregation logic to be triggered seamlessly from Artisan CLI commands or Web UI.

### Rule 1.4: Testing Policy Scoping (No Tests for Standard Web Routes)
- **Instruction**: Do NOT create test files for standard web routes, views, or simple controllers. Automated Pest PHP test files MUST ONLY be created when developing or updating a NEW Platform Integration (`app/Platforms/<PlatformName>/`).
- **Why**: Keeps test execution focused on high-risk platform response parsers, DTO transformations, and scrapers, preventing unnecessary test maintenance bloat for basic web views.

### Rule 1.5: Test Fixtures Location (Never Depend on `/docs`)
- **Instruction**: Automated tests requiring mock payloads, sample HTML, or API responses MUST locate their fixtures in `tests/Fixtures/Platforms/<PlatformName>/` (tracked by Git). Tests MUST NEVER reference `base_path('docs/...')`.
- **Why**: The `/docs` folder is excluded in `.gitignore` and is absent on CI environments (e.g. GitHub Actions runners), causing instant test failures in continuous integration.

---

## 2. Data Transfer Object (DTO) Rules

### Rule 2.1: Immutability
- **Instruction**: All DTO classes in `App\Core\DTOs\*` MUST be declared as `readonly class` (PHP 8.2+). Properties must be set strictly via constructor.
- **Why**: Guarantees that data fetched from external judges cannot be mutated unexpectedly while flowing through services and importers.

### Rule 2.2: Normalized Enums over Raw Strings
- **Instruction**: DTOs MUST transform platform-specific verdict strings (e.g. Codeforces `OK`, AtCoder `AC`, LeetCode `Accepted`) into standardized system verdict enums before leaving the Platform Adapter.
- **Why**: Prevents external OJ API schema changes from cascading into internal database queries or leaderboard calculations.

### Rule 2.3: Zero Framework & Database Dependencies
- **Instruction**: DTO classes MUST NOT extend Eloquent `Model`, import HTTP Request classes, or execute database queries.
- **Why**: Keeps the core abstraction layer framework-agnostic, portable, and trivial to unit test with Pest PHP.

---

## 3. Service Layer & Business Logic Rules

### Rule 3.1: Service Single Responsibility
- **Instruction**: Each service in `App\Services\*` MUST focus on one domain responsibility (e.g., `SyncRunnerService` orchestrates execution, `SyncSchedulerService` manages cooldowns/scheduling, `PlatformSyncStateService` manages checkpoints).
- **Why**: Prevents monster service classes ("God objects") and ensures high reusability across queue workers and web controllers.

### Rule 3.2: Standardized Return Contracts
- **Instruction**: Service methods MUST return typed DTOs, Enums, or standardized Result objects. Returning untyped associative arrays or raw HTTP responses is forbidden.
- **Why**: Guarantees deterministic interfaces for calling code and eliminates `undefined index` runtime notices.

### Rule 3.3: Remote Standings Caching & Dynamic Platform Resolution
- **Instruction**: When handling contest standings or large historical payloads, services and adapters MUST use `StandingsCacheService` to read/write compressed data to Google Drive via the Cache-Aside pattern. Local server storage or `storage/app` disk must NEVER be used for caching raw payloads. When resolving platform folder names for storage, ALWAYS use `PlatformRegistry::getPlatformName($platform)` (or `StandingsCacheService::platformFolder($platform)`). Hardcoded platform `match` or `switch` statements are strictly forbidden.
- **Why**: Prevents server disk exhaustion on cPanel (3GB limit), compresses payloads by ~85-90% with Gzip level 9, and ensures zero-code modifications when scaling to 100+ platforms.

---

## 4. Queue & Asynchronous Processing Rules

### Rule 4.1: Mandatory Async Scraper Ingestion
- **Instruction**: External web scraping or API synchronization MUST NEVER be executed inside an HTTP request cycle. All sync tasks MUST be dispatched as asynchronous Queue Jobs (`PlatformSyncJob`).
- **Why**: Prevents web request timeouts (HTTP 504), ensures snappy user experience, and insulates web workers from external OJ slowness.

### Rule 4.2: Idempotent Queue Execution
- **Instruction**: Every sync queue job MUST be idempotent. Executing a job multiple times with identical parameters MUST produce the exact same database state without inserting duplicate submissions or duplicating rating deltas.
- **Why**: Queue workers may retry failed jobs automatically; non-idempotent jobs cause corrupted user statistics and duplicate rows.

### Rule 4.3: Explicit Backoff & Retry Policies
- **Instruction**: Queue jobs handling external platforms MUST specify explicit retry limits and exponential backoff:
  ```php
  public int $tries = 3;
  public array $backoff = [60, 300, 900]; // 1 minute, 5 minutes, 15 minutes
  ```
- **Why**: Prevents hammering external platforms when they suffer temporary outages or Cloudflare rate limits.

### Rule 4.4: Incremental Sync Standard
- **Instruction**: All platform importers MUST implement incremental sync logic using `PlatformSyncState` checkpoints (e.g., `last_submission_id`, `pagination_offset`).
- **Why**: Re-fetching or re-processing full historical data (e.g., 5000 past submissions) during every sync cycle destroys performance and triggers rate limits. Syncs must only fetch the delta since the last checkpoint.

### Rule 4.5: Standings Single-Run Policy (No Arbitrary 50-Chunking)
- **Instruction**: Contest standings synchronization importers (`UserStandingImporter`) MUST process all un-synced contests for the user in a single, uninterrupted run. Arbitrary chunk limits (such as `MAX_CONTESTS_PER_RUN = 50`) and artificial `partial_sync` status transitions are strictly prohibited.
- **Why**: Because contest standings payloads are cached remotely in Google Drive with Gzip compression, subsequent sync runs or multi-user syncs take milliseconds and do not incur external rate-limit penalties. Artificially throttling to 50 contests creates state-machine fragmentation, partial sync loops, and poor UX.

### Rule 4.6: Importer Progress Reporting & Standardized CLI Output
- **Instruction**: All platform importer contracts and implementations MUST support an optional progress reporting callback: `?callable $onProgress = null` (e.g., `import(?callable $onProgress = null): ImportResult`). When `$onProgress` is provided, importers report live item processing status via `if ($onProgress !== null) { $onProgress($total, $current, $message); }`. All Artisan import and sync commands MUST implement the standardized JudgeArena UI banner, customized green progress bar (`━`, `❯`, elapsed timer `⏱ %elapsed:6s%`), and detailed summary table.
- **Why**: Provides transparent, real-time observability to administrators running manual or scheduled synchronizations via CLI, while maintaining 100% backward compatibility with automated background queue workers and test runners.

### Rule 4.7: State-Driven Problem Import Batching & Proactive Standings Caching
- **Instruction**: Problem importers (`ProblemImporter`) across all platforms (Codeforces, AtCoder) MUST support state-driven batching via `import(?int $limit = null, ?callable $onProgress = null): ImportResult` and query only un-synced contests (where `PlatformSyncEntityType::ContestProblems` is not `Synced` or `phase != 'FINISHED'`). Furthermore, during problem import for a contest, the importer MUST proactively invoke `getUserStandings($contestPlatformId)` to compress and cache the full contest standings in Google Drive via `StandingsCacheService`.
- **Why**: Eliminates monster 45-minute monolithic script execution, protects against rate-limits, and pre-populates Google Drive with full standings so that future user profile standings syncs can be fulfilled instantly without making external API calls.

