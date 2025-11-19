# Phase 9 — Logs and monitoring (Weeks 8–9)

## Objectives
- Consolidated log viewer (Laravel, Nginx, PHP-FPM, Queue); search, filter, tail; basic uptime and SSL expiry alerts.

## Deliverables
- Blade view: logs/viewer with file selector and live tail via SSE or polling.
- Health checks page with status cards per site.

## Implementation
1) Log catalog for each site: paths to laravel.log, nginx access/error, php-fpm, workers.
2) Streaming endpoint using Symfony Process tail -F (with safe abstraction) or reading file increments.
3) Health: periodic jobs to check HTTP 200, TLS expiry date, disk space, DB connectivity; write results to a health table.
4) Notifications via Mail and optional Slack/Discord webhook.

## Testing
- Unit tests for parsers and filters; feature test stream endpoint with fake files.

## Acceptance
- User can view and filter logs; health statuses visible; alerts generated when thresholds crossed.
