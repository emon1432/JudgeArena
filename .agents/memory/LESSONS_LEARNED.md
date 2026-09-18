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

---

## 8. CI/CD Deployment Job Decoupling

- **Rule**: When production deployment credentials (such as cPanel FTP secrets) are pending or not yet configured, decouple the deployment job using `if: false` in `.github/workflows/cpanel.yml` instead of commenting out or deleting the pipeline.
- **Benefit**: The test and quality assurance job (`test`) runs normally, maintaining full CI verification, while the `deploy` job cleanly reports as skipped, resulting in a 100% green workflow badge.

---

## 9. Standings Remote Cache Payload Optimization (Raw-Only Storage)

- **Issue**: Previously, `StandingsCacheService::serialize()` stored both the normalized DTO schema (`contest`, `problems`, `rows`) AND the full raw API response (`raw`) in the same JSON file. This caused unnecessary data duplication and inflated compressed file sizes.
- **Rule**: Remote standings cache files (`.json.gz`) MUST store ONLY the platform's raw API/crawler response payload (`$standings->raw`).
- **Dynamic Transformation**: When reading from cache via `StandingsCacheService::get()`, the raw JSON payload is dynamically mapped and transformed into `ContestStandingsDTO` using the platform's native mappers and transformers (`CodeforcesStandingsMapper`, `AtCoderStandingsMapper`) with zero data duplication.

---

## 10. Codeforces Standings-Based Problem Ingestion & cPanel Timeout Prevention

- **Domain Requirement**: Problems MUST be imported via `contest.standings` rather than `problemset.problems` because over 100 contest problems are absent from Codeforces' global problemset.
- **Root Cause of Stuck Loop**:
  - Processing 2,146 contests sequentially with a 2-second rate limit requires ~70 minutes. Running all contests in a single synchronous PHP execution causes cPanel/shared hosting to terminate the process (`SIGKILL`) after 30–60 seconds, leaving in-flight records stranded in `syncing` state.
  - Finished unrated/gym contests (e.g. 1595, 1596) returning HTTP 400 Bad Request caused repeated failures at the start of every run.
- **Architectural Solution**:
  1. **Incremental Un-Synced Batching**: `ProblemImporter` queries only un-synced contests (`whereNotIn('platform_contest_id', $syncedIds)`) in safe batches (default 30 contests), completing in ~35–40 seconds per run.
  2. **Finished Unrated Contest 400 Handling**: When a `FINISHED` contest returns HTTP 400 ("Contest not found"), it is marked `Synced` with a descriptive metadata note, preventing it from stalling subsequent runs.
  3. **Phase-Aware Retry Guarantee**: Contests in `BEFORE` or `CODING` phases are NEVER marked `Synced` on error/empty response, ensuring they automatically refresh when the contest completes.
- **Execution Limits Strategy**:
  - All console import and sync commands (`judgearena:import-*`, `judgearena:sync`) configure unlimited execution time (`set_time_limit(0)`) and 512MB memory limit (`ini_set('memory_limit', '512M')`).
  - `ProblemImporter` performs contest-scoped problem sync via `$adapter->getUserStandings($contestPlatformId)`. Contests with finished status that are already synced are skipped, allowing long-running CLI executions to progress seamlessly.

---

## 11. Google Drive Stale Folder Recovery & AtCoder Standings Deserialization

- **Google Drive 404 Cache Invalidation**:
  - In Google Drive API, subfolder IDs are cached in Redis/file cache. If folders in Google Drive are moved, recreated, or deleted externally, API requests return HTTP 404.
  - `GoogleDriveClient::put()` catches HTTP 404 on file metadata creation, automatically clears cached subfolder IDs via `clearSubfolderCache($subfolder)`, resolves fresh folder IDs, and retries the upload seamlessly.
