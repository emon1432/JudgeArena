# JudgeArena — Lessons Learned & Architectural Anti-Patterns

> **Purpose**: This living document captures operational incidents, debugging discoveries, and anti-patterns encountered during development. AI Coding Assistants and human engineers MUST consult this document to avoid repeating past mistakes.

---

## 1. Zero External/Gitignored Fixtures in Tests (`docs/*` Invariant)

- **Incident**: CI tests failed on GitHub Actions because Unit tests attempted to read HTML fixtures from `docs/platforms/atcoder.jp/sample-responses/`. The `docs/` folder was listed in `.gitignore` as a developer-only scratchpad.
- **Root Cause**: Tests coupled to local developer scratch files that do not exist in fresh repository checkouts or CI runners.
- **Permanent Rule**:
  - `docs/*` is **strictly for manual research notes** and temporary developer documentation that may be deleted at any time.
  - **NEVER** link, reference, read, or import any file from `docs/*` inside `app/`, `tests/`, `config/`, or `database/`.
  - All unit and feature tests **must be 100% self-contained**, utilizing synthetic inline HTML/JSON strings or mocked HTTP responses (`Http::fake()`).

---

## 2. MySQL 8.0 `ONLY_FULL_GROUP_BY` & `DISTINCT` (Error 3065)

- **Incident**: Server-side datatable queries failed on MySQL 8.0 with:
  `SQLSTATE[HY000]: General error: 3065 Expression of ORDER BY clause is not in SELECT list, references column which is not in SELECT list; this is incompatible with DISTINCT`
- **Root Cause**: `ServerSideDatatable` executed `SELECT DISTINCT id FROM ... ORDER BY other_column`. In MySQL 8.0 with strict `ONLY_FULL_GROUP_BY`, any column in `ORDER BY` must be present in the `SELECT` list when `DISTINCT` is used.
- **Solution**:
  - `ServerSideDatatable` dynamically extracts all `ORDER BY` column names and includes them alongside the primary key in the ID-pluck query:
    `$idQuery->select(array_unique(array_merge([$key], $orderColumns)))->distinct()`.
  - The pagination count subquery selects only `datatable_row_id` to prevent overhead.

---

## 3. Datatable Search & Order Column Validation (Error 1054)

- **Incident**: Searching `/admin/all-problems` threw:
  `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'problems.difficulty' in 'where clause'`
- **Root Cause**: `ProblemController` specified `'problems.difficulty'` in the `'searchable'` array, but the physical column on `problems` table is `platform_problem_id`, while difficulty is a virtual attribute/rating.
- **Solution**:
  - Only specify **verified, physical database columns** with table qualification (e.g., `problems.platform_problem_id`, `problems.name`, `problems.code`, `problems.rating`) in `searchable` and `orderable`.
  - Virtual accessors, relations, or computed attributes must never be used in raw SQL `where` or `order by` clauses.

---

## 4. Standings Remote Cache-Aside Architecture

- **Context**: Contest standings payloads (especially for large Codeforces or AtCoder contests) can exceed tens of megabytes, creating heavy API rate-limit pressure and high memory consumption.
- **Architecture**:
  - Two-tier Cache-Aside: Google Drive (primary remote storage for large gzip JSONs) + local disk cache fallback.
  - Subfolder resolution: Dynamically resolved via `PlatformRegistry::getPlatformName($platform)` (e.g., `['Codeforces', 'Standings']`), eliminating hardcoded folder paths.
  - Test Isolation: In automated tests, always isolate storage via `Storage::fake('local')` to prevent disk collisions across test runs.

---

## 5. DTO Construction vs. Factory Mappers

- **Anti-Pattern**: Instantiating platform DTOs directly (e.g. `new AtCoderContestDTO(...)`, `new CodeforcesRanklistRowDTO(...)`) in tests or services with manual constructor arguments.
- **Problem**: Platform DTO constructors evolve with typed non-optional arguments. Direct instantiation creates fragile tests that break when DTO fields change.
- **Standard**:
  - Always construct DTOs using platform mappers:
    - `AtCoderContestMapper::fromNormalized($array)`
    - `AtCoderProblemMapper::fromNormalized($array)`
    - `AtCoderSubmissionMapper::fromNormalized($array)`
    - `AtCoderStandingsMapper::fromApiResponse($array)`
    - `CodeforcesContestMapper::fromNormalized($array)`
    - `CodeforcesStandingsMapper::fromApiResponse($array)`

---

## 6. Enum Completeness & Match Coverage

- **Incident**: Submissions with rare verdicts (e.g., `REJECTED`, `CHALLENGED`) threw `UnhandledMatchError` in Blade components calling `badgeClass()`.
- **Standard**:
  - Every `match ($this)` expression inside domain Enums (`SubmissionVerdict`, `PlatformSyncStatus`, `PlatformSyncEntityType`) must be exhaustive and include all enum cases.
  - Unit tests must loop through `::cases()` and verify that UI methods (`label()`, `badgeClass()`, `icon()`) return valid non-empty strings.

---

## 7. Platform Sync Orchestration & Entity Independence

- **Principle**: Platform sync jobs exist to track orchestration progress independently from the imported domain rows.
- **Rules**:
  - `PlatformSyncJob` uses `PlatformSyncJobEntity` enum (`Contest`, `Problem`, `User`, `UserRatingHistory`, `UserSubmissions`, `UserStandings`).
  - No artificial 50-chunk limits on batch syncing: either sync all eligible items or paginate with state tracking (`PlatformSyncStateService`).

