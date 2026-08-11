# ARCHITECTURE.md — Architectural Blueprint & Design System

> **Purpose for AI Agents**: This document defines the system layers, component boundaries, dependency rules, data flows, and extension patterns for JudgeArena. All code edits must comply with the rules established here.

---

## 1. Architectural Layers & Responsibilities

JudgeArena follows a modified **Clean Architecture / Layered Architecture** pattern separating framework delivery from core domain abstractions and concrete platform adapters.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ 1. Delivery & Presentation Layer                                            │
│    • Livewire 3 Components / Controllers (Admin\SyncMonitorController, etc.) │
│    • Artisan Commands (SyncCommand, ImportContestsCommand, etc.)            │
└───────────────────────────────────┬─────────────────────────────────────────┘
                                    │ Calls
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ 2. Application & Orchestration Layer (app/Services/)                        │
│    • SyncRunnerService (Orchestrates sync execution across adapters)        │
│    • SyncSchedulerService (Rate limits, cooldowns, scheduling)              │
│    • PlatformSyncStateService (Checkpoint management & state machine)       │
└───────────────────────────────────┬─────────────────────────────────────────┘
                                    │ Uses Contracts & DTOs
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ 3. Core Abstraction & Contract Layer (app/Core/)                            │
│    • Contracts: PlatformAdapter, ContestImporter, UserImporter, etc.        │
│    • DTOs: UserDTO, SubmissionDTO, ProblemDTO, RatingChangeDTO, etc.        │
└───────────────────────────────────▲─────────────────────────────────────────┘
                                    │ Implemented By
┌───────────────────────────────────┴─────────────────────────────────────────┐
│ 4. Platform Adapter Layer (app/Platforms/<PlatformName>/)                   │
│    • Platform Client (HTTP API or Symfony/Chrome Scraping)                 │
│    • Concrete Adapters (CodeforcesAdapter, AtCoderAdapter)                  │
│    • Importers (Codeforces\Importers\*, AtCoder\Importers\*)                 │
└───────────────────────────────────┬─────────────────────────────────────────┘
                                    │ Persists Via
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ 5. Persistence & Domain Model Layer (app/Models/)                           │
│    • Eloquent Models (User, PlatformProfile, Submission, Problem, etc.)     │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Component Layer Responsibilities

| Layer | Path | Allowed Dependencies | Prohibited Dependencies |
| :--- | :--- | :--- | :--- |
| **Delivery** | `app/Http/`, `app/Console/`, `app/Livewire/` | Services, Models, Core Contracts | Direct platform scraping / HTTP client calls |
| **Services** | `app/Services/` | Core Contracts, Core DTOs, Models | Concrete Platform Adapters directly (use Factory/Registry) |
| **Core Contracts & DTOs** | `app/Core/` | Standalone PHP / Carbon / DTOs | Controllers, Blade views, Livewire, Concrete Adapters |
| **Platform Adapters** | `app/Platforms/` | Core Contracts, Core DTOs, HTTP/Chrome Scraper | Controllers, Livewire components |
| **Persistence** | `app/Models/` | Eloquent, Database Drivers | Web Scrapers, HTTP Clients |

---

## 3. Core Data Flow & Synchronization Lifecycle

### A. Ingestion & Sync Flow

1. **Trigger**: An Artisan Command (`SyncCommand`) or Scheduled Task triggers `SyncRunnerService::run()`.
2. **Scheduling Check**: `SyncSchedulerService` checks cooldowns and platform rate limits against `config/platforms.php`.
3. **Adapter Resolution**: The adapter class registered in `config/platforms.php` (e.g. `CodeforcesAdapter`) is instantiated.
4. **Data Ingestion**:
   - The Adapter fetches raw data from external APIs or parses HTML using `symfony/dom-crawler` or `chrome-php/chrome`.
   - Raw JSON/HTML payloads are converted immediately into standardized `App\Core\DTOs\*` (e.g. `UserDTO`, `SubmissionDTO`).
5. **Importer Execution**:
   - The Adapter delegates persistence to entity importers (e.g. `userSubmissionImporter()`).
   - Importers compare DTO data with `App\Models\Submission` records using `PlatformSyncState` to avoid duplicate insertion.
6. **State Update**: `PlatformSyncStateService` updates sync status, last submission ID, and checkpoint timestamps.

---

