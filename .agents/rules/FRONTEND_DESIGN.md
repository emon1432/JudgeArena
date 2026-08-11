# FRONTEND_DESIGN.md — Frontend & View Architecture Rules

> **Purpose for AI Agents**: This document specifies strict rules for frontend architecture, component replication, UI consistency, and performance inside JudgeArena.

---

## 1. Strict Design Consistency & Component Replication

### Rule 1.1: Exact Component Replication
- **Instruction**: All web pages MUST strictly follow a unified design language. When rendering lists, toolbars, or tables, **you must open the reference page (e.g., `platforms/index.blade.php`) and copy its exact DOM structure, CSS classes, and alignment rules.**
- **Why**: Inventing new layouts breaks the unified premium feel of JudgeArena.

### Rule 1.2: Table Consistency Requirements
- **Instruction**: For tables, ensure row alignment (`class="text-center"` on standard cells, `class="text-start"` for primary columns like title), avatar boxes, and column combinations (e.g., Image + Title) perfectly mirror the reference design. Do not invent new layouts if a reference exists.

---

## 2. JavaScript & View Architecture

### Rule 2.1: Page-Specific JavaScript Management Pattern
- **Instruction**: Do NOT create static `.js` files in `public/` for page-specific view logic. Page-specific JavaScript MUST be stored inside Blade script partials (e.g., `resources/views/web/pages/<feature>/scripts.blade.php`) and included in the view using `@push('scripts') @include('web.pages.<feature>.scripts') @endpush`.
- **Why**: Allows direct, safe usage of Blade directives (`route()`, `config()`, `@json()`, `csrf_token()`) inside JavaScript without hardcoding URLs or creating global Window scope pollution.

### Rule 2.2: Self-Contained Component-Based Architecture for UI Partials
- **Instruction**: Reusable UI elements MUST be created as single-file, self-contained Blade Components inside `resources/views/components/*` and ALWAYS utilized across views instead of raw HTML copy-pasting (e.g., ALWAYS use `<x-breadcrumb>` for top navigation and page titles, `<x-infinite-scroll>` for endless listings). NEVER recreate manual breadcrumb or loader HTML in individual view pages.
- **Why**: Guarantees DRY architecture, global accessibility across all views, and consistent styling without cluttering `views/includes/`.

---

## 3. Data Rendering & Infinite Scrolling

### Rule 3.1: Universal Infinite Scrolling & Debounced Search (No Traditional Pagination)
- **Instruction**: Traditional numbered pagination links (`paginate()`, `<x-pagination>`) are STRICTLY PROHIBITED across all data tables and listings. All listings handling large datasets (thousands to lakhs of records like submissions, rankings, platforms, and problems) MUST implement **Universal Infinite Scrolling / On-Scroll Loading** using server-side Cursor Pagination (`cursorPaginate()`) or simple chunking combined with a reusable frontend Intersection Observer architecture. Search inputs MUST use 300ms JavaScript debouncing with AJAX table reset and smooth infinite loading.
- **Why**: Traditional page numbers create friction and degrade User Experience when navigating lakhs of data points. Universal infinite scrolling delivers an immersive, app-like real-time UX while keeping client memory and database queries optimal.

### Rule 3.2: Detail / Profile Page Table Preview vs Directory Infinite Scroll
- **Instruction**: On detail/profile overview pages (e.g. Platform Detail `show.blade.php`, User Profile) where additional widgets, analytics cards, or content components are positioned below a table or tabbed section, NEVER implement Infinite Scrolling on the main page stream to prevent the "Footer Trap" UX problem. Instead, display a **Top 10 Recent Preview** with a prominent Call-to-Action button linking directly to the full filtered Directory page (where full-screen Universal Infinite Scrolling is implemented).
- **Why**: Ensures users can easily reach bottom-page widgets and components without getting trapped in an endless expanding table, while keeping detail page load times lightning-fast.
