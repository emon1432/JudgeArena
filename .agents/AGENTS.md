# JudgeArena — AI Agent Master Index & Rules

> **Note for AI Assistant**: This file is the primary entry point for AI Coding Assistants working on `JudgeArena`. The project's architectural knowledge, engineering rules, memory, workflows, and review checklists are split into modular documents under `.agents/`. You MUST consult these files before undertaking design, refactoring, or implementation tasks.

---

## 📚 Knowledge Base Documents

| Document | Purpose & Scope |
| :--- | :--- |
| **[PROJECT.md](./memory/PROJECT.md)** | Core domain definition, ubiquitous language, models map, scope boundaries, and non-goals. |
| **[ARCHITECTURE.md](./memory/ARCHITECTURE.md)** | 5-layer system design, data flow, adapter lifecycle, DTO lifecycle, and dependency rules. |
| **[SYSTEM_AXIOMS.md](./memory/SYSTEM_AXIOMS.md)** | Permanent architectural invariants, system axioms, and strategic technical decisions. |
| **[BACKEND_LARAVEL.md](./rules/BACKEND_LARAVEL.md)** | Strict coding standards for Laravel, DTOs, Services, and Queues. |
| **[DATABASE_SECURITY.md](./rules/DATABASE_SECURITY.md)** | Database bulk operations, indexes, and scraper security rules. |
| **[FRONTEND_DESIGN.md](./rules/FRONTEND_DESIGN.md)** | Strict frontend architecture, UI consistency, and infinite scrolling rules. |
| **[PLATFORM_INTEGRATION.md](./workflows/PLATFORM_INTEGRATION.md)** | Step-by-step procedures for new platform integrations and scrapers. |
| **[MAINTENANCE_REFACTOR.md](./workflows/MAINTENANCE_REFACTOR.md)** | Step-by-step procedures for bug fixes, new features, and core refactoring. |
| **[PRE_COMMIT_REVIEW.md](./checklists/PRE_COMMIT_REVIEW.md)** | Pre-commit self-review checklists for Architecture, Adapters, Security, and Performance. |

---

## 🌐 Integrated Platform Specifications Index (`.agents/platforms/`)

| Platform | Documentation Path | Key Architecture |
| :--- | :--- | :--- |
| **Codeforces** | **[API.md](./platforms/codeforces/API.md)** | Codeforces REST API spec, HMAC SHA-512 `apiSig`, rate limits, DTO mappers. |
| **AtCoder** | **[API.md](./platforms/atcoder/API.md)** | AtCoder Native JSON endpoints, DOM scraping spec, category handling. |

---

## ⚡ Quick Start Local Commands

- **Dependencies**: `composer install` && `npm install`
- **All-in-One Dev**: `composer run dev` (Runs web server, queue worker, pail logs, and Vite concurrently)
- **Run Tests**: `php artisan test`

---

## 🤖 Behavioral Directives for AI Agents

1. **Language Preference**: Respond in **Bengali** (or English when requested) with professional formatting.
2. **Consult Knowledge Documents First**: Before modifying or creating code, inspect the relevant `.agents/*/*.md` document.
3. **Strict Compliance**: Follow Clean Architecture layer rules (`ARCHITECTURE.md`), non-negotiable invariants (`SYSTEM_AXIOMS.md`), and pre-commit checklists (`PRE_COMMIT_REVIEW.md`).
4. **Mandatory UI Reference Check**: Before building ANY page, you MUST physically view the reference pages (`platforms`, `contests`) and copy their Search, Filter, and Table HTML structures perfectly. Do not invent layouts.
5. **Empirical Verification**: Always run `php artisan test` to verify changes before declaring a task finished.
6. **Strict Design Consistency & Component Replication**: All web pages MUST strictly follow a unified design language. When rendering lists, toolbars, or tables, **you must open the reference page (e.g., `platforms/index.blade.php`) and copy its exact DOM structure, CSS classes, and alignment rules.** For tables, ensure row alignment (`class="text-center"`), avatar boxes, and column combinations (e.g., Image + Title) perfectly mirror the reference design. Do not invent new layouts if a reference exists.
7. **Empirical Verification Protocol**: After implementing or updating any platform component/feature, run empirical test verification ONCE to verify functionality before reporting completion.