## 4. Platform Adapter Lifecycle & Contract Rules

All platform integrations MUST implement `App\Core\Contracts\Platforms\PlatformAdapter`.

```
                     ┌──────────────────────────┐
                     │ PlatformAdapterInterface │
                     └────────────┬─────────────┘
                                  │
      ┌───────────────────────────┴───────────────────────────┐
      ▼                                                       ▼
Fetch Methods (Getters)                               Importer Factory Methods
• getUser(string $handle): UserDTO                    • userImporter(): UserImporter
• getUserSubmissions(array $params): array            • userSubmissionImporter(): UserSubmissionImporter
• getContests(): ContestDTO[]                         • problemImporter(): ProblemImporter
• getContestProblems(id): ProblemDTO[]                • contestImporter(): ContestImporter
• getUserRatingHistory(handle): array                 • ratingChangeImporter(): RatingChangeImporter
```

### Lifecycle Rules for Adapters
1. **Purity of DTO Output**: Getter methods (`getUser`, `getUserSubmissions`) MUST return standard `App\Core\DTOs\*` instances or arrays of DTOs. They must NEVER return raw HTTP responses or vendor arrays.
2. **Importers Decoupling**: Database insertion logic MUST be encapsulated in Importer classes located under `app/Platforms/<PlatformName>/Importers/`.
3. **Graceful Handling of Platform Faults**:
   - HTTP 429 (Too Many Requests) -> Throw custom rate-limit exception caught by `SyncRunnerService` to mark cooldown.
   - HTTP 404 / Invalid Handle -> Return null or handle-not-found DTO status.
   - Structural HTML change -> Log exception context to `ApplicationLog` without throwing unhandled 500 errors.

---

## 5. Unified Storage Model

- **Single Source of Truth:** `Submissions` and `RatingChanges` are stored in singular, unified tables. There is no separation between "Global Submissions" and "User Submissions".
- **Context via Relations:** The context (whether a submission belongs to a registered user or a global leaderboard) is determined by relation to a `PlatformProfile`, which may or may not be linked to a local `User`.

---

## 6. DTO Lifecycle & Immutability Rules

Data Transfer Objects (`App\Core\DTOs\*`) represent clean, normalized domain data across all Online Judges.

- **Immutability**: DTO properties MUST be read-only / immutable once constructed.
- **Normalization**: Verdict string mapping happens inside the Adapter before constructing a `SubmissionDTO`:
  - Codeforces `OK` / AtCoder `AC` / LeetCode `Accepted` ➔ Standardized Verdict Enum `AC`.
  - Codeforces `WRONG_ANSWER` / AtCoder `WA` ➔ Standardized Verdict Enum `WA`.
- **Zero Framework Bloat**: DTOs MUST NOT extend Eloquent `Model` or depend on HTTP request contexts.

---

## 7. Dependency Rules & Enforcement

1. **Inner Layer Independence**: `app/Core/` MUST NOT depend on `app/Platforms/`, `app/Http/`, or `app/Services/`.
2. **No Direct Instantiation**: Services must resolve Platform Adapters via `config/platforms.php` or a dedicated Platform Manager/Factory.
3. **Database Boundaries**: Web controllers MUST NOT execute database migrations, raw scraper invocations, or external network requests.

---

## 8. Platform Extension Guidelines

To add a new Online Judge platform (e.g. `LeetCode`):

1. **Directory Setup**: Create `app/Platforms/LeetCode/` and `app/Platforms/LeetCode/Importers/`.
2. **Adapter Creation**: Implement `App\Core\Contracts\Platforms\PlatformAdapter` in `app/Platforms/LeetCode/LeetCodeAdapter.php`.
3. **Importer Creation**: Implement Importer interfaces (`UserImporter`, `UserSubmissionImporter`, etc.) inside `app/Platforms/LeetCode/Importers/`.
4. **Configuration Registration**: Register metadata and adapter reference in `config/platforms.php`:
   ```php
   'leetcode' => [
       'base_url' => 'https://leetcode.com/',
       'adapter' => \App\Platforms\LeetCode\LeetCodeAdapter::class,
       'status' => 'Active',
   ],
   ```
5. **Testing Contract**: Create Pest PHP feature test in `tests/Feature/Platforms/LeetCodeTest.php` ensuring DTO transformation and Importer behavior pass standard test suites.
