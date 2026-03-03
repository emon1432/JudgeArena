# Platform Functionality

Version: 1.0  
Date: 2026-03-03  
Source: `app/Platforms/*` implementations

---

## 1. Purpose

This document describes **every platform integration** implemented under the platform folder, including:
- API endpoints / web sources used
- Fetching technique (REST, GraphQL, HTML scraping, browser fallback)
- Profile sync behavior
- Submission sync behavior
- Extra metadata collected
- Known limitations and fallbacks

This is implementation documentation for the platform engine only.

---

## 2. Shared Integration Pattern

Each platform adapter implements the common contract:
- `platform()` → stable platform key
- `profileUrl(handle)` → canonical profile URL
- `supportsSubmissions()` → indicates whether submission-based solved counting is used
- `fetchProfile(handle)` → normalized profile DTO (`rating`, `totalSolved`, `raw`)
- `fetchSubmissions(handle)` → normalized accepted submissions DTO collection

Sync orchestrator behavior:
1. Calls `fetchProfile`.
2. If `supportsSubmissions=true`, fetches submissions and computes unique accepted problem count.
3. If submission fetch fails, falls back to profile-level solved count.
4. Writes sync success/failure log with duration.

---

## 3. Platform-by-Platform Details

## 3.1 Codeforces

### Adapter/Client
- `app/Platforms/Codeforces/CodeforcesAdapter.php`
- `app/Platforms/Codeforces/CodeforcesClient.php`

### Data sources and APIs
- REST API base: `https://codeforces.com/api`
- Profile: `/user.info?handles={handle}`
- Submissions: `/user.status?handle={handle}&from=1[&count=n]`
- Contest list: `/contest.list?gym=false`
- Global rank by rating: `/user.ratedList?activeOnly=true&includeRetired=false`
- Problem tags: `/problemset.problems`
- Contest history source (web): `https://codeforces.com/contests/with/{handle}` (HTML table scrape)

### Fetching technique
- Primary: REST API + HTML scrape for contest history.
- Uses caching:
  - rating rank cache (6h)
  - problem tags cache (24h)
- Retries used for `user.ratedList` call.

### Synced functionality
- Profile rating, max rating, rank, contribution, avatars.
- Contest history + rating graph data.
- Accepted submissions with:
  - problem id (`contestId + index`)
  - language, memory/time, submission URL, problem URL
  - problem tags from cached problemset map

### Submission support
- `supportsSubmissions = true`
- Solved count derived from unique accepted submissions.

---

## 3.2 LeetCode

### Adapter/Client
- `app/Platforms/LeetCode/LeetCodeAdapter.php`
- `app/Platforms/LeetCode/LeetCodeClient.php`

### Data sources and APIs
- GraphQL endpoint: `https://leetcode.com/graphql`
- Main profile query: `matchedUser` (profile, submitStatsGlobal, badges)
- Recent AC submissions query: `recentAcSubmissionList` (limit 20)
- Contest query: `userContestRanking`, `userContestRankingHistory`
- Calendar query: `userCalendar`

### Fetching technique
- GraphQL POST with browser-like headers.
- Retries for transient/blocked statuses: `403, 429, 499, 503, 504`.
- Attempts endpoint with and without trailing slash.
- Backoff with jitter between retries.

### Synced functionality
- Profile details + badges/upcoming badges.
- Difficulty breakdown (easy/medium/hard solved).
- Contest rating/ranking/history.
- Submission calendar and streak metrics.
- Recent accepted submissions (raw metadata only).

### Submission support
- `supportsSubmissions = false`
- Reason: API exposes only recent limited submissions (insufficient for full solved derivation).
- Solved count uses profile difficulty totals.

---

## 3.3 AtCoder

### Adapter/Client
- `app/Platforms/AtCoder/AtCoderAdapter.php`
- `app/Platforms/AtCoder/AtCoderClient.php`

### Data sources and APIs
- Profile page: `https://atcoder.jp/users/{handle}` (HTML scrape)
- Contest history page: `https://atcoder.jp/users/{handle}/history` (HTML table scrape)
- Kenkoooo API (configurable base, default `https://kenkoooo.com/atcoder/atcoder-api`):
  - `/v3/user/ac_rank?user={handle}`
  - `/v3/user_info?user={handle}`
  - fallback `/v2/user_info?user={handle}`

### Fetching technique
- HTML scraping via DomCrawler for profile/history.
- JSON fetch for accepted count from Kenkoooo endpoints.
- If Kenkoooo returns 403, tries headless browser fallback (Chrome + `chrome-php`).

### Synced functionality
- Rating, highest rating, rank, rated matches.
- Contest history and rating graph transformation.
- Accepted count from Kenkoooo API chain.

