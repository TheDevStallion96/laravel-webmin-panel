# Laravel + Blade + Alpine.js Control Panel — Comprehensive Project Plan

This document is the primary, executable plan to build a self-hosted control panel for managing multiple Laravel application environments on local machines and VPS providers (Linode, DigitalOcean, etc.). It is written for an AI agent or developer to follow step by step with minimal ambiguity.

Key principles
- Single source of truth: this plan owns the end-to-end scope, architecture, and acceptance criteria.
- Minimal viable core first: Ship Site Management, Domains/SSL, Deployments, and Logs before advanced features.
- Safety by design: least privilege, auditable actions, dry-run support, and explicit approvals for destructive operations.
- Stack alignment: Laravel 12 + Blade + Alpine.js + Vite + Tailwind; Redis (optional) for queues; SQLite/MySQL/PostgreSQL for data; Supervisor + Nginx on Linux servers.

Repository assumptions
- This repository is already a Laravel + Blade (Breeze Blade stack) + Tailwind + Alpine starter with Vite and Pest configured.
- PHP 8.2+, Node 18+, Composer. Tests run using in-memory SQLite per phpunit.xml.
- Use Laravel Pint for code style and php artisan test (Pest) for tests.

Outcomes and definitions of done
- Each feature lists Objectives, Deliverables, Implementation steps, Data model, Interfaces (routes/services/CLI), UI pages and components, Background processing, Security, Observability, Tests, and Acceptance criteria.
- All code paths have tests (unit and/or feature) and linters pass.
- User flows have end-to-end feature tests where feasible.

Project phases overview
0. Baseline and conventions
1. Platform architecture and foundation
2. Authentication and RBAC
3. Site Management (MVP core)
4. PHP Version and Web Server integration
5. Databases (MySQL/PostgreSQL)
6. Domains and SSL
7. Deployments and Git integration
8. Process management (queues and scheduler)
9. Logs and monitoring
10. Backups and restore
11. Server information and utilities
12. CLI companion
13. Optional: Docker mode and Teams
14. Testing, security, and CI/CD hardening
15. Documentation
16. Beta, launch, and post-launch

Non-functional requirements
- OS target: Ubuntu 22.04/24.04 primary; macOS local (non-root) supported for development.
- Idempotency: all provisioning/changes must be repeatable; reruns do not corrupt state.
- Concurrency: queue jobs safe to retry; deployment steps use locks per site.
- Secrets: .env and certificates stored encrypted at rest; access is role-gated and audited.
- Observability: structured logs, activity audit, health probes.

Conventions
- Names: snake_case DB columns; StudlyCaps PHP classes; kebab-case route names; PascalCase Blade components.
- Paths (panel host):
  - App data: storage/app/panel
  - Generated configs (rendered templates): storage/app/panel/configs/{site_slug}
  - SSH keys: storage/app/panel/ssh/{site_slug}
  - Backups: storage/app/panel/backups/{site_slug}
- Managed sites (managed server): /srv/sites/{site_slug} with releases/, shared/, current -> releases/{timestamp}
- Templates: resources/stubs/{nginx|supervisor|certbot|docker}/...

Tooling tasks (always available)
- composer test — run full test suite
- ./vendor/bin/pint -v — code style
- php artisan queue:work — run local queue for dev
- npm run build — build assets for production-like checks

----------------------------------------
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

----------------------------------------
Phase 1 — Platform architecture and foundation (Week 1)

Objectives
- Establish domain model scaffolding, base policies, activity logging, and error/reporting patterns.

Deliverables
- Core tables and Eloquent models with factories.
- Activity log and audit trail.
- Base FormRequest, Policy, Service, and Action patterns.

