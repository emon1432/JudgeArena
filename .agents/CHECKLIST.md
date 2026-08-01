# CHECKLIST.md — AI Agent Pre-Commit & Code Review Checklists

> **Purpose for AI Agents**: Use these checklists to self-audit code changes before completing tasks or marking PRs ready. Every item MUST pass before declaring success.

---

## 1. Architecture Review Checklist

- [ ] **No Controller Scraping**: No HTTP Controller, Livewire Component, or Blade view executes web scraping or external HTTP calls directly.
- [ ] **Service Layer Isolation**: Multi-entity business logic and sync orchestration reside in `App\Services\*`.
- [ ] **Core Contract Adherence**: All code modifications comply with interfaces in `App\Core\Contracts\*`.
- [ ] **Clean DTO Boundaries**: Data Transfer Objects in `App\Core\DTOs\*` are immutable (`readonly`) and free of Eloquent/HTTP dependencies.
- [ ] **Adapter Resolution**: Services resolve platform adapters dynamically via `config/platforms.php` or factory interfaces (no hardcoded adapter instantiations).

---

## 2. Platform Adapter Review Checklist

- [ ] **Interface Compliance**: Class implements `App\Core\Contracts\Platforms\PlatformAdapter`.
- [ ] **Pure DTO Output**: Adapter getter methods return typed `App\Core\DTOs\*` objects, never raw HTTP/HTML responses or vendor arrays.
- [ ] **Verdict Normalization**: Platform-specific verdicts (`OK`, `AC`, `Accepted`, `WA`) are mapped to standardized system verdict enums.
- [ ] **Importer Encapsulation**: Database persistence logic is separated into concrete importer classes in `app/Platforms/<PlatformName>/Importers/`.
- [ ] **Config Registration**: Platform metadata, base URLs, rate limits, and adapter mapping are registered in `config/platforms.php`.

---

## 3. Feature Review Checklist

- [ ] **Strict Typing**: All new PHP files include `declare(strict_types=1);` with explicit parameter and return types.
- [ ] **Enum Usage**: All sync statuses, job entities, and verdicts use backed PHP Enums (`App\Enums\*`).
- [ ] **Migration Safety**: Database migrations specify appropriate column types and foreign key constraints.
- [ ] **Test Coverage**: Automated Pest PHP tests (`php artisan test`) are created or updated for new features.

---

## 4. Security Review Checklist

- [ ] **Zero Hardcoded Secrets**: Credentials, API keys, and session cookies (`ATCODER_SESSION_COOKIES`) are loaded strictly via `.env` / `config/platforms.php`.
- [ ] **Scraper Input Sanitization**: User handles and problem IDs passed to headless Chrome (`chrome-php/chrome`) are sanitized against shell injection.
- [ ] **Mass Assignment Protection**: Eloquent models define explicit `$fillable` arrays or guarded properties.
- [ ] **Input Validation**: All incoming requests and Livewire inputs are validated before processing.

---

## 5. Performance & Scraper Review Checklist

- [ ] **Mandatory Async Execution**: Heavy ingestion and sync tasks run exclusively via queue jobs (`PlatformSyncJob`).
- [ ] **Bulk Ingestion Operations**: Importers use bulk operations (`upsert`, `insertOrIgnore`, `DB::transaction()`) for submission batches.
- [ ] **Scraper Rate Limit Compliance**: Scraping routines respect throttling delays configured in `config/platforms.php` to prevent HTTP 429 / IP bans.
- [ ] **DOM Crawler Memory Cleanup**: Paginated HTML crawling loops explicitly release crawler references (`unset($crawler)`) to prevent worker memory leaks.
- [ ] **Composite Index Verification**: Database queries on submissions utilize unique composite index on `(platform_profile_id, external_submission_id)`.
