# CODEFORCES API — Platform Specification & Master Reference

> **Purpose for AI Agents**: This document captures the complete API specifications, rate limits, request signature algorithms, return object schemas, and internal JudgeArena DTO mapping rules for **Codeforces** (`codeforces.com`). All Codeforces adapter, client, transformer, and importer implementations MUST comply with this specification.

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
| `user.info` | `handles` (semicolon separated, max 10000) | `checkHistoricHandles` (bool) | `User[]` | Syncing user profile metadata (rating, rank, avatar, country). |
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

## 6. Directory Structure & Architecture

```
app/Platforms/Codeforces/
├── CodeforcesAdapter.php        # Implements PlatformAdapter interface
├── Clients/
│   └── CodeforcesApiClient.php  # Low-level HTTP client with rate limiting & apiSig
├── DTOs/
│   ├── CodeforcesUserDTO.php
│   ├── CodeforcesContestDTO.php
│   ├── CodeforcesProblemDTO.php
│   └── CodeforcesSubmissionDTO.php
├── Mappers/
│   ├── CodeforcesUserMapper.php
│   ├── CodeforcesContestMapper.php
│   ├── CodeforcesProblemMapper.php
│   └── CodeforcesSubmissionMapper.php
├── Transformers/
│   ├── UserTransformer.php
│   ├── ContestTransformer.php
│   ├── ProblemTransformer.php
│   └── SubmissionTransformer.php
└── Importers/
    ├── UserImporter.php
    ├── ContestImporter.php
    ├── ProblemImporter.php
    ├── UserSubmissionImporter.php
    └── RatingChangeImporter.php
```
