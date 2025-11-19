# Phase 14 — Testing, security, and CI/CD (Continuous, finalize Week 13)

## Testing strategy
 Unit tests for services, actions, template rendering, parsers.
 Feature tests for HTTP endpoints and Blade views using Laravel testing helpers.
 Contract tests for Shell wrapper with fakes; no destructive commands in CI.

- Sudoers or policy file for limited commands if elevated privileges needed.
- File permission audits and umasks for generated files.

## CI/CD
- GitHub Actions: composer install --no-scripts; npm ci; composer test; build assets; pint.
- Artifacts: test logs and coverage; fail on coverage regression (optional threshold).

## Acceptance
- All tests green; lints pass; CI runs under 10 minutes.
