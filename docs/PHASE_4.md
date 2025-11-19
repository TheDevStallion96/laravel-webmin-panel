# Phase 4 — PHP Version and Web Server (Weeks 3–4)

## Objectives
- Support multiple PHP versions and per-site FPM pools; generate Nginx vhosts and reload server.

## Deliverables
- Templates: resources/stubs/nginx/site.conf.stub; resources/stubs/supervisor/php-fpm-pool.conf.stub
- Service to render templates with variables from Site and Domain.

## Implementation
1) Add app/Services/TemplateRenderer.php to render Blade-like stub files.
2) app/Services/WebServer/NginxService.php
   - writeSiteVhost($site, $domains)
   - testConfig() -> bool
   - reload()
3) app/Services/PHP/FpmService.php
   - ensureVersionInstalled($version)
   - writePool($site)
   - reload()
4) Command execution via Process wrapper: app/Support/Shell.php with allow-listed commands and timeouts.

## Security
- Run shell commands via a restricted non-root user when possible; capture stdout/stderr to logs.

## Testing
- Unit: template rendering; fake Shell to assert commands; Feature: vhost endpoint writes expected files under storage/app/panel/configs.

## Acceptance
- Generating config files is idempotent; a dry-run shows diffs; reload only if config test passes.