Data model (initial)
- users, personal_access_tokens (Laravel Sanctum if used)
- sites(id, name, slug, root_path, public_dir, php_version, repo_url nullable, default_branch, status[active|paused|error], environment json, deploy_strategy[basic|zero_downtime], created_by, timestamps)
- domains(id, site_id, hostname, is_primary, https_forced, created_at)
- databases(id, site_id, engine[mysql|pgsql], name, username, host, port, password_encrypted, created_at)
- ssl_certificates(id, site_id, type[letsencrypt|custom|self_signed], common_name, expires_at, path_cert, path_key, last_renewed_at, status, created_at)
- deployments(id, site_id, commit_hash, branch, status, started_at, finished_at, log_path, user_id)
- backups(id, site_id, type[db|files|full], storage[local|s3], location, size_bytes, checksum, status, created_by, created_at)
- queue_workers(id, site_id, name, connection, queue, processes, balance, status, created_at)
- scheduled_tasks(id, site_id, expression, command, enabled, created_at)
- activity_logs(id, user_id nullable, site_id nullable, action, meta json, ip, created_at)

Implementation steps
1) Create migrations for the above tables with indexes and FKs (onDelete cascade for site_id).
2) Create Eloquent models with relationships and attribute casts; add model factories.
3) Seed example data for dev.
4) Add ActivityLog service (app/Services/ActivityLogService.php) with helper activity()->onSite($site)->action('...')->meta([...]).
5) Add base policies for Site, Domain, Deployment (view, create, update, delete).

Testing
- Unit tests for model relationships and casts.
- Feature tests: creating a site records activity.

Acceptance
- Migrations run; factories generate data; tests pass.

----------------------------------------
Phase 2 — Authentication and RBAC (Week 1)

Objectives
- Multi-user auth with roles: admin, developer, viewer; API tokens for CLI.

Deliverables
- User roles/abilities; policies enforced in controllers; Sanctum tokens if CLI required.

Implementation
1) Add role column to users with enum [admin, developer, viewer].
2) Define Gates: manage-server (admin), manage-site (admin|developer), view-site (all).
3) Apply middleware in routes; ensure auth and role data are shared to Blade views (View::share or view composers) and exposed to Alpine as needed.
4) If CLI planned: install Sanctum and expose token management UI (Blade view with Alpine.js interactivity).

Testing
- Feature tests for role access to site CRUD and sensitive endpoints.

Acceptance
- Viewer cannot mutate; developer can manage assigned sites; admin is superuser.

----------------------------------------
Phase 3 — Site Management (MVP Core) (Weeks 2–3)

Objectives
- CRUD for Sites; Create Site Wizard; .env management; root path and public dir validation; pause/resume and delete.

Deliverables
- Blade views: sites/index, sites/create-wizard, sites/show, sites/settings.
- Actions for provisioning directory structure (local or remote) and writing .env.

Implementation
Data and model updates
- sites: add public_dir (default "public"), environment (json for key/val), deploy_strategy [basic|zero_downtime].

Routes and controllers
- routes/web.php
  - GET /sites -> SitesController@index
  - GET /sites/create -> SitesController@create
  - POST /sites -> SitesController@store
  - GET /sites/{site} -> SitesController@show
  - PUT /sites/{site} -> SitesController@update
  - DELETE /sites/{site} -> SitesController@destroy
  - POST /sites/{site}/pause -> SitesPauseController@store
  - POST /sites/{site}/resume -> SitesResumeController@store

Form requests and policies
- StoreSiteRequest, UpdateSiteRequest (validate unique slug, path ownership, php_version whitelist).
- Authorize via SitePolicy.

Services and actions
- app/Actions/Sites/ProvisionSite.php — create directories, write .env, initialize git (optional).
- app/Actions/Sites/WriteEnv.php — renders .env from stored environment JSON.
- app/Services/FilesystemService.php — safe FS ops; dry-run support for tests.

UI and UX
- Blade views enhanced with Alpine.js step wizard: Basics -> PHP -> Database -> Repository -> Review.
- Blade components/partials: EnvEditor, PathPicker, PhpVersionSelector.

Testing
- Feature tests for Site CRUD and wizard; unit tests for env rendering.

Acceptance
- Can create, pause, resume, delete site; env managed; audit records written.

----------------------------------------
Phase 4 — PHP Version and Web Server (Weeks 3–4)

Objectives
- Support multiple PHP versions and per-site FPM pools; generate Nginx vhosts and reload server.

Deliverables
- Templates: resources/stubs/nginx/site.conf.stub; resources/stubs/supervisor/php-fpm-pool.conf.stub
- Service to render templates with variables from Site and Domain.

