Phase 0 — Baseline and conventions (Day 0)

Objectives
  - Confirm stack, wire core scripts, and ensure tests execute in this repo.

Deliverables
  - Coding standards (Pint), lint scripts, and CI job skeleton.
  - Project Makefile or Composer scripts for setup/dev/test.

Implementation
  1) Ensure Composer scripts include: setup, dev, test (already present per guidelines).
  2) Verify phpunit.xml uses in-memory SQLite and APP_ENV=testing.
  3) Add base stubs directory: resources/stubs/ (commit placeholders to keep dirs under VCS).
  4) Ensure config cache clear before tests in composer test.

Acceptance
  - composer test passes in a clean checkout without .env using in-memory SQLite.
