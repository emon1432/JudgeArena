# JudgeArena — Pre-Push & CI Readiness Checklist

> **Purpose**: A strict verification checklist to ensure changes will pass GitHub Actions CI without failures. Run through this checklist before notifying the user that work is complete.

---

## 🚦 Pre-Push Verification Checklist

- [ ] **1. Zero Gitignored / `docs/*` References**:
  - Run: `grep -rn "docs/" tests/ app/`
  - Output MUST be completely empty (0 results).

- [ ] **2. 100% Automated Test Suite Passing**:
  - Run: `php artisan test --compact`
  - All Unit and Feature tests must pass (100% green).
  - Assertions count should match expected suite size.

- [ ] **3. Database Migrations on Clean Schema**:
  - `RefreshDatabase` runs cleanly in tests without foreign key constraint violation or missing default values.
  - All model `$fillable` attributes match physical table schema.

- [ ] **4. MySQL 8.0 Strict Mode Compatibility**:
  - Queries using `DISTINCT` and `ORDER BY` must explicitly select all order columns.
  - No `1054 Unknown column` or `1364 Field doesn't have default value` errors.

- [ ] **5. Enum Exhaustiveness**:
  - All enum `match ($this)` expressions (e.g., in `SubmissionVerdict`) cover all enum cases without uncaught match errors.

- [ ] **6. Blade Template Consistency**:
  - New or modified Blade components adhere to reference layouts (`platforms/index.blade.php`).
  - No broken action buttons or styling discrepancies.

- [ ] **7. CI/CD Deployment Guard**:
  - In `.github/workflows/cpanel.yml`, `deploy` job is guarded with `if: false` until production secrets are ready.

- [ ] **8. Knowledge Base Synchronization (Rule 9)**:
  - Any new architectural patterns, table columns, or cache changes have been synced with `.agents/*/*.md`.