Implementation
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

Security
- Run shell commands via a restricted non-root user when possible; capture stdout/stderr to logs.

Testing
- Unit: template rendering; fake Shell to assert commands; Feature: vhost endpoint writes expected files under storage/app/panel/configs.

Acceptance
- Generating config files is idempotent; a dry-run shows diffs; reload only if config test passes.

----------------------------------------
Phase 5 — Databases (Weeks 4–5)

Objectives
- Manage MySQL/PostgreSQL DBs and users per site; show connection info; create and drop safely.

Deliverables
- Blade view: databases/index per site; create user/db flows.
- Services for MySQL and Postgres with a common interface.

Implementation
1) app/Services/Database/DatabaseManager.php (factory by engine).
2) MySqlService and PostgresService with methods: createDatabase, dropDatabase, createUser, grant, revoke, dump, restore.
3) Store encrypted credentials in databases table; render .env DB_* entries for the site.

Testing
- Unit tests with fake drivers; feature test exercises controller without hitting real DB (drivers mocked).

Acceptance
- Creating a DB yields credentials, updates site env, and is auditable.

----------------------------------------
Phase 6 — Domains and SSL (Weeks 5–6)

Objectives
- Map hostnames to sites; provision SSL via Let’s Encrypt (ACME); support custom/self-signed certs; force HTTPS.

Deliverables
- Blade view: domains/index; toggle primary; toggle HTTPS forced.
- Certificates stored under storage/app/panel/certs/{site_slug}.

Implementation
1) Domain CRUD endpoints; validation for FQDN/wildcards; ensure uniqueness per site.
2) ACME client integration (e.g., certbot shell via Shell wrapper) with HTTP-01 challenges via Nginx.
3) Renew cron (Supervisor or system cron) and status tracking; emit notifications on failure.
4) Update Nginx template to include ssl_certificate/ssl_certificate_key when present; set 301 http->https when forced.

Testing
- Unit tests for domain validation; fake ACME calls; feature tests create cert and update vhost content.

Acceptance
- Provisioning cert writes files, updates vhost, reloads server, and records expiry date.

----------------------------------------
Phase 7 — Deployments and Git (Weeks 6–7)

Objectives
- Connect repositories (GitHub/GitLab/Bitbucket via SSH); provide one-click deploy pipeline; track history and rollback.

Deliverables
- Blade views: deploy/settings (repo+branch), deploy/history, deploy/logs.
- Pipeline: git pull/clone -> composer install -> npm ci+build -> artisan migrate -> cache clear -> symlink current -> restart queues.

Implementation
1) SSH keys per site under storage/app/panel/ssh/{site_slug}; public key displayed for repo settings.
2) app/Jobs/RunDeployment.php orchestrates steps with per-step logs and a deployment lock.
3) Steps implemented as Actions: GitCloneOrPull, ComposerInstall, NpmBuild, RunMigrations, OptimizeLaravel, SwitchSymlink, RestartWorkers.
4) Store deployment output to storage/app/panel/deployments/{site_slug}/{timestamp}.log and index in deployments table.

Zero-downtime option
- releases/{timestamp} dirs; current symlink switch; shared storage for .env and storage/.

Testing
- Unit: pipeline step ordering and failure handling with fakes.
- Feature: run a fake deployment and assert history rows + log file presence.

Acceptance
- Deploy runs end-to-end with per-step status; rollback switches to previous successful release.

----------------------------------------
Phase 8 — Process management (Weeks 7–8)

Objectives
- Configure and manage queue workers and the scheduler per site via Supervisor.

Deliverables
- Blade views: processes/workers and processes/scheduler.
- Templates for supervisor program configs per site.

Implementation
1) app/Services/SupervisorService.php writeWorkerConfig($site, $worker) and writeSchedulerConfig($site).
2) Commands to start/stop/reload programs; monitor state via supervisorctl.
3) Health pings (optional) to detect stalled workers; auto-restart policy.

Testing
- Unit tests generate expected config; fake supervisorctl to assert commands.

Acceptance
- Create/edit workers; start/stop works; status visible and refreshed.