### Submission support
- `supportsSubmissions = false`
- Submission sync intentionally disabled; solved count comes from accepted-count API/profile value.

---

## 3.4 CodeChef

### Adapter/Client
- `app/Platforms/CodeChef/CodeChefAdapter.php`
- `app/Platforms/CodeChef/CodeChefClient.php`

### Data sources and APIs
- Profile page: `https://www.codechef.com/users/{handle}` (HTML scrape)
- Rating graph source: JS variable `var all_rating = ...` from profile HTML
- Problem details API conversion:
  - from `/CONTEST/problems/PROBLEM`
  - to `/api/contests/CONTEST/problems/PROBLEM?v={timestamp}`

### Fetching technique
- HTML scraping for profile metrics.
- Embedded JavaScript data extraction + JSON decoding for contest rating graph.
- Optional API fetch for per-problem tags/editorial/author.

### Synced functionality
- Rating, max rating, stars.
- Country rank/global rank.
- Solved totals (fully/partially/total).
- Badges.
- Contest category history split:
  - Long, Cook-off, Lunchtime, Starters.

### Submission support
- `supportsSubmissions = false`
- Reason: full submissions require OAuth API/token workflow and pagination management (not implemented).

---

## 3.5 HackerRank

### Adapter/Client
- `app/Platforms/HackerRank/HackerRankAdapter.php`
- `app/Platforms/HackerRank/HackerRankClient.php`

### Data sources and APIs
- Profile page: `https://www.hackerrank.com/profile/{handle}`
- Extracts embedded initial state JSON from HTML script.
- Rating history API: `/rest/hackers/{handle}/rating_histories_elo`
- Submissions API: `/rest/hackers/{handle}/recent_challenges`
- Problem details API via transformed REST challenge URLs.

### Fetching technique
- HTML fetch + script payload parsing (`initialData` or legacy `__INITIAL_STATE__`).
- REST JSON calls for rating histories and submissions.
- Cursor-based pagination for submissions until `last_page=true`.

### Synced functionality
- Solved challenge count.
- Ranking and badges count.
- Rating graph (contest categories/events).
- Accepted submissions list with challenge URL/type.

### Submission support
- `supportsSubmissions = true`
- Solved count can be recomputed from unique accepted submissions.

---

## 3.6 HackerEarth

### Adapter/Client
- `app/Platforms/HackerEarth/HackerEarthAdapter.php`
- `app/Platforms/HackerEarth/HackerEarthClient.php`

### Data sources and APIs
- Profile page: `https://www.hackerearth.com/@{handle}/`
- Rating graph endpoint: `/ratings/AJAX/rating-graph/{handle}/`
- Leaderboard API for global rank lookup: `/api/leaderboard/?page={p}&size={n}&type=rated`
- Submissions pages:
  - landing `/submissions/{handle}/`
  - AJAX feed `/AJAX/feed/newsfeed/submission/user/{handle}/?page={p}`
- Browser-rendered metrics script: `public/web/js/hackerearth-profile-metrics.mjs` (Node/Playwright process)

### Fetching technique
- Multi-source strategy:
  1. Browser automation (Playwright process) for reliable profile metrics.
  2. HTML text fallback extraction for solved/rank values.
  3. Leaderboard API probing (with cache + candidate-page search) for missing global rank.
  4. Rating graph extraction from AJAX HTML script variable.
- Caching used for leaderboard pages and global rank resolution.

### Synced functionality
- Rating, solved count, global/country rank.
- Rating graph and extra profile metrics.

### Submission support
- Adapter currently sets `supportsSubmissions = false`.
- Even though client has submission crawler logic, it is not used in core sync due to instability/timeouts.
- Current strategy: trust profile metrics for total solved.

### Known limitations
- Submission endpoints can timeout/flake for heavy users.
- Playwright runtime dependency required for highest-quality metrics extraction.

---

## 3.7 SPOJ

### Adapter/Client
- `app/Platforms/Spoj/SpojAdapter.php`
- `app/Platforms/Spoj/SpojClient.php`

### Data sources and APIs
- Profile page: `https://www.spoj.com/users/{handle}/`
- Status pages (paginated): `https://www.spoj.com/status/{handle}/all/start={offset}`
- Problem pages for tags/author metadata.
- Optional FlareSolverr proxy endpoint: `{FLARESOLVERR_URL}/v1`

### Fetching technique
- Primary challenge: Cloudflare anti-bot protection.
- Strategy:
  1. Try FlareSolverr if configured.
  2. Fallback to direct HTTP with challenge detection and retries.
  3. Detect Cloudflare pages (`Just a moment`, `_cf_chl_opt`) and raise handled errors.
- Uses DOM parsing for profile and status table data.

### Synced functionality
- Profile: solved count, points, world rank, join date.
- Submissions: accepted-only extraction, problem slug, language.
- Optional problem tags/author scraping.

