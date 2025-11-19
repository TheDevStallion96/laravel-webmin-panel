# Phase 7 — Deployments and Git (Weeks 6–7)

## Objectives
- Connect repositories (GitHub/GitLab/Bitbucket via SSH); provide one-click deploy pipeline; track history and rollback.

## Deliverables
- Blade views: deploy/settings (repo+branch), deploy/history, deploy/logs.
- Pipeline: git pull/clone -> composer install -> npm ci+build -> artisan migrate -> cache clear -> symlink current -> restart queues.

## Implementation
1) SSH keys per site under storage/app/panel/ssh/{site_slug}; public key displayed for repo settings.
2) app/Jobs/RunDeployment.php orchestrates steps with per-step logs and a deployment lock.
3) Steps implemented as Actions: GitCloneOrPull, ComposerInstall, NpmBuild, RunMigrations, OptimizeLaravel, SwitchSymlink, RestartWorkers.
4) Store deployment output to storage/app/panel/deployments/{site_slug}/{timestamp}.log and index in deployments table.

### Zero-downtime option
- releases/{timestamp} dirs; current symlink switch; shared storage for .env and storage/.

## Testing
- Unit: pipeline step ordering and failure handling with fakes.
- Feature: run a fake deployment and assert history rows + log file presence.

## Acceptance
- Deploy runs end-to-end with per-step status; rollback switches to previous successful release.