----------------------------------------
Phase 9 — Logs and monitoring (Weeks 8–9)

Objectives
- Consolidated log viewer (Laravel, Nginx, PHP-FPM, Queue); search, filter, tail; basic uptime and SSL expiry alerts.

Deliverables
- Blade view: logs/viewer with file selector and live tail via SSE or polling.
- Health checks page with status cards per site.

Implementation
1) Log catalog for each site: paths to laravel.log, nginx access/error, php-fpm, workers.
2) Streaming endpoint using Symfony Process tail -F (with safe abstraction) or reading file increments.
3) Health: periodic jobs to check HTTP 200, TLS expiry date, disk space, DB connectivity; write results to a health table.
4) Notifications via Mail and optional Slack/Discord webhook.

Testing
- Unit tests for parsers and filters; feature test stream endpoint with fake files.

Acceptance
- User can view and filter logs; health statuses visible; alerts generated when thresholds crossed.

----------------------------------------
Phase 10 — Backups and restore (Weeks 9–10)

Objectives
- Scheduled and manual backups of DB and files; store locally or S3; restore selectively.

Deliverables
- Blade view: backups/index with schedules and history; Restore dialog.

Implementation
1) app/Services/BackupService.php with drivers: Local and S3; create, list, verify, restore.
2) Database dump/restore using mysqldump/pg_dump; files archive via tar; checksum via sha256.
3) Scheduler integrates with Laravel schedule; status tracked in backups table.

Testing
- Unit: archive and checksum logic mocked; feature: record entries and permissions.

Acceptance
- Backups listed with size and checksum; restore recreates state; actions are auditable.

----------------------------------------
Phase 11 — Server information and utilities (Week 10)

Objectives
- Read-only server dashboard; safe utility actions (service restarts, ufw rules) gated to admins.

Implementation
1) SystemInfoService collects OS, kernel, CPU, RAM, disk.
2) UtilitiesService wraps service commands (nginx reload, php-fpm reload) via Shell with allow-list.

Acceptance
- Info loads quickly; actions require admin and log activity.

----------------------------------------
Phase 12 — CLI companion (Week 11)

Objectives
- Provide a CLI that mirrors panel features with API token auth (optional if time permits for v1.0).

Deliverables
- A Laravel Zero or Symfony Console app in a separate package or bin/laracontrol script that calls panel APIs.

Implementation
1) Expose JSON APIs (api.php) for core operations (sites, deployments, logs tail, backups) secured by Sanctum tokens.
2) CLI commands: site:create, site:deploy, site:logs, backup:create, backup:list, backup:restore.

Acceptance
- CLI can perform at least site:list and site:deploy successfully against a dev instance.

----------------------------------------
Phase 13 — Optional: Docker mode and Teams (Weeks 12–13)

Docker mode
- Generate docker-compose.yml per site with services: app, queue, scheduler, nginx, db (optional).
- Map volumes to /srv/sites/{slug}.

Teams and permissions
- SiteMemberships table linking users to sites with role [owner|maintainer|viewer].
- Policies consult membership when role != admin.

----------------------------------------
Phase 14 — Testing, security, and CI/CD (Continuous, finalize Week 13)

- Unit tests for services, actions, template rendering, parsers.
- Feature tests for HTTP endpoints and Blade views using Laravel testing helpers.
- Contract tests for Shell wrapper with fakes; no destructive commands in CI.

Security
- CSRF on web; rate limiting on APIs; validate all input via FormRequests.
- Secrets encryption using Crypt::encryptString for stored credentials and keys.
- Sudoers or policy file for limited commands if elevated privileges needed.
- File permission audits and umasks for generated files.

CI/CD
- GitHub Actions: composer install --no-scripts; npm ci; composer test; build assets; pint.
- Artifacts: test logs and coverage; fail on coverage regression (optional threshold).

Acceptance
- All tests green; lints pass; CI runs under 10 minutes.

----------------------------------------
Phase 15 — Documentation (Parallel; finalize Week 13)

Objectives
- Clear user and developer docs in docs/ with task-oriented guides.

