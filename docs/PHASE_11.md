# Phase 11 — Server information and utilities (Week 10)

## Objectives
- Read-only server dashboard; safe utility actions (service restarts, ufw rules) gated to admins.

## Implementation
1) SystemInfoService collects OS, kernel, CPU, RAM, disk.
2) UtilitiesService wraps service commands (nginx reload, php-fpm reload) via Shell with allow-list.

## Acceptance
- Info loads quickly; actions require admin and log activity.
