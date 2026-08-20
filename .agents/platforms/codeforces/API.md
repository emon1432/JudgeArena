# CODEFORCES API & IMPORTERS — Master Architectural Reference

> **Purpose for AI Agents**: This document captures the complete API specifications, rate limits, request signature algorithms, return object schemas, internal JudgeArena DTO mapping rules, and exact Importer implementation standards for **Codeforces** (`codeforces.com`). All Codeforces adapters, clients, transformers, and importers MUST strictly comply with this specification.

---

## 1. System Invariants & Operational Rules

- **Base Endpoint**: `https://codeforces.com/api/`
- **Strict Rate Limit**: Maximum **1 request per 2 seconds** (`SYNC_COOLDOWN_SECONDS = 2`). Exceeding this limit returns `{"status": "FAILED", "comment": "Call limit exceeded"}`.
- **Response Format**: All API calls return a JSON object with three root attributes:
  - `status`: `"OK"` or `"FAILED"`.
  - `result`: Method-specific JSON element when `status == "OK"`.
  - `comment`: Descriptive error string when `status == "FAILED"`.
- **Localization**: Pass optional query param `lang=en` or `lang=ru`.

---

## 2. Authentication & HMAC SHA-512 Signature Algorithm (`apiSig`)

Anonymous requests access public data. Authenticated requests access private user data (e.g. contest hacks during rounds) using credentials configured in `.env`:
- `CODEFORCES_API_KEY` (`key`)
- `CODEFORCES_API_SECRET` (`secret`)

### `apiSig` Generation Workflow
When making authenticated API requests:
1. Append query params: `apiKey=<key>` and `time=<unix_timestamp>`.
2. Generate a random 6-character prefix `rand` (e.g., `123456`).
3. Lexicographically sort all query parameters (including `apiKey` and `time`, excluding `apiSig`) first by parameter name, then by parameter value.
4. Construct query string: `param1=value1&param2=value2...&paramN=valueN`.
5. Construct payload string: `<rand>/<methodName>?<queryString>#<secret>`.
6. Calculate hexadecimal SHA-512 hash: `sha512Hex(payload)`.
7. Final `apiSig` parameter value: `<rand><sha512Hex>`.

---

## 3. Complete Method Taxonomy & Endpoint Specifications

### A. User Methods
| Endpoint | Required Params | Optional Params | Return Type | Purpose in JudgeArena |
| :--- | :--- | :--- | :--- | :--- |
| `user.info` | `handles` (semicolon separated, max 10000) | `checkHistoricHandles` (bool) | `User[]` | Syncing user profile metadata (rating, rank, avatar, country). Batch chunks of 50. |
| `user.status` | `handle` | `from` (1-based), `count`, `includeSources` | `Submission[]` | Historical and incremental sync of user submissions. |
| `user.rating` | `handle` | None | `RatingChange[]` | Syncing user contest rating history. |
| `user.ratedList` | None | `activeOnly`, `includeRetired`, `contestId` | `User[]` | Fetching global Codeforces rated leaderboards. |
| `user.blogEntries`| `handle` | None | `BlogEntry[]` | User blog activities. |
| `user.friends` | None | `onlyOnline` | `string[]` | User friends handles (Requires Auth). |

### B. Contest Methods
| Endpoint | Required Params | Optional Params | Return Type | Purpose in JudgeArena |
| :--- | :--- | :--- | :--- | :--- |
| `contest.list` | None | `gym` (bool), `groupCode` | `Contest[]` | Ingesting and updating Codeforces contest directory. |
| `contest.standings` | `contestId` | `from`, `count`, `handles`, `room`, `showUnofficial`, `participantTypes`, `asManager` | `{contest: Contest, problems: Problem[], rows: RanklistRow[]}` | Ingesting contest standings, problems, and participant ranks. |
| `contest.ratingChanges` | `contestId` | None | `RatingChange[]` | Batch importing contest rating updates across participants. |
| `contest.status` | `contestId` | `handle`, `from`, `count`, `asManager` | `Submission[]` | Ingesting submissions for a specific contest. |
| `contest.hacks` | `contestId` | `asManager` | `Hack[]` | Ingesting contest hacks. |