- **AtCoder Standings Normalization Pipeline**:
  - Raw AtCoder web standings payloads (`TaskInfo`, `StandingsData`, `Fixed`) lack explicit contest timestamps.
  - `StandingsCacheService::transformRaw()` normalizes cached raw AtCoder JSON via `AtCoderResponseNormalizer::standings($raw, null, $contestId)` before passing to `AtCoderStandingsMapper`, ensuring 100% fidelity without data loss.
- **Sync Batching Invariant**:
  - Default problem import batch size is 30 contests when invoked by automated cron (`judgearena:sync` / `SyncRunnerService`), while manual CLI commands support `--all` (unlimited) and `--limit=N`.

---

## 12. Eventual Consistency & Self-Healing Ingestion Architecture

- **Problem / Race Condition**:
  - When sync jobs run asynchronously or in batches (e.g. Submissions sync before Problem sync, Standings sync before Problem sync), temporary missing database references can lead to orphaned submissions (`problem_id = null`, `contest_id = null`) or skipped `standing_task_results`.
- **Architectural Solution**:
  1. **Tier 1 (JIT Problem Ingestion in Standings)**: When `UserStandingImporter` processes a contest's standings, it calls `ensureContestProblems()`, immediately persisting any missing problems directly from the loaded `ContestStandingsDTO->problems`. `StandingTaskResult` records are **never skipped**.
  2. **Tier 2 (Problem Self-Healing Backlink)**: When `ProblemImporter` creates/updates a `Problem`, it immediately backfills and links all existing submissions where `contest_id = $contest->id` and `problem_id IS NULL`.
  3. **Tier 3 (Contest Entity Backlink)**: When `ContestImporter` creates/updates a `Contest`, it immediately backfills `contest_id` on all existing submissions where `contest_id IS NULL` and `metadata->contest_platform_id` matches.
- **Guarantee**: Regardless of ingestion order (Contest ➔ Problem ➔ Submission or Submission ➔ Contest ➔ Problem), all relationships achieve 100% relational integrity and zero data loss.

---

## 13. High-Speed Google Drive Cache-Aside & Admin Panel Standings Management

- **Datatable Latency Challenge**:
  - In `/admin/all-contests`, rendering 25–100 rows while executing real-time Google Drive API `files->listFiles()` searches for each contest causes severe latency (several seconds per page) and risks hitting Google API rate limits.
- **Solution**:
  1. **Google Drive File Cache**: `GoogleDriveClient::findFileId()` caches file search results in Laravel application cache (`gdrive:file:{subfolder}:{filename}`) for 1 hour, with a 5-minute negative cache for absent files. Caches are automatically invalidated upon `put()` and `delete()`.
  2. **Admin Panel Standings Status**: The admin contest datatable displays a real-time `Uploaded` badge or `Upload` button per row.
  3. **Single-Click Real-Time AJAX Sync**: Clicking `Upload` fires a POST to `admin.all-contests.sync-standings`, fetches from the platform API via the platform adapter, streams the gzipped payload directly to Google Drive, and updates the datatable dynamically without full page reloads.

---

## 14. Google Drive OAuth Health Monitoring & Global DataTables Filtering Engine

- **OAuth Token Expiry Visibility**:
  - In previous versions, when the Google Drive refresh token expired or was revoked (`invalid_grant`), background standings syncs and uploads failed silently, reverting to slow API fallbacks without the administrator being notified.
  - **Solution**: `GoogleDriveClient::checkConnection()` proactively checks token validity. `SyncMonitor` Livewire displays a dedicated **Drive Storage Status** card (`Connected`, `Token Expired`, `Local Disk`) with live indicators, and automatically displays a prominent alert banner at the top if the token is disconnected.
- **Batch Standings Filtering ($O(1)$ SQL Optimization)**:
  - Filtering contests by `Uploaded` vs `Missing` in `/admin/all-contests` requires knowing all uploaded files across platforms.
  - `GoogleDriveClient::listFiles()` performs single-request paginated listings and warms the file cache. `StandingsCacheService::getUploadedContestIds()` provides an in-memory array of contest IDs for instantaneous SQL `whereIn` / `whereNotIn` filtering with zero per-row network overhead.
