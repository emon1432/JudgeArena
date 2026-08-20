# AtCoder Platform Specifications & Ingestion Master Reference

## Overview & System Axioms

AtCoder does not provide an official public REST API for all resources. Rather than relying on third-party services (e.g. Kenkoooo API), **JudgeArena directly ingests data via AtCoder's native web JSON endpoints, DOM scraping, and session-authenticated scrapers**.

This document serves as the authoritative specification for AtCoder data ingestion, endpoints, JSON structures, HTML selectors, DTO mappings, rate limits, and Importer implementations.

---

## 🌐 Endpoints & Scraping Specification Map

### 1. User Rating History (Native Web JSON)
- **Algorithm Endpoint**: `GET https://atcoder.jp/users/{handle}/history/json?contestType=algo`
- **Heuristic Endpoint**: `GET https://atcoder.jp/users/{handle}/history/json?contestType=heuristic`
- **Format**: Native JSON Array
- **Key Fields**:
  - `IsRated` (bool)
  - `Place` (int - rank)
  - `OldRating` (int)
  - `NewRating` (int)
  - `Performance` (int) / `InnerPerformance` (int - fallback when `Performance` is null)
  - `ContestName` (string)
  - `ContestScreenName` (string - e.g. `abc350`)
  - `EndTime` (ISO 8601 string timestamp)

### 2. Contest Standings (Native Web JSON)
- **Live Standings Endpoint**: `GET https://atcoder.jp/contests/{contest_id}/standings/json`
- **Format**: Native JSON Object
- **Key Fields & Scaling**:
  - `TaskResults`: Array of problem IDs, titles, and maximum scores.
  - `StandingsData`: Array of participant rows containing `UserScreenName`, `Rank`, `TotalResult` (Score, Penalty, Elapsed time in nanoseconds), and `TaskResults`.
  - **Score Scaling**: Points in JSON are multiplied by 100 (e.g., `255000` = `2550.00` points). Must divide by `100` before saving.
  - **Time Scaling**: Elapsed time is in nanoseconds (e.g., `1977000000000` ns). Must divide by `10^9` to get seconds (`1977` sec).

### 3. User Profile (DOM Scraping & Dual Contest Types)
- **Algorithm Profile URL**: `GET https://atcoder.jp/users/{handle}?contestType=algo`
- **Heuristic Profile URL**: `GET https://atcoder.jp/users/{handle}?contestType=heuristic`
- **Scraped Data**:
  - `avatar_url`: Avatar image link (`https://img.atcoder.jp/icons/...`)
  - `country`: Country or region
  - `birth_year`: Birth year
  - `affiliation`: University or company
  - Social Handles: `twitter_id`, `topcoder_id`, `codeforces_id`
  - Dual Ratings (`algo` vs `heuristic`): `rank` (clean integer), `percentile` (e.g., `"Top <0.01%"`), `rating` (clean integer), `is_provisional` (boolean), `highest_rating` (clean integer), `user_title` (e.g., `"King"`), `rated_matches` (integer), `last_competed` (`YYYY-MM-DD`).

### 4. User Submissions (Cookie Authenticated DOM Scraping)
- **URL**: `GET https://atcoder.jp/contests/{contest_id}/submissions?f.User={handle}&page={page}`
- **Authentication**: Requires `Cookie: RE_session=...` header from `config('platforms.atcoder.credentials.atcoder_session_cookies')` / `.env`.
- **DOM Table Parsing**:
  - `submission_id`: `td[4]->data-id` or detail URL
  - `submitted_at`: Timestamp string
  - `task_id`: Problem ID (e.g. `abc399_a`)
  - `username`: Author handle
  - `language`: Programming language
  - `score`: Points obtained
  - `verdict`: `AC`, `WA`, `TLE`, `MLE`, `CE`, `RE`
  - `exec_time`: Converted from `109 ms` to `109` milliseconds integer.
  - `memory`: Converted from `37376 KiB` to `38273024` bytes integer.

---

## ⚡ Command Taxonomy & Ingestion Workflows

