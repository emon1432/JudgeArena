## 📌 Description
<!-- Provide a concise description of the changes introduced by this pull request. -->

## 🎯 Type of Change
- [ ] 🐛 Bug fix (non-breaking change fixing an issue)
- [ ] ✨ New feature (non-breaking change adding functionality)
- [ ] 🌐 Platform integration (new judge/scraper adapter)
- [ ] ♻️ Refactoring / Performance optimization
- [ ] 📝 Documentation / Knowledge base update

## 📋 Quality & Architectural Checklist
Please ensure that your PR conforms to the JudgeArena standards before requesting review:
- [ ] **Empirical Tests Pass**: `php artisan test` runs 100% green locally.
- [ ] **Zero `docs/*` Dependencies**: No test, service, or feature references files in `docs/*`.
- [ ] **Clean Architecture Compliant**: Follows strict layer rules (Controllers $\to$ Services $\to$ Clients/DTOs).
- [ ] **UI Component Replication**: Blade templates and data tables strictly mirror reference designs (`platforms/index.blade.php`).
- [ ] **Code Style**: Code adheres to Laravel Pint formatting standards.
- [ ] **Knowledge Base Synchronized**: Any new architectural decision or pattern is documented under `.agents/`.

## 📸 Screenshots / Demos (If Applicable)
<!-- Attach screenshots or GIFs demonstrating UI or API results. -->

## 🔗 Related Issues / References
<!-- Closes #123 or fixes issue -->