Deliverables
- docs/Overview.md, docs/Install.md, docs/Operating.md, docs/Developing.md, docs/Security.md.
- Screenshots or CLI examples where relevant.

Acceptance
- A new contributor can set up, run tests, and complete a small task using the docs only.

----------------------------------------
Phase 16 — Beta, launch, and post-launch

Beta (2–4 weeks)
- Recruit early users, gather feedback, triage issues, iterate.

Launch
- Tag v1.0.0, publish release notes, announcement.

Post-launch
- Monthly maintainers meeting, triage cadence, roadmap updates.

----------------------------------------
Feature implementation recipes (copy/paste templates)

1) CRUD feature template
- Objective: What the user accomplishes.
- Deliverables: Pages, endpoints, services.
- Data model: Tables/columns with types and constraints.
- Routes: web.php/api.php with methods and names.
- Controllers: methods and dependencies.
- Requests: validation rules and authorization.
- Policies: gates/abilities per action.
- Services/Actions: pure business logic interfaces and side-effects.
- UI: Blade views and Alpine.js components; data attributes and events.
- Tests: unit + feature scenarios.
- Acceptance: checklist of observable outcomes.

2) Command execution (Shell) template
- All commands go through app/Support/Shell.php with:
  - allowList: array of base commands
  - default timeout: 120s (configurable)
  - user: optional run-as user
  - capture: combined logs to storage/logs/shell/{date}.log
- Provide a FakeShell for tests.

3) Template rendering
- TemplateRenderer renders stub files from resources/stubs with {{ variables }}.
- Before overwriting files, compare content; write only if changed; provide dry-run diff.

4) Background jobs
- All long-running operations are queued jobs with retry and backoff; progress reported via events/broadcasts if needed.

5) Auditing and observability
- Use ActivityLogService for every mutation; include actor, site_id, action, and meta.
- Expose a lightweight Activity feed in the UI under each Site.

----------------------------------------
Acceptance criteria matrix (key flows)

Create Site
- User completes wizard; site row exists; directory plan rendered to storage/app/panel/configs; optional .env generated; activity logged.

Add Domain and Enable HTTPS
- Domain saved; ACME issues cert; vhost updated; HTTP redirects to HTTPS; expiry date visible.

Deploy from Git
- SSH key generated; repository cloned; pipeline produces release; current symlink updated; logs available; deployment history updated.

Configure Queue Workers
- Worker created; supervisor config written; status becomes RUNNING; can stop/start; activity logged.

View Logs
- User can open and tail logs; filter by level and component; download a segment.

Create Backup and Restore
- Backup artifact created with checksum; listing shows size; restore completes and re-enables site; audit trail exists.

----------------------------------------
Risk mitigation
- Shell safety: comprehensive allow-list and dry-run; no user-input concatenation; use escapeshellarg.
- Permissions: least privilege users; file owners/groups set explicitly; no world-writable outputs.
- Rollback: deployments are atomic with previous symlink retention.
- Complexity: start with Ubuntu 22.04/24.04 to reduce variability.

Success metrics
- 100+ active installations, <5 critical issues in first year.
- P50 deploy < 3 minutes on small VPS; P95 page load < 300ms on panel.
- 90%+ success rate of automated renewals for SSL.

Operational checklists
- Pre-release: tests green, Pint green, build assets, smoke deployment on staging.
- Incident: capture logs, identify failed step, rollback if deployment, create post-mortem entry.

Appendix — Suggested file and namespace layout
- app/Actions/Sites/*
- app/Jobs/Deployments/*
- app/Services/{WebServer,PHP,Database,Backup,Supervisor,TemplateRenderer}/*
- app/Support/Shell.php (and Fakes)
- resources/views/{sites,domains,deploy,processes,logs,backups}/*
- resources/stubs/{nginx,supervisor,certbot,docker}/*

Appendix — Example commands (non-binding)
- php artisan make:model Site -mf
- php artisan make:policy SitePolicy --model=Site
- php artisan make:request StoreSiteRequest
- php artisan make:job RunDeployment

Notes
- Prefer small, composable Actions and Services; keep controllers thin.
- Keep all side-effects (filesystem/shell) behind interfaces to enable deterministic tests.
