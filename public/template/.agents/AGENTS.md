# JudgeArena Design System

## Project Overview

JudgeArena is NOT an online judge.

JudgeArena is NOT another Codeforces, AtCoder, or LeetCode.

JudgeArena is a Competitive Programming Analytics & Portfolio Platform.

It aggregates data from 100+ competitive programming platforms into a single unified profile.

Users connect multiple competitive programming accounts and JudgeArena automatically synchronizes contests, ratings, submissions, solved problems, rankings and achievements.

JudgeArena is similar in philosophy to:

- GitHub Profile
- LinkedIn
- Spotify Wrapped
- Strava
- Chess.com Profile
- StopStalk
- Clist
- Codolio

The goal is to create the best competitive programming profile on the internet.

---

# Primary Design Principles

Every page should feel like a premium SaaS application.

NOT a contest website.

NOT an online judge.

NOT a coding challenge website.

NOT a dashboard overloaded with colors.

Design inspirations:

- GitHub
- Vercel
- Stripe
- Linear
- Notion
- Supabase
- Raycast
- Arc Browser

---

# Design Philosophy

Always prefer

✓ whitespace

✓ hierarchy

✓ readability

✓ comparison

✓ clarity

instead of

✗ unnecessary colors

✗ decorative widgets

✗ duplicated information

✗ giant cards

✗ visual clutter

---

# Profile Philosophy

The user profile is the heart of JudgeArena.

There are TWO profile types.

1. Unified JudgeArena Profile

Shows statistics aggregated from ALL connected platforms.

2. Platform Profile

Shows statistics for ONE platform only.

Never confuse these two.

The unified profile should NEVER behave like Codeforces.

Platform-specific pages can use platform-specific layouts.

---

# Widgets

Every widget must answer ONE question.

Examples:

Rating History

→ How did the user's rating evolve?

Solved Problems

→ What kind of problems does the user solve?

Contest History

→ How does the user perform in contests?

Connected Platforms

→ Which platforms are connected?

Statistics

→ What is the user's overall performance?

Avoid widgets that answer multiple unrelated questions.

---

# Tables

When displaying many connected platforms or submissions,

prefer premium SaaS tables over colorful cards.

Examples:

GitHub

Stripe

Vercel

Linear

Supabase

Tables should prioritize

comparison

sorting

filtering

navigation

---

# Cards

Cards should only be used for

KPIs

Charts

Insights

Achievements

Not for large datasets.

---

# Charts

Charts should be clean.

Minimal grid lines.

No excessive colors.

Use color only when it communicates meaning.

---

# Responsive

Desktop-first.

Tablet optimized.

Mobile simplified.

Never remove important information on mobile.

Instead collapse or reorganize it.

---

# Consistency Rules

Header

Footer

Navbar

Breadcrumb

Sidebar

Buttons

Dropdowns

Spacing

Typography

must remain consistent across all pages.

Only page content should change.

Do not open browser for this project.

---

# Future Scale

JudgeArena will eventually support

100+

competitive programming platforms.

Every UI component should scale naturally from

1 platform

to

100+ platforms.

Never design assuming only Codeforces exists.
