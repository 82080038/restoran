# Devin Configuration for RESTAURANT_ERP

This directory contains the Devin session configuration for the RESTAURANT_ERP project.

## Workflows

- **ai-development-cycle.md** — Main AI-driven development cycle (analysis, design, implementation, testing, integration, deployment).
- **project-cleanup.md** — Procedure to remove generated artifacts and obsolete test harnesses.
- **comprehensive-testing.md** — Playwright-headed and PHPUnit testing workflow.

## Knowledge

- **knowledge/playwright-headed-browser.md** — Canonical reference for Playwright selectors, URLs, test users, and E2E scenarios.

## Quick Start

1. Review `DOCUMENTATION/PROJECT_AUDIT_CLEANUP_AND_TESTING_PLAN.md` for the latest audit.
2. Run cleanup before committing: `.devin/workflows/project-cleanup.md`.
3. Run tests after changes: `.devin/workflows/comprehensive-testing.md`.
4. Follow the AI development cycle: `.devin/workflows/ai-development-cycle.md`.

## Notes

- Workflows and knowledge files are tracked in Git so they are available across sessions.
- Generated artifacts (`test-results`, `playwright-report`, `node_modules`, `logs`) are ignored and should not be committed.
