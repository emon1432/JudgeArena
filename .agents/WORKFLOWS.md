# WORKFLOWS.md — Standard Operating Workflows

> **Purpose for AI Agents**: This document defines the step-by-step procedural workflows for developing, testing, refactoring, and maintaining JudgeArena. Follow these exact sequences when executing tasks.

---

## 1. Workflow: New Platform Integration

Use this workflow whenever adding support for a new Online Judge (e.g. LeetCode, CodeChef, SPOJ).

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ 1. Research &   │───>│ 2. Directory &  │───>│ 3. Implement    │
│    Discovery    │    │    Skeleton     │    │    Adapter      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                                       │
┌─────────────────┐    ┌─────────────────┐             │
│ 6. Config       │<───│ 5. Normalization│<────────────┘
│    Registration │    │    & Importers  │
└────────┬────────┘    └─────────────────┘
         │
         ▼
┌─────────────────┐    ┌─────────────────┐
│ 7. Pest Testing │───>│ 8. Sync Test    │
│    Execution    │    │    Verification │
└─────────────────┘    └─────────────────┘
```

### Steps:
1. **Research & Discovery**:
   - Determine whether the platform provides a public REST API or requires web scraping.
   - If HTML scraping is required, identify whether static parsing (`symfony/dom-crawler`) or JavaScript rendering (`chrome-php/chrome`) is necessary.
   - Document base URLs, profile URL structures, pagination mechanisms, and rate limits.
2. **Directory & Skeleton Creation**:
   - Create `app/Platforms/<PlatformName>/` directory.
   - Create `app/Platforms/<PlatformName>/Importers/` sub-directory.
3. **Implement Platform Adapter**:
   - Create `app/Platforms/<PlatformName>/<PlatformName>Adapter.php` implementing `App\Core\Contracts\Platforms\PlatformAdapter`.
   - Stub all getter methods (`getUser`, `getUserSubmissions`, `getContests`, etc.) and importer factory methods.
4. **Implement Entity Importers**:
   - Create entity-specific importers in `app/Platforms/<PlatformName>/Importers/` (`UserImporter`, `UserSubmissionImporter`, `ProblemImporter`, `ContestImporter`, etc.).
5. **DTO Mapping & Verdict Normalization**:
   - Ensure all adapter getter methods map raw platform data into standardized `App\Core\DTOs\*` objects.
   - Map platform-specific verdict strings into standard verdict enums.
6. **Config Registration**:
   - Register platform metadata, base URL, rate limit cooldowns, and adapter class mapping in `config/platforms.php`.
7. **Pest Testing**:
   - Write unit and feature tests in `tests/Feature/Platforms/<PlatformName>Test.php` using mock HTML/JSON fixtures.
8. **Verification Command Execution**:
   - Execute test sync command (e.g. `php artisan test:platform {handle} --sync` or `php artisan test`) to verify end-to-end ingestion into MySQL.

---

## 2. Workflow: Scraper & API Integration Development

Use this workflow when building or updating web scrapers or API client interfaces.

### Steps:
1. **Payload & DOM Analysis**:
   - Capture sample JSON API responses or save raw target HTML pages into fixture files.
   - Identify CSS selectors or JSON paths for key fields (verdict, language, submission time, problem ID).
2. **Client Isolation**:
   - Implement HTTP or Chrome Client handling network retries, timeout limits, and Cloudflare challenge detection.
3. **Rate Limit & Delay Configuration**:
   - Configure per-request delays in `config/platforms.php` to prevent HTTP 429 (Too Many Requests) or IP bans.
4. **Memory Management Audit**:
   - Verify that paginated scraping loops explicitly release DOM crawler memory references (`unset($crawler)`).
5. **Fixture-Based Parser Validation**:
   - Create Pest unit tests asserting that raw fixture HTML/JSON is accurately converted to valid DTOs.

---

## 3. Workflow: Bug Fix (Sync Failure or Scraper Breakage)

Use this workflow when an existing platform adapter fails or throws errors during queue sync jobs.

### Steps:
1. **Log & Traceback Inspection**:
   - Inspect failure logs in `PlatformSyncJob`, `ApplicationLog`, or `storage/logs/laravel.log`.
   - Identify the exact line number and failing response payload.
2. **Failure Categorization**:
   - **Category A (External OJ HTML Change)**: Update CSS selectors in target Platform Adapter/Client.
   - **Category B (Rate Limit / IP Ban)**: Increase cooldown duration in `config/platforms.php`.
   - **Category C (Unmapped Verdict/Data)**: Update DTO parser to handle new verdict string.
3. **Reproduce via Pest Test**:
   - Add a failing test case in `tests/` using the problematic response payload.
4. **Apply Targeted Fix**:
   - Modify only the affected Adapter, Client, or Importer class without changing public contracts.
5. **Regression Run**:
   - Run `php artisan test` to confirm the bug is resolved and no other platform integration was broken.

---

## 4. Workflow: Feature Development (UI / Leaderboards)

Use this workflow when introducing new platform metrics, user features, or leaderboard views.

### Steps:
1. **Schema & Model Definition**:
   - Create database migration and update relevant Eloquent models (`App\Models\*`).
2. **Service Layer Implementation**:
   - Implement business logic, metric aggregation, or standing calculations inside `App\Services\*`.
3. **Presentation Layer Construction**:
   - Create or update Livewire components / Blade views using Tailwind CSS.
4. **Automated & Manual Verification**:
   - Write feature tests asserting database state and UI component rendering.
   - Verify Livewire interaction in local dev server (`composer run dev`).

---

## 5. Workflow: Core Refactoring

Use this workflow when refactoring core service layers, queue pipelines, or DTO contracts.

### Steps:
1. **Contract Stability Check**:
   - Ensure modifications do not break `App\Core\Contracts\Platforms\PlatformAdapter`.
2. **Stepwise Refactoring**:
   - Make isolated code edits in single components (e.g. `SyncRunnerService`).
3. **Full Test Suite Execution**:
   - Run `php artisan test` after every step to detect regressions immediately.
