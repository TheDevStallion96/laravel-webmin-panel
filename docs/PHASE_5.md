# Phase 5 — Databases (Weeks 4–5)

## Objectives
- Manage MySQL/PostgreSQL DBs and users per site; show connection info; create and drop safely.

## Deliverables
- Blade view: databases/index per site; create user/db flows.
- Services for MySQL and Postgres with a common interface.

## Implementation
1) app/Services/Database/DatabaseManager.php (factory by engine).
2) MySqlService and PostgresService with methods: createDatabase, dropDatabase, createUser, grant, revoke, dump, restore.
3) Store encrypted credentials in databases table; render .env DB_* entries for the site.

## Testing
- Unit tests with fake drivers; feature test exercises controller without hitting real DB (drivers mocked).

## Acceptance
- Creating a DB yields credentials, updates site env, and is auditable.