### C. Problemset Methods
| Endpoint | Required Params | Optional Params | Return Type | Purpose in JudgeArena |
| :--- | :--- | :--- | :--- | :--- |
| `problemset.problems` | None | `tags` (semicolon separated), `problemsetName` | `{problems: Problem[], problemStatistics: ProblemStatistics[]}` | Ingesting and refreshing problem archive and solve counts. |
| `problemset.recentStatus` | `count` (max 1000) | `problemsetName` | `Submission[]` | Real-time global submission monitoring. |

---

## 4. Codeforces Object Schemas & Mappings

### 1. `User` Object
- `handle` (string), `email` (string), `firstName` (string), `lastName` (string), `country` (string), `city` (string), `organization` (string), `contribution` (int), `rank` (string), `rating` (int), `maxRank` (string), `maxRating` (int), `lastOnlineTimeSeconds` (int), `registrationTimeSeconds` (int), `avatar` (string), `titlePhoto` (string).

### 2. `Contest` Object
- `id` (int), `name` (string), `type` (`CF` | `IOI` | `ICPC`), `phase` (`BEFORE` | `CODING` | `PENDING_SYSTEM_TEST` | `SYSTEM_TEST` | `FINISHED`), `frozen` (bool), `durationSeconds` (int), `startTimeSeconds` (int), `relativeTimeSeconds` (int), `preparedBy` (string).

### 3. `Problem` Object
- `contestId` (int), `index` (string, e.g. `A`, `B1`), `name` (string), `type` (`PROGRAMMING` | `QUESTION`), `points` (float), `rating` (int difficulty), `tags` (string[]).
- **Composite Key in JudgeArena**: `contestId + index` (e.g. `2225A`).

### 4. `Submission` Object
- `id` (int), `contestId` (int), `creationTimeSeconds` (int), `relativeTimeSeconds` (int), `problem` (`Problem`), `author` (`Party`), `programmingLanguage` (string), `verdict` (enum), `testset` (enum), `passedTestCount` (int), `timeConsumedMillis` (int), `memoryConsumedBytes` (int).

---

## 5. Verdict Mapping to JudgeArena Enums

Codeforces verdicts MUST be mapped to `App\Enums\Verdict` as follows:

| Codeforces Verdict | Standardized `App\Enums\Verdict` | Internal Code |
| :--- | :--- | :--- |
| `OK` | `Verdict::ACCEPTED` | `AC` |
| `WRONG_ANSWER` | `Verdict::WRONG_ANSWER` | `WA` |
| `TIME_LIMIT_EXCEEDED` | `Verdict::TIME_LIMIT_EXCEEDED` | `TLE` |
| `MEMORY_LIMIT_EXCEEDED` | `Verdict::MEMORY_LIMIT_EXCEEDED` | `MLE` |
| `COMPILATION_ERROR` | `Verdict::COMPILATION_ERROR` | `CE` |
| `RUNTIME_ERROR` | `Verdict::RUNTIME_ERROR` | `RE` |
| `SKIPPED` | `Verdict::SKIPPED` | `SKIPPED` |
| `TESTING` / `SUBMITTED` | `Verdict::RUNNING` | `RUNNING` |
| `FAILED` / `CRASHED` | `Verdict::UNKNOWN` | `UNKNOWN` |

---

## 6. Detailed Importers Architectural Specifications

### A. Contest Importer (`ContestImporter.php`)
- **CLI Command**: `php artisan judgearena:import-contests codeforces {--full}`
- **Endpoint**: `contest.list?gym=false`
- **Incremental Sync Rule**:
  - Checks `PlatformSyncStateService::isSynced($platform, Contest, $contestPlatformId)`.
  - If contest exists, phase is `FINISHED`, and sync state is `Synced`, it is **skipped** (`incrementSkipped()`).
  - If contest phase is `BEFORE` or `CODING`, it saves/updates the record and calls `resetForRetry()` so future syncs will update progress in real time.

