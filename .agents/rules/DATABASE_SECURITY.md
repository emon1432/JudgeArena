# DATABASE_SECURITY.md — Database & Security Rules

> **Purpose for AI Agents**: This document specifies strict database performance, ingestion, and security guidelines for JudgeArena. Follow these rules to prevent system bottlenecks and security vulnerabilities.

---

## 1. Database & Ingestion Performance Rules

### Rule 1.1: Bulk Database Operations in Importers
- **Instruction**: When persisting batches of submissions or rating histories in Importers (`App\Platforms\<Platform>\Importers\*`), NEVER execute Eloquent `save()` inside a loop. Use `upsert()`, `insertOrIgnore()`, or `DB::transaction()`.
- **Why**: Inserting 5,000 historical submissions individually causes 5,000 database round-trips; bulk upsert completes in a single query.

### Rule 1.2: Composite Index Requirements
- **Instruction**: Any query filtering submissions by user handle and platform MUST utilize composite indexes. Table migrations MUST enforce unique composite keys on `(platform_profile_id, external_submission_id)`.
- **Why**: Guarantees fast lookup speeds for sync state checks even as the `submissions` table grows to millions of records.

### Rule 1.3: Standardized Database Constraint Naming Convention
- **Instruction**: All explicitly named indexes, unique constraints, and foreign keys in migrations MUST follow a predictable, standardized prefix format:
  - **Unique Constraints**: `uq_<table_name>_<columns>` (e.g. `uq_submissions_platform_submission_id`, `uq_contests_platform_contest_id`)
  - **Composite & Secondary Indexes**: `idx_<table_name>_<columns>` (e.g. `idx_submissions_profile_verdict_submitted`, `idx_standings_contest_rank`)
  - **Foreign Keys**: `fk_<table_name>_<referenced_column>` (where explicitly named)
- **Why**: Ensures uniform database schema maintenance, predictable SQL migration troubleshooting, and self-documenting raw SQL query plans in MariaDB/MySQL.

### Rule 1.4: Unified Storage (No Data Duplication)
- **Instruction**: Submissions and Rating Changes MUST be persisted in their respective single, unified tables (`submissions`, `contest_rating_changes`). Do NOT create separate tables for "Global" versus "User" records.
- **Why**: Centralizes data aggregation, prevents desync between global leaderboards and user profiles, and simplifies queries.

---

## 2. Security & Credential Safeguards

### Rule 2.1: Strict Environment Configuration
- **Instruction**: Platform credentials, session cookies (e.g. `ATCODER_SESSION_COOKIES`), and API keys MUST be loaded strictly via `config/platforms.php` sourced from `.env`. NEVER hardcode credentials in adapter files.
- **Why**: Prevents credential leakage in Git repositories and enables separate staging/production configurations.

### Rule 2.2: Sanitization of Scraper Input Parameters
- **Instruction**: User handles and problem IDs passed to headless browser tools (`chrome-php/chrome`) or DOM crawlers MUST be sanitized to prevent shell parameter injection or XSS.
- **Why**: Protects the server environment from Remote Code Execution (RCE) when executing headless browser commands with malicious user inputs.

---

## 3. Platform Scraper & Memory Safety Rules

### Rule 3.1: Strict Rate Limiting Compliance
- **Instruction**: Platform Adapters MUST respect the `rate_limit` and `cooldown` settings configured in `config/platforms.php`. Loop scraping MUST include throttling delays.
- **Why**: Prevents external Online Judges from IP-banning JudgeArena servers or triggering bot protection.

### Rule 3.2: Crawler Memory Management
- **Instruction**: When processing paginated HTML responses in scraping loops, memory references to `Crawler` or DOM instances MUST be cleared explicitly (`unset($crawler)` or garbage collection triggers).
- **Why**: Prevents PHP process memory exhaustion in long-running queue workers (`php artisan queue:listen`).