### Submission support
- `supportsSubmissions = true`
- Special solved rule in sync action:
  - final solved = `max(profile.totalSolved, uniqueAcceptedFromSubmissions)`
  to protect against incomplete paginated history.

### Known limitations
- Cloudflare may block both profile and submission fetches.
- Adapter returns graceful placeholder profile when blocked.

---

## 3.8 UVA (UHunt)

### Adapter/Client
- `app/Platforms/Uva/UvaAdapter.php`
- `app/Platforms/Uva/UvaClient.php`

### Data sources and APIs
- UHunt API base: `http://uhunt.onlinejudge.org/api`
- Username to UID: `/uname2uid/{handle}`
- Ranklist by UID: `/ranklist/{uid}/0/0`
- Submissions payload: `/subs-user/{uid}`
- Problem mapping: `/p`

### Fetching technique
- API-based integration (no page scraping required).
- Supports handle as username or numeric UID.
- Uses retries and longer timeout; SSL verification disabled for compatibility.

### Synced functionality
- Profile solved count, submission count, rank/ranking.
- Accepted submissions with mapped problem names and language/verdict normalization.

### Submission support
- `supportsSubmissions = true`
- Solved count can be derived from unique AC problem IDs if ranklist data is incomplete.

---

## 3.9 Timus

### Adapter/Client
- `app/Platforms/Timus/TimusAdapter.php`
- `app/Platforms/Timus/TimusClient.php`

### Data sources and APIs
- Profile page: `http://acm.timus.ru/author.aspx?id={handle}`
- Status pages: `http://acm.timus.ru/status.aspx?author={handle}&count={n}[&from={id}]`

### Fetching technique
- HTML scraping from profile table rows for:
  - total solved
  - rank by solved
  - rank by rating
  - rating score
- Optional submission pagination crawler exists in client.

### Synced functionality
- Profile metrics and ranking information.
- Submission normalization with parsed timestamps and verdict mapping.

### Submission support
- Adapter currently sets `supportsSubmissions = false`.
- Reason: profile solved count considered authoritative; paginated submission history may miss old data.

---

## 4. Capability Matrix (Current Behavior)

| Platform | Profile Source | Submission Source | `supportsSubmissions()` | Primary Solved Source |
|---|---|---|---|---|
| Codeforces | REST API + contest page scrape | REST API (`user.status`) | true | Unique AC submissions |
| LeetCode | GraphQL | Limited recent GraphQL list | false | Profile difficulty totals |
| AtCoder | Profile/history HTML + Kenkoooo API | Disabled in adapter | false | Kenkoooo accepted count |
| CodeChef | Profile HTML + embedded JS | Disabled in adapter | false | Profile solved metrics |
| HackerRank | Profile HTML + REST | REST paginated (`recent_challenges`) | true | Unique AC submissions |
| HackerEarth | Profile HTML + Playwright + leaderboard API | Client supports AJAX crawl but adapter disables | false | Browser/profile metrics |
| SPOJ | Profile/status HTML (Cloudflare-handled) | Status pages | true | Submissions; fallback/special max() rule |
| UVA | UHunt API | UHunt API (`subs-user`) | true | API + unique AC fallback |
| Timus | Profile/status HTML | Client supports status crawl but adapter disables | false | Profile solved metric |

---

## 5. Cross-Platform Error/Fallback Rules

1. Profile fetch failure should mark sync as failed for that profile.
2. Submission fetch failure should not always fail full sync:
   - If profile fetch succeeded, sync may continue with profile solved count.
3. Keep raw payloads to debug parser/API changes.
4. Platform-specific anti-bot/rate-limit behavior is expected and must be handled gracefully.

---

## 6. What to Preserve in Any Rewrite

If this is reimplemented in another stack, preserve these invariants:
1. One adapter per platform with same logical contract.
2. `supportsSubmissions()` decides solved counting strategy.
3. Distinct handling for API-driven vs scrape-driven platforms.
4. Graceful fallback when external endpoints are unstable.
5. Platform-specific exceptions (e.g., SPOJ max(profile, submissions)).

---

## 7. Primary Source Files

- `app/Contracts/Platforms/PlatformAdapter.php`
- `app/Actions/SyncPlatformProfileAction.php`
- `app/Jobs/SyncPlatformProfileJob.php`
- `app/Platforms/Codeforces/*`
- `app/Platforms/LeetCode/*`
- `app/Platforms/AtCoder/*`
- `app/Platforms/CodeChef/*`
- `app/Platforms/HackerRank/*`
- `app/Platforms/HackerEarth/*`
- `app/Platforms/Spoj/*`
- `app/Platforms/Uva/*`
- `app/Platforms/Timus/*`
- `config/platforms.php`
