# JudgeArena Project Structure Blueprint

Version: 1.0  
Date: 2026-03-03

## Goal

Define a stable, scalable project structure before feature coding, with **MVP support without cron/queue jobs**.

Product intent:
- Bring problems and contests from multiple competitive programming platforms into one place.
- Reduce context switching for users who currently visit many sites separately.
- Prepare the foundation for a future integrated code editor + submission pipeline.

This blueprint is designed around 4 platform data domains:
1. User data
2. User submission data (most required)
3. Problem catalog data (optional)
4. Contest data (optional)

---

## Why Aggregate Problems and Contests

Users currently need to browse multiple platforms to discover problems and track contests.
JudgeArena aims to become a unified discovery and participation hub by listing:
- Problems from all supported platforms in one searchable catalog
- Contests (past, ongoing, upcoming) in one unified timeline

This architecture decision also supports a future execution layer:
- Integrated code editor in JudgeArena
- Platform API-based submission flow (where official APIs permit)
- Unified result tracking for submissions across platforms

Because of this roadmap, `platform_problems` and `contests` are strategic core datasets,
not only optional convenience features.

---

## Architecture Principles

- Keep platform-specific parsing isolated from core business logic.
- Keep normalized storage independent from platform raw payloads.
- Make sync executable synchronously (HTTP request) for MVP.
- Keep async background execution as an optional upgrade path.
- Add one canonical mapping layer so all platforms produce the same internal model.
- Separate global discovery data from user-owned progress data.

---

## Recommended Folder Structure

```text
app/
  Contracts/
    Platforms/
      PlatformAdapter.php

  DataTransferObjects/
    Platform/
      ProfileDTO.php
      SubmissionDTO.php
      ContestDTO.php                # add
      ProblemDTO.php                # add
      SyncResultDTO.php             # add

  Actions/
    PlatformSync/
      SyncPlatformProfileAction.php # move existing action gradually
      SyncPlatformSubmissionsAction.php
      SyncPlatformContestsAction.php
      SyncPlatformProblemsAction.php

  Services/
    PlatformSync/
      SyncOrchestrator.php          # central no-job / with-job strategy
      SyncModeResolver.php          # decides sync mode: sync/after-response/queue
      SubmissionAggregator.php      # dedupe accepted solves
      PlatformCapabilityRegistry.php
      PlatformCapability.php        # value object for support matrix

  Repositories/
    Global/
      ProblemRepository.php
      ContestRepository.php
    User/
      PlatformProfileRepository.php
      SubmissionRepository.php
      UserContestParticipationRepository.php

  Domain/
    Platform/
      Profile/
      Submission/
      Problem/
      Contest/

  Models/
    PlatformProfile.php
    PlatformSubmission.php          # add
    PlatformContest.php             # add
    PlatformProblem.php             # add
    PlatformSyncState.php           # add
    SyncLog.php

  Platforms/
    Codeforces/
      CodeforcesAdapter.php
      CodeforcesClient.php
      Mappers/
        CodeforcesProfileMapper.php
        CodeforcesSubmissionMapper.php
        CodeforcesContestMapper.php
        CodeforcesProblemMapper.php
    ...same per platform

  Http/
    Controllers/
      User/
        SyncController.php

database/
  migrations/
    *_create_platform_submissions_table.php
    *_create_contests_table.php
    *_create_user_contest_participations_table.php
    *_create_platform_problems_table.php
    *_create_platform_sync_states_table.php

docs/
  PLATFORM_CORE_DOCUMENTATION.md
  PROJECT_STRUCTURE_BLUEPRINT.md
  PLATFORM_FIELD_MAPPING.md         # add later
```

---

## Domain Ownership (Your 4 Data Requirements)

Domain separation:
- Global Discovery Domain (non-user specific): `platform_problems`, `contests`
- User Data Domain (user/profile specific): `platform_profiles`, `platform_submissions`, user contest participation

## 1) User Data

Source:
- `PlatformAdapter::fetchProfile()`

Storage:
- `platform_profiles` (existing)
- `raw_profile` JSON keeps platform-native profile payload

Required normalized fields:
- `name`
- `avatar_url`
- `joined_at`
- `country`
- `institute`
- `ranking`
- `rating`
- `total_solved`
- `captured_at`
- `raw_profile`

## 2) User Submission Data (Most Required)

Source:
- `PlatformAdapter::fetchSubmissions()`

Storage:
- `platform_submissions` (new)

Required normalized fields:
- `submission_id` (nullable if unavailable)
- `problem_id`
- `problem_name`
- `problem_url`
- `difficulty`
- `tags` (json)
- `language`
- `verdict`
- `submitted_at`
- `raw_submission`

