# JudgeArena — Testing Standards & Quality Guidelines

> **Purpose**: Defines mandatory testing rules, isolation practices, mocking protocols, and test suite design for unit and feature tests across JudgeArena.

---

## 1. Absolute Self-Containment (Zero External File Dependencies)

- **Strict Invariant**: Tests must run deterministically on any fresh machine or CI runner without external dependencies, local files, or internet access.
- **`docs/*` Ban**: Under NO circumstances should any test reference `docs/*` or any path listed in `.gitignore`.
- **Fixtures**:
  - Small payloads must be written inline directly in the test method as PHP arrays or strings.
  - HTML scraper tests must use compact synthetic HTML strings covering all target DOM nodes (e.g. `<img class="avatar">`, `table.dl-table`, etc.).

---

## 2. Test Suite Organization

```
tests/
├── Pest.php                         # Pest bootstrap configuration
├── TestCase.php                     # Base TestCase (RefreshDatabase, platform/profile factories)
├── Unit/                            # Isolated fast unit tests (no network, purely algorithmic/transformers)
│   ├── Enums/                       # Enum mapping & exhaustive match tests
│   ├── Platforms/
│   │   ├── AtCoder/                 # Scraper, mapper, transformer tests
│   │   └── Codeforces/              # Mapper, transformer tests
│   └── Services/                    # Core service tests (Cache, normalizers)
└── Feature/                         # Database-backed integration tests
    ├── Admin/                       # Admin controllers, datatables, status toggles
    └── Platforms/
        ├── AtCoder/                 # AtCoder importers with Http::fake()
        └── Codeforces/              # Codeforces importers with Http::fake()
```

---

## 3. Mocking & Isolation Standards

1. **HTTP Requests**:
   - Always use `Http::fake([...])` for outbound API calls.
   - Use wildcards for resilient matching: `Http::fake(['*contest.list*' => Http::response([...], 200)])`.
   - Never allow real network requests to hit Codeforces, AtCoder, or external services during tests.
2. **File Storage & Standings Cache**:
   - Tests that trigger `StandingsCacheService` or file storage operations must call `Storage::fake('local')` to prevent reading or writing to real storage directories.
3. **Database Interactions**:
   - All tests extend `Tests\TestCase` which uses `Illuminate\Foundation\Testing\RefreshDatabase`.
   - Utilize helper factory methods on `TestCase`:
     - `createPlatform(slug, name, baseUrl)`
     - `createUserWithProfile(platform, handle)`
     - `createAdminUser()`

---

## 4. Execution Speed & Rate Limits in Tests

- Do not let rate limit sleep functions run in tests.
- When testing scrapers or importers with rate limit protections, isolate or mock the scraper (`$this->mock(AtCoderHtmlScraper::class)`) or clear the cache so rate-limiting backoff does not introduce artificial delays.
- Target execution speed: Full test suite should finish in under 45 seconds locally.

---

## 5. Verification Command

Before committing or pushing any code, run:
```bash
php artisan test --compact
```
Expected output: 100% passing tests with 0 failures or errors.