- **Universal DataTables Filter Engine (`[data-dt-filter]`)**:
  - `resources/views/admin/layouts/includes/scripts.blade.php` automatically queries all DOM elements with `data-dt-filter`, injects their parameters into the server-side AJAX payload, and triggers real-time table reloads on change and reset without repetitive boilerplate.

---

## 15. Google Drive Cold-Start Discovery & Optimistic Cache Acceleration

- **Cold-Start Discovery Latency**:
  - Previously, `StandingsCacheService::getUploadedContestIds()` sequentially traversed platforms, resolving folder hierarchies (`Codeforces/Standings`, `AtCoder/Standings`) via individual Google Drive API calls, taking 15–25 seconds on a cold cache.
  - **Solution**:
    1. **Single-Query Folder Warmup (`warmupAllFolders`)**: Discovers and maps all subfolders across Google Drive in a single request (`mimeType = 'application/vnd.google-apps.folder'`), caching paths for 24 hours.
    2. **Multi-Parent Batch File Listing (`batchListFiles`)**: Queries standings files across all platform folder IDs in a single unified API call (`q = ('id1' in parents or 'id2' in parents) and trashed = false`).
    3. **Optimistic Cache Updates**: `StandingsCacheService::put()` and `GoogleDriveClient::put()` directly append newly uploaded contest IDs and filenames into the active cache arrays instead of flushing, eliminating post-upload reload latency and dropping DataTable response time from 15s down to < 20ms.

---

## 16. Remote Standings User Ingestion & High-Concurrency Streaming (Zero Local Storage Invariant)

- **Context & Strict Invariants**:
  - Synchronizing user standings across hundreds of contests (e.g., `tourist` with 515 Codeforces and 230 AtCoder contests) under strict constraints of **0 Bytes local disk caching** and capped PHP memory (< 128 MB) requires streaming standings directly from remote Google Drive storage (`.json.gz`).
- **Bottlenecks Identified**:
  1. *Sequential Latency*: Downloading and processing 745 contest JSON files one by one took ~3.02s per contest, requiring ~37 minutes for a single user.
  2. *Single-Row DB Cache Overhead*: Waking up and querying Google Drive file IDs sequentially or writing 6,400 cache entries to DB cache individually added 75+ seconds of latency.
  3. *Memory Exhaustion (OOM)*: A single contest standings JSON can contain up to 25,000 participant objects (~20MB uncompressed). Instantiating 25 contests in memory concurrently consumed > 1 GB RAM, causing PHP `Allowed memory size exhausted`.
- **Architectural Solution**:
  1. *Folder Warmup in 1000-page Batches*: `GoogleDriveClient::warmupFolderFiles()` fetches up to 10,000 files in paginated Google Drive batches and stores the entire key-value mapping in Laravel application cache under a single key (`gdrive:folder_files:{subfolder}`), dropping warmup time from 75s down to **0.006s**.
  2. *Concurrent Network Streaming (`Http::pool()`)*: `GoogleDriveClient::getMultiple()` and `StandingsCacheService::getMultipleBinaries()` fetch 10 compressed `.gz` files concurrently in parallel HTTP connections. 10 compressed files occupy only ~2 MB in RAM.
  3. *Sequential Stream Decode & Immediate Unset*: In `UserStandingImporter::processContestBatch()`, binary `.gz` payloads are decompressed and mapped to `ContestStandingsDTO` **one contest at a time**. The importer extracts only the tracked user (`tourist`) and their problem task results, immediately `unset($standings)` to destroy the other 24,999 participant objects from RAM, followed by bulk `Standing::upsert()` and `StandingTaskResult::upsert()`, then `gc_collect_cycles()`. Peak RAM stays strictly capped under **40 MB**.
  4. *Speedup Result*: Average time dropped from 3.02s per contest down to **~0.25s–0.35s per contest** (**10x speedup**), reducing total import time from ~37 minutes down to **~3.5 minutes**, all while preserving **0 Bytes local disk storage**.

