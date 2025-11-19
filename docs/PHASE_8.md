# Phase 8 — Process management (Weeks 7–8)

## Objectives
- Configure and manage queue workers and the scheduler per site via Supervisor.

## Deliverables
- Blade views: processes/workers and processes/scheduler.
- Templates for supervisor program configs per site.

## Implementation
1) app/Services/SupervisorService.php writeWorkerConfig($site, $worker) and writeSchedulerConfig($site).
2) Commands to start/stop/reload programs; monitor state via supervisorctl.
3) Health pings (optional) to detect stalled workers; auto-restart policy.

## Testing
- Unit tests generate expected config; fake supervisorctl to assert commands.

## Acceptance
- Create/edit workers; start/stop works; status visible and refreshed.
