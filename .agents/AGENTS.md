# JudgeArena — AI Agent Master Index & Rules

> **Note for AI Assistant**: This file is the primary entry point for AI Coding Assistants working on `JudgeArena`. The project's architectural knowledge, engineering rules, memory, workflows, and review checklists are split into modular documents under `.agents/`. You MUST consult these files before undertaking design, refactoring, or implementation tasks.

---

## 📚 Knowledge Base Documents

| Document | Purpose & Scope |
| :--- | :--- |
| **[PROJECT.md](file:///home/emonideas/Documents/JudgeArena/.agents/PROJECT.md)** | Core domain definition, ubiquitous language, models map, scope boundaries, and non-goals. |
| **[ARCHITECTURE.md](file:///home/emonideas/Documents/JudgeArena/.agents/ARCHITECTURE.md)** | 5-layer system design, data flow, adapter lifecycle, DTO lifecycle, and dependency rules. |
| **[RULES.md](file:///home/emonideas/Documents/JudgeArena/.agents/RULES.md)** | Strict coding standards for Laravel, DTOs, Services, Queues, Database bulk operations, and Security. |
| **[WORKFLOWS.md](file:///home/emonideas/Documents/JudgeArena/.agents/WORKFLOWS.md)** | Step-by-step procedures for platform integrations, scraper development, bug fixes, and refactoring. |
| **[MEMORY.md](file:///home/emonideas/Documents/JudgeArena/.agents/MEMORY.md)** | Permanent architectural invariants, system axioms, and strategic technical decisions. |
| **[CHECKLIST.md](file:///home/emonideas/Documents/JudgeArena/.agents/CHECKLIST.md)** | Pre-commit self-review checklists for Architecture, Adapters, Security, and Performance. |

---

## ⚡ Quick Start Local Commands

- **Dependencies**: `composer install` && `npm install`
- **All-in-One Dev**: `composer run dev` (Runs web server, queue worker, pail logs, and Vite concurrently)
- **Run Tests**: `php artisan test`

---

## 🤖 Behavioral Directives for AI Agents

1. **Language Preference**: Respond in **Bengali** (or English when requested) with professional formatting.
2. **Consult Knowledge Documents First**: Before modifying or creating code, inspect the relevant `.agents/*.md` document.
3. **Strict Compliance**: Follow Clean Architecture layer rules (`ARCHITECTURE.md`), non-negotiable invariants (`MEMORY.md`), and pre-commit checklists (`CHECKLIST.md`).
4. **Empirical Verification**: Always run `php artisan test` to verify changes before declaring a task finished.