Idempotency rules:
- Unique key: (`platform_profile_id`, `submission_id`) when `submission_id` is present
- Fallback unique key: (`platform_profile_id`, `problem_id`, `submitted_at`) when `submission_id` is null

## 3) All Problems Data (Optional)

Source:
- Platform-specific catalog endpoints/scraping

Storage:
- `platform_problems` (new)

Required normalized fields:
- `problem_id`
- `problem_name`
- `problem_url`
- `difficulty`
- `tags` (json)
- `submissions_count`
- `accepted_count`
- `acceptance_rate`
- `raw_problem`

## 4) Contest Data (Optional but good to have)

Source:
- Platform contest/rating history endpoints/pages

Storage:
- `contests` (new, global)
- `user_contest_participations` (new, user-specific)

Required normalized fields:
- `contest_id`
- `contest_name`
- `contest_url`
- `contest_date`
- `duration_seconds`
- `status` (`past`, `ongoing`, `upcoming`)
- `raw_contest`

User contest participation fields:
- `platform_profile_id`
- `contest_id`
- `user_rank`
- `rating_change`
- `performance` (score/percentile)
- `raw_contest_participation`

Contest coverage scope:
- Past contests
- Ongoing contests
- Upcoming contests

Note:
- Store contests in a unified structure with a `status`/`phase` field (`past`, `ongoing`, `upcoming`).
- For upcoming contests where user metrics are not available yet, keep `user_rank`, `rating_change`, and `performance` as `null`.

---

## No-Job MVP Sync Design

Use a single orchestrator with mode-aware execution:

- `sync` mode (default MVP): run immediately inside request.
- `after_response` mode (optional): dispatch lightweight post-response callbacks.
- `queue` mode (future): dispatch queue jobs when needed.

Suggested config shape:

```php
// config/platforms.php
'sync' => [
    'mode' => env('PLATFORM_SYNC_MODE', 'sync'), // sync | after_response | queue
    'cooldown_minutes' => env('SYNC_COOLDOWN_MINUTES', 120),
],
```

This keeps one code path and only changes execution strategy.

Recommended orchestration sequence:
- `syncProfile()`
- `syncSubmissions()`
- `syncContests()`
- `syncProblems()`

Each stage should be independently retryable and failure-isolated.

---

## Platform Capability Model

Avoid hardcoded feature checks spread across adapters/services.

Use a capability object via registry:

```php
$capability = PlatformCapabilityRegistry::for($platform);

$capability->supportsSubmissions();
$capability->supportsContestHistory();
$capability->supportsUpcomingContests();
$capability->supportsProblemCatalog();
$capability->submissionAccessMode(); // api | scrape | limited | none
```

This enables support for partial/limited/rate-limited platform behavior without changing core flow.

---

## Sync State Model (Critical)

Use `platform_sync_states` to make sync incremental and resilient.

Minimum fields:
- `platform_profile_id`
- `last_submission_cursor`
- `last_problem_cursor`
- `last_contest_cursor`
- `last_full_sync_at`
- `last_status`
- `last_error`

Purpose:
- Resume long-running syncs safely
- Prevent re-fetching entire history for power users
- Reduce timeouts and API pressure

---

## Naming and Mapping Conventions

- Always store both:
  - normalized keys (for app queries)
  - typed raw payload columns (`raw_profile`, `raw_submission`, `raw_problem`, `raw_contest`)
- Use stable IDs where possible:
  - `platform_user_id`, `problem_id`, `submission_id`, `contest_id`
- For solved count consistency:
  - count first `Accepted` per unique `problem_id` per user/platform

---

## Rollout Plan (Safe, Incremental)

Phase 1 (now):
- Keep existing `PlatformAdapter` and `SyncPlatformProfileAction`
- Introduce structure + new tables for submissions first

Phase 2:
- Add contest and problems tables + mappers
- Move platform-specific extraction into `Mappers/`

Phase 3:
- Introduce `SyncOrchestrator` and `PLATFORM_SYNC_MODE`
- Keep queue job path as optional, not mandatory

---

## What "Done" Means Per Platform

A platform is considered done when it provides:
- Profile payload with ranking/rating/total solved (or explicit nulls)
- Submission rows with verdict + timestamps
- Correct solved-count derivation from submissions (or documented fallback)
- Sync metadata (`last_synced_at`, error logging, duration)

Optional completion:
- Contest listing (past/ongoing/upcoming) and problem catalog

Production completion:
- Capability registry entry with explicit support matrix
- Sync state cursoring for submissions/contests/problems
- Idempotent write guarantees for submission ingestion

---

## Notes for Current Codebase

- Existing implementation already matches core adapter idea.
- `SyncPlatformProfileJob` can remain in codebase but should be optional via sync mode.
- Start by stabilizing submissions storage and mapping quality; this yields the highest product value first.