### B. Problem Importer (`ProblemImporter.php`)
- **CLI Command**: `php artisan judgearena:import-problems codeforces`
- **Strategy**: Contest-Scoped Problem Sync via `$adapter->getUserStandings($contestPlatformId)`.
- **Dual Benefit**: Fetches both contest problem set and updates `$contest->participant_count = count($standings->rows)`.
- **Incremental Sync Rule**: Skips finished contests whose problems are already marked `Synced`.

### C. User Importer (`UserImporter.php`)
- **CLI Command**: `php artisan judgearena:import-user codeforces {handle?}`
- **Strategy**: Batch API fetching in chunks of 50 (`array_chunk($profiles, 50)`).
- **Fault-Tolerant Fallback**:
  - Executes batch API call `user.info?handles=h1;h2;h3...`.
  - If any handle is deleted/invalid on Codeforces (causing API error 400), catches the exception and falls back to **sequential 1-by-1 fetching** (`processUsersSequentially`). Valid handles succeed; invalid handle is marked `Failed`.

### D. User Rating History Importer (`UserRatingHistoryImporter.php`)
- **CLI Command**: `php artisan judgearena:import-user-rating-history codeforces {handle?}`
- **Endpoint**: `user.rating?handle={handle}`
- **N+1 SQL Optimization**: Pre-indexes all DB contests and profiles into memory maps (`keyBy('platform_contest_id')`), reducing database queries to zero inside the loop.
- **Persisted Entity**: `contest_rating_changes` table (`old_rating`, `new_rating`, `rating_change`, `rank`, `performance`).

### E. User Submission Importer (`UserSubmissionImporter.php`)
- **CLI Command**: `php artisan judgearena:import-user-submissions codeforces {handle?} {--full}`
- **Endpoint**: `user.status?handle={handle}&from=1&count=100`
- **Incremental Stop Condition**: Reads `last_submission_id` from `$syncState->metadata['last_submission_id']`. Pages through submissions newest-first; as soon as `$submission->id === last_submission_id` is encountered, pagination immediately exits (`break;`), saving 99% bandwidth and time.

### F. User Standings Importer (`UserStandingImporter.php`)
- **CLI Command**: `php artisan judgearena:import-user-standings codeforces {handle?}`
- **Endpoint**: `contest.standings?contestId={id}`
- **History-Guided Contest Discovery**: Discovers participated contest IDs for a user by unioning `contest_rating_changes` and `submissions`.
- **Rate-Limit Friendly Batching**: Differential calculation (`$missingContestIds`), processes max 50 missing contests per run (`MAX_CONTESTS_PER_RUN = 50`), calls `resetForRetry()` for remaining contests.
- **Strict User Filtering**: Standings and task results are ONLY persisted for registered JudgeArena users. Unregistered public contestants are skipped.

---

## 7. Directory Structure & Architecture

```
app/Platforms/Codeforces/
├── CodeforcesAdapter.php        # Implements PlatformAdapter interface
├── Client/
│   └── BaseClient.php           # Low-level HTTP client with rate limiting & apiSig
├── DTOs/
│   ├── CodeforcesUserDTO.php
│   ├── CodeforcesContestDTO.php
│   ├── CodeforcesProblemDTO.php
│   ├── CodeforcesSubmissionDTO.php
│   └── CodeforcesStandingsDTO.php
├── Mappers/
│   ├── CodeforcesUserMapper.php
│   ├── CodeforcesContestMapper.php
│   ├── CodeforcesProblemMapper.php
│   ├── CodeforcesSubmissionMapper.php
│   ├── CodeforcesRatingChangeMapper.php
│   └── CodeforcesStandingsMapper.php
├── Transformers/
│   ├── UserTransformer.php
│   ├── ContestTransformer.php
│   ├── ProblemTransformer.php
│   ├── SubmissionTransformer.php
│   └── StandingsTransformer.php
├── Support/
│   └── ResponseNormalizer.php   # API JSON response normalizer
└── Importers/
    ├── ContestImporter.php
    ├── ProblemImporter.php
    ├── UserImporter.php
    ├── UserRatingHistoryImporter.php
    ├── UserSubmissionImporter.php
    └── UserStandingImporter.php
```
