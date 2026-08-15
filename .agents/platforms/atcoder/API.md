# AtCoder Platform Specifications & Web Scraping Specification

## Overview & System Axioms

AtCoder does not provide a public official REST API. Rather than relying on third-party services (e.g. Kenkoooo API), **JudgeArena directly ingests data via AtCoder's native web JSON endpoints and HTML DOM scraping**.

This document serves as the authoritative specification for AtCoder data ingestion, endpoints, JSON structures, HTML selectors, and fallback strategies.

---

## 🌐 Endpoints Specification Map

### 1. User Rating History (Native Web JSON)
- **Endpoint**: `GET https://atcoder.jp/users/{handle}/history/json?contestType=algo`
- **Heuristic Endpoint**: `GET https://atcoder.jp/users/{handle}/history/json?contestType=heuristic`
- **Format**: Native JSON Array
- **Key Fields**:
  - `IsRated` (bool)
  - `Place` (int - rank)
  - `OldRating` (int)
  - `NewRating` (int)
  - `Performance` (int)
  - `ContestName` (string)
  - `ContestScreenName` (string - e.g. `abc350`)
  - `EndTime` (ISO 8601 string timestamp)

### 2. Contest Standings (Native Web JSON)
- **Endpoint**: `GET https://atcoder.jp/contests/{contestScreenName}/standings/json`
- **Virtual Standings Endpoint**: `GET https://atcoder.jp/contests/{contestScreenName}/standings/virtual/json`
- **Format**: Native JSON Object
- **Key Fields**:
  - `TaskDetails`: Array of problem IDs, titles, and maximum scores.
  - `StandingsData`: Array of participant rows containing `UserScreenName`, `Rank`, `TotalResult` (score, penalty, time), and `TaskResults` (per-problem score, penalty count, status).

### 3. Contest Results & Rating Changes (Native Web JSON)
- **Endpoint**: `GET https://atcoder.jp/contests/{contestScreenName}/results/json`
- **Format**: Native JSON Array
- **Key Fields**: `IsRated`, `Place`, `OldRating`, `NewRating`, `Performance`, `UserScreenName`.

---

## 📄 HTML Scraping Specification Map

### 1. Contests Ingestion Specification (DOM Scraping & Incremental Early-Break)
- **Primary Catalog Scrape**: `https://atcoder.jp/contests/archive?lang=ja&page={page}` (with `lang=ja` to ensure 100% complete catalog coverage of 6,320+ contests).
- **Categories Crawled**:
  - Main Archive: `https://atcoder.jp/contests/archive?lang=ja&page={page}`
  - Category 20 (Weekday Contests): `https://atcoder.jp/contests/archive?category=20&lang=ja&page={page}`
  - Category 60 (Daily Training): `https://atcoder.jp/contests/archive?category=60&lang=ja&page={page}`
  - Home Page (4 Tables: Permanent, Active, Scheduled, Daily): `https://atcoder.jp/contests/?lang=ja`
  - Unlisted/Hidden Contests: `storage/app/private/atcoder_hidden_contests.json`
- **English Titles Resolution (3-Layer Hybrid)**:
  1. Official English Title Lookup (`lang=en` overlay archive lookup).
  2. Local In-Memory Dictionary (`決勝` ➔ `Finals`, `予選` ➔ `Qualifier`, `夏` ➔ `Summer`, `プログラミングコンテスト` ➔ `Programming Contest`).
  3. Automatic Fallback Translator (`Google Translate API` for remaining Japanese characters).
- **Slug Generation**: `Str::slug($platformContestId . '-' . $englishTitle)`
- **Contest Types**: `'normal'`, `'weekday'`, `'daily_training'`, `'permanent'`, `'hidden'`.
- **Permanent Contests Detail Scraping**:
  - Detail URL: `https://atcoder.jp/contests/{contest_id}?lang=en`
  - DOM Selector: `//small[contains(@class, "contest-duration")]//time`
  - 1st `<time>`: `start_time` (e.g. `2012-06-25 00:00:00+0900`)
  - 2nd `<time>`: `end_time` (e.g. `3038-01-19 12:14:07+0900`)
  - Phase: `CODING` (permanently ongoing practice).
- **Smart Incremental Early-Break Strategy**:
  - On scheduled/hourly runs, each category checks Page 1. If all contests on Page 1 are already in DB and marked `FINISHED` & `Synced`, it triggers an immediate early break for that category (~2 seconds total execution time).
  - Admin CLI Option: `php artisan judgearena:import-contests atcoder --full` forces a complete multi-page deep sweep.

### 2. Contest Problems List (`tasks`)
- **URL**: `https://atcoder.jp/contests/{contestScreenName}/tasks`
- **DOM Selectors**: `table.table tbody tr`
- **Fields Extracted**: Problem ID/Code (e.g. `abc350_a`), Title, Time Limit, Memory Limit, Task URL.

### 3. User Submissions (`submissions`)
- **URL**: `https://atcoder.jp/contests/{contestScreenName}/submissions?f.User={handle}&page={page}`
- **DOM Selectors**: `table.table tbody tr`
- **Fields Extracted**: Submission ID, Submission Time, Task Name, User, Language, Score, Code Size, Status (`AC`, `WA`, `TLE`, `MLE`, `CE`, `RE`), Execution Time (`ms`), Memory Used (`KB`).

---

## 🛠️ Data Handling Guidelines
1. **Verdicts Normalization**:
   - `AC` ➔ `AC`
   - `WA` ➔ `WA`
   - `TLE` ➔ `TLE`
   - `MLE` ➔ `MLE`
   - `CE` ➔ `CE`
   - `RE` ➔ `RE`
   - `WJ` / `QJ` ➔ `Pending` / `SystemTest`
2. **Slug Generation**:
   - `Str::slug($title . '-' . $platformProblemId)`
