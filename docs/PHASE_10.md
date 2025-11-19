# Phase 10 — Backups and restore (Weeks 9–10)

## Objectives
- Scheduled and manual backups of DB and files; store locally or S3; restore selectively.

## Deliverables
- Blade view: backups/index with schedules and history; Restore dialog.

## Implementation
1) app/Services/BackupService.php with drivers: Local and S3; create, list, verify, restore.
2) Database dump/restore using mysqldump/pg_dump; files archive via tar; checksum via sha256.
3) Scheduler integrates with Laravel schedule; status tracked in backups table.

## Testing
- Unit: archive and checksum logic mocked; feature: record entries and permissions.

## Acceptance
- Backups listed with size and checksum; restore recreates state; actions are auditable.