| Command | Signature | Scope & Strategy |
| :--- | :--- | :--- |
| **Import Contests** | `judgearena:import-contests atcoder {--full}` | Scrapes AtCoder contest archives with English title auto-translation and early pagination exit. |
| **Import Problems** | `judgearena:import-problems atcoder` | Contest-scoped scraping of tasks and problem score extraction from task detail pages. |
| **Import Users** | `judgearena:import-users atcoder {handle?}` | Scrapes AtCoder user profiles with clean parsing of rank, rating, highest rating, and user titles. |
| **Import Rating History** | `judgearena:import-user-rating-history atcoder {handle?}` | Native JSON sync for `algo` and `heuristic` rating changes with `innerPerformance` fallback. |
| **Import Submissions** | `judgearena:import-user-submissions atcoder {handle?} {--full}` | Cookie-authenticated submission table scraping with `stopSubmissionId` early exit. |
| **Import Standings** | `judgearena:import-user-standings atcoder {handle?}` | Native JSON standings ingestion, history-guided contest discovery, registered users filter. |

---

## 🏗️ Detailed Importer Implementation Specifications

### 1. `ContestImporter.php`
- **Catalog Archives**: Scrapes `Normal`, `Weekday`, `Daily Training`, and `Permanent` contest tables.
- **English Title Resolution**: Uses English title map overlay, falling back to built-in Japanese translation dictionary (`translateJapaneseTitle`).
- **Early-Break Optimization**: Checks if all contests on Page 1 are already in DB and marked `FINISHED`. If so, stops scraping further pages on incremental runs.

### 2. `ProblemImporter.php`
- **Contest Task Scraper**: Scrapes `/contests/{contest_id}/tasks`.
- **Score Extraction**: Visits `/contests/{contest_id}/tasks/{task_id}?lang=en` and parses regex `Score: 100` / `<var>100</var>`.
- **Triple-Fallback Problem Matcher**:
  ```php
  $problem = $this->problemMap[$probId]
      ?? $this->problemMap[strtolower($probId)]
      ?? $this->problemMap[str_replace('_', '-', $probId)]
      ?? null;
  ```

### 3. `UserImporter.php`
- **Scrapes Both Contest Types**: Fetches `algo` and `heuristic` profile HTML pages.
- **Clean Field Parsers**:
  - `rating`: Integer extraction via regex (`/^\d+/`)
  - `is_provisional`: Checks for `(Provisional)` string
  - `highest_rating` & `user_title`: Parses integer rating and title (e.g. `"King"`)
  - `rank` & `percentile`: Parses numeric rank and percentile string (`"Top <0.01%"`)
  - `last_competed`: Standardized YYYY-MM-DD date string.
- **Primary Rating Resolution**: Prioritizes clean `algo` rating as primary `$userDto->rating`, falling back to `heuristic` rating.

### 4. `UserRatingHistoryImporter.php`
- **Dual JSON Sync**: Ingests both `algo` and `heuristic` rating history JSONs.
- **InnerPerformance Fallback**:
  `$performance = $ratingChange->performance ?? $ratingChange->innerPerformance;`
- **Contest Safety Check**: Logs warning and skips if referenced contest is not in DB without crashing.

### 5. `UserSubmissionImporter.php`
- **Cookie Session Auth**: Passes `Cookie: RE_session=...` header.
- **Unit Standardization**:
  - Execution Time: `109 ms` ➔ `109` ms
  - Memory Consumption: `37376 KiB` ➔ `38273024` bytes
- **Incremental Early Exit**: Tracks `last_submission_id` in `PlatformSyncState`. When encountered, breaks pagination loop immediately.

### 6. `UserStandingsImporter.php`
- **History-Guided Contest Discovery**: Queries `contest_rating_changes` and `submissions` to discover only contests where active registered JudgeArena users participated.
- **Score & Time Scaling**: Divides JSON score by `100` and converts nanoseconds to seconds (`floor(elapsed / 1e9)`).
- **Registered User Filter**: Persists standings and `standing_task_results` ONLY for registered JudgeArena users (`$rowProfile !== null`).

---

## 🛠️ Data Handling & Verdict Mapping Rules

### Verdict Mapping
- `AC` ➔ `AC` (Accepted)
- `WA` ➔ `WA` (Wrong Answer)
- `TLE` ➔ `TLE` (Time Limit Exceeded)
- `MLE` ➔ `MLE` (Memory Limit Exceeded)
- `CE` ➔ `CE` (Compilation Error)
- `RE` ➔ `RE` (Runtime Error)
- `WJ` / `QJ` ➔ `Pending` / `SystemTest` (Waiting for Judging)
