# Phase 12 — CLI companion (Week 11)

## Objectives
- Provide a CLI that mirrors panel features with API token auth (optional if time permits for v1.0).

## Deliverables
- A Laravel Zero or Symfony Console app in a separate package or bin/laracontrol script that calls panel APIs.

## Implementation
1) Expose JSON APIs (api.php) for core operations (sites, deployments, logs tail, backups) secured by Sanctum tokens.
2) CLI commands: site:create, site:deploy, site:logs, backup:create, backup:list, backup:restore.

## Acceptance
- CLI can perform at least site:list and site:deploy successfully against a dev instance.