---

## 17. Rating Change Fallback Reconciliation, Task Results Synthesis & Live Contest Filtering

- **Low-Rank Standings Omission & Task Results Synthesis**:
  - In massive contests (25,000+ competitors), Google Drive cached standings files often contain the top $N$ rows (e.g., top 10,000). A tracked user ranked outside that range would be omitted from `standings` despite having officially competed and received a rating update.
  - **Solution (Self-Healing Fallback Reconciliation & Task Synthesis)**:
    `UserStandingImporter::reconcileMissingRatedStandings()` compares `contest_rating_changes` against `standings` post-import. Any rated contest missing from `standings` is immediately synthesized using the official `rank`, `old_rating`, and `new_rating` from `contest_rating_changes` ($O(1)$ in-memory/DB operation, 0 network calls).
    Furthermore, the importer scans user submissions for that contest, filtering exclusively to live contest submissions (`CONTESTANT` within the contest start/end time window), and automatically synthesizes `StandingTaskResult` records (`AC`, `rejected_attempt_count`, `best_submission_time_seconds`, `points`, `penalty`) while calculating and updating the `Standing` point total.
- **Strict Participant Type Filtering (CONTESTANT Only)**:
  - Standings files frequently include `PRACTICE`, `VIRTUAL`, and `MANAGER` rows when users solve problemsets post-contest or compete unofficially.
  - `UserStandingImporter` strictly enforces that only `CONTESTANT` participant types are allowed for standings and task result calculations. Post-contest practice or upsolving submissions (`PRACTICE` or `submitted_at > end_time`) are completely excluded from contest standings and point totals.
- **Targeted Profile Isolation**:
  - When running targeted imports (`judgearena:import-user-standings <platform> <handle>`), the importer isolates `$profilesForBatch` to only the target user, preventing unrelated opportunistic updates during targeted debug/sync runs while retaining global opportunistic batching during full platform syncs.

---

## 18. Standing Task Results Table Bloat Prevention (Unattempted Problems)

- **Incident & Table Bloat**:
  - In Codeforces and AtCoder contest standings JSON payloads, problem result slots are returned for every single problem in the contest for each participant, even if the participant never viewed or submitted code for that problem (`points = 0`, `rejectedAttemptCount = 0`, `bestSubmissionTimeSeconds = null`).
  - Previously, `UserStandingImporter` iterated over all problem slots in `problemResults` and inserted zero-valued rows into `standing_task_results`. For a contest with 8 problems where a participant solved or attempted 2, 6 completely empty rows were inserted, causing massive database bloat (e.g., hundreds of thousands of redundant rows).
- **Rule & Solution**:
  - `UserStandingImporter::processContestBatch()` in all platform implementations (`Codeforces`, `AtCoder`) MUST strictly filter out unattempted problems before queuing rows for `StandingTaskResult::upsert()`:
    ```php
    $isAttempted = ($pResult->points !== null && (float) $pResult->points > 0.0)
        || ($pResult->rejectedAttemptCount !== null && (int) $pResult->rejectedAttemptCount > 0)
        || ($pResult->bestSubmissionTimeSeconds !== null);

    if (! $isAttempted) {
        continue;
    }
    ```
  - **AtCoder Elapsed Seconds Normalization**: `AtCoderStandingsMapper::normalizeElapsedSeconds()` returns `null` when `$elapsed <= 0` (unattempted tasks have `Elapsed: 0` in AtCoder JSON), preventing zero elapsed times from being falsely treated as live submissions.
  - **Pruning Existing Bloated Data**:
    ```sql
    DELETE FROM standing_task_results
    WHERE (points IS NULL OR points = 0)
      AND (rejected_attempt_count IS NULL OR rejected_attempt_count = 0)
      AND (best_submission_time_seconds IS NULL);
    ```
