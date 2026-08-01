# RULES.md — Engineering Rules & Quality Standards

> **Purpose for AI Agents**: This document specifies strict, non-negotiable coding standards, architectural rules, and safety constraints for JudgeArena. Every code modification MUST adhere to these rules with zero exceptions.

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

---

## 5. Database & Ingestion Performance Rules

### Rule 5.1: Bulk Database Operations in Importers
- **Instruction**: When persisting batches of submissions or rating histories in Importers (`App\Platforms\<Platform>\Importers\*`), NEVER execute Eloquent `save()` inside a loop. Use `upsert()`, `insertOrIgnore()`, or `DB::transaction()`.
- **Why**: Inserting 5,000 historical submissions individually causes 5,000 database round-trips; bulk upsert completes in a single query.

### Rule 5.2: Composite Index Requirements
- **Instruction**: Any query filtering submissions by user handle and platform MUST utilize composite indexes. Table migrations MUST enforce unique composite keys on `(platform_profile_id, external_submission_id)`.
- **Why**: Guarantees fast lookup speeds for sync state checks even as the `submissions` table grows to millions of records.

---

## 6. Security & Credential Safeguards

### Rule 6.1: Strict Environment Configuration
- **Instruction**: Platform credentials, session cookies (e.g. `ATCODER_SESSION_COOKIES`), and API keys MUST be loaded strictly via `config/platforms.php` sourced from `.env`. NEVER hardcode credentials in adapter files.
- **Why**: Prevents credential leakage in Git repositories and enables separate staging/production configurations.

### Rule 6.2: Sanitization of Scraper Input Parameters
- **Instruction**: User handles and problem IDs passed to headless browser tools (`chrome-php/chrome`) or DOM crawlers MUST be sanitized to prevent shell parameter injection or XSS.
- **Why**: Protects the server environment from Remote Code Execution (RCE) when executing headless browser commands with malicious user inputs.

---

## 7. Platform Scraper & Memory Safety Rules

### Rule 7.1: Strict Rate Limiting Compliance
- **Instruction**: Platform Adapters MUST respect the `rate_limit` and `cooldown` settings configured in `config/platforms.php`. Loop scraping MUST include throttling delays.
- **Why**: Prevents external Online Judges from IP-banning JudgeArena servers or triggering bot protection.

### Rule 7.2: Crawler Memory Management
- **Instruction**: When processing paginated HTML responses in scraping loops, memory references to `Crawler` or DOM instances MUST be cleared explicitly (`unset($crawler)` or garbage collection triggers).
- **Why**: Prevents PHP process memory exhaustion in long-running queue workers (`php artisan queue:listen`).
