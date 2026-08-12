# MAINTENANCE_REFACTOR.md — Bug Fixes & Refactoring Workflows

> **Purpose for AI Agents**: This document defines the step-by-step procedural workflows for refactoring, testing, and maintaining JudgeArena. Follow these exact sequences when executing tasks.

---

## 1. Workflow: Bug Fix (Sync Failure or Scraper Breakage)

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

## 2. Workflow: Feature Development (UI / Leaderboards)

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

## 3. Workflow: Core Refactoring

Use this workflow when refactoring core service layers, queue pipelines, or DTO contracts.

### Steps:
1. **Contract Stability Check**:
   - Ensure modifications do not break `App\Core\Contracts\Platforms\PlatformAdapter`.
2. **Stepwise Refactoring**:
   - Make isolated code edits in single components (e.g. `SyncRunnerService`).
3. **Full Test Suite Execution**:
   - Run `php artisan test` after every step to detect regressions immediately.
