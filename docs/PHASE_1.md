# Phase 1 — Platform architecture and foundation (Week 1)

## Objectives
- Establish domain model scaffolding, base policies, activity logging, and error/reporting patterns.

## Deliverables
- Core tables and Eloquent models with factories.
- Activity log and audit trail.
- Base FormRequest, Policy, Service, and Action patterns.

## Data model (initial)
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

## Implementation steps
1) Create migrations for the above tables with indexes and FKs (onDelete cascade for site_id).
2) Create Eloquent models with relationships and attribute casts; add model factories.
3) Seed example data for dev.
4) Add ActivityLog service (app/Services/ActivityLogService.php) with helper activity()->onSite($site)->action('...')->meta([...]).
5) Add base policies for Site, Domain, Deployment (view, create, update, delete).

## Testing
- Unit tests for model relationships and casts.
- Feature tests: creating a site records activity.

## Acceptance
- Migrations run; factories generate data; tests pass.

---

```
Create Laravel migration files for a site management platform with the following requirements:

TABLES TO CREATE:

1. sites table:
   - id (primary key)
   - name, slug (unique), root_path, public_dir, php_version
   - repo_url (nullable), default_branch
   - status (enum: active, paused, error)
   - environment (json)
   - deploy_strategy (enum: basic, zero_downtime)
   - created_by (foreign key to users)
   - timestamps
   - Indexes on: slug, status, created_by

2. domains table:
   - id, site_id (FK to sites with CASCADE delete)
   - hostname (unique), is_primary (boolean), https_forced (boolean)
   - created_at timestamp
   - Indexes on: hostname, site_id, is_primary

3. databases table:
   - id, site_id (FK to sites with CASCADE delete)
   - engine (enum: mysql, pgsql), name, username, host, port
   - password_encrypted (text)
   - created_at timestamp
   - Indexes on: site_id, name

4. ssl_certificates table:
   - id, site_id (FK to sites with CASCADE delete)
   - type (enum: letsencrypt, custom, self_signed)
   - common_name, expires_at, path_cert, path_key
   - last_renewed_at, status (string)
   - created_at timestamp
   - Indexes on: site_id, expires_at, status

5. deployments table:
   - id, site_id (FK to sites with CASCADE delete)
   - commit_hash, branch, status (string)
   - started_at, finished_at (nullable timestamps)
   - log_path (text), user_id (FK to users with SET NULL delete, nullable)
   - Indexes on: site_id, status, started_at, user_id

6. backups table:
   - id, site_id (FK to sites with CASCADE delete)
   - type (enum: db, files, full)
   - storage (enum: local, s3)
   - location, size_bytes (bigInteger), checksum, status
   - created_by (FK to users), created_at timestamp
   - Indexes on: site_id, type, created_at, status

7. queue_workers table:
   - id, site_id (FK to sites with CASCADE delete)
   - name, connection, queue, processes (integer), balance, status
   - created_at timestamp
   - Indexes on: site_id, status

8. scheduled_tasks table:
   - id, site_id (FK to sites with CASCADE delete)
   - expression (cron expression), command, enabled (boolean)
   - created_at timestamp
   - Indexes on: site_id, enabled

9. activity_logs table:
   - id, user_id (FK to users with SET NULL delete, nullable)
   - site_id (FK to sites with CASCADE delete, nullable)
   - action (string), meta (json), ip (string, nullable)
   - created_at timestamp
   - Indexes on: user_id, site_id, action, created_at

Use Laravel's Schema builder with proper column types, foreign key constraints, and indexes. Ensure migrations follow Laravel naming conventions (e.g., create_sites_table.php). Include complete use statements and follow PSR-12 standards.
```

```
Create Eloquent models for a Laravel site management platform based on these tables:

MODELS TO CREATE:

1. Site model (app/Models/Site.php):
   - Fillable: name, slug, root_path, public_dir, php_version, repo_url, default_branch, status, environment, deploy_strategy, created_by
   - Casts: environment (array), status (string - will be enum), deploy_strategy (string)
   - Relationships: 
     * hasMany: domains, databases, sslCertificates, deployments, backups, queueWorkers, scheduledTasks, activityLogs
     * belongsTo: creator (users table via created_by)

2. Domain model:
   - Fillable: site_id, hostname, is_primary, https_forced
   - Casts: is_primary (boolean), https_forced (boolean)
   - Relationships: belongsTo Site

3. Database model:
   - Fillable: site_id, engine, name, username, host, port, password_encrypted
   - Casts: port (integer)
   - Relationships: belongsTo Site
   - Accessor for password decryption

4. SslCertificate model:
   - Fillable: site_id, type, common_name, expires_at, path_cert, path_key, last_renewed_at, status
   - Casts: expires_at (datetime), last_renewed_at (datetime)
   - Relationships: belongsTo Site

5. Deployment model:
   - Fillable: site_id, commit_hash, branch, status, started_at, finished_at, log_path, user_id
   - Casts: started_at (datetime), finished_at (datetime)
   - Relationships: belongsTo Site, belongsTo User

6. Backup model:
   - Fillable: site_id, type, storage, location, size_bytes, checksum, status, created_by
   - Casts: size_bytes (integer)
   - Relationships: belongsTo Site, belongsTo creator (User via created_by)

7. QueueWorker model:
   - Fillable: site_id, name, connection, queue, processes, balance, status
   - Casts: processes (integer)
   - Relationships: belongsTo Site

8. ScheduledTask model:
   - Fillable: site_id, expression, command, enabled
   - Casts: enabled (boolean)
   - Relationships: belongsTo Site

9. ActivityLog model:
   - Fillable: user_id, site_id, action, meta, ip
   - Casts: meta (array)
   - Relationships: belongsTo User (nullable), belongsTo Site (nullable)

For each model:
- Include proper namespace and use statements
- Use $fillable arrays (not $guarded)
- Define all relationship methods with proper return types
- Add appropriate casts array
- Follow Laravel naming conventions
- Include PHPDoc blocks for IDE support
```

```
Create Laravel model factories for a site management platform with realistic test data:

FACTORIES TO CREATE:

1. SiteFactory:
   - name: fake company/project name
   - slug: slugified version of name (unique)
   - root_path: /var/www/[slug]
   - public_dir: public or public_html
   - php_version: random from ['8.1', '8.2', '8.3']
   - repo_url: fake GitHub URL or null (50% chance)
   - default_branch: 'main' or 'master'
   - status: random from ['active', 'paused', 'error']
   - environment: array with 3-5 key-value pairs (APP_ENV, APP_DEBUG, etc.)
   - deploy_strategy: 'basic' or 'zero_downtime'
   - created_by: User::factory()

2. DomainFactory:
   - site_id: Site::factory()
   - hostname: fake domain (example.com format)
   - is_primary: false (create state for primary domain)
   - https_forced: random boolean

3. DatabaseFactory:
   - site_id: Site::factory()
   - engine: 'mysql' or 'pgsql'
   - name: site slug + '_db'
   - username: site slug + '_user'
   - host: 'localhost' or '127.0.0.1'
   - port: 3306 for mysql, 5432 for pgsql
   - password_encrypted: encrypt fake password

4. SslCertificateFactory:
   - site_id: Site::factory()
   - type: random from ['letsencrypt', 'custom', 'self_signed']
   - common_name: domain name
   - expires_at: date 90 days in future
   - path_cert: /etc/ssl/certs/[domain].crt
   - path_key: /etc/ssl/private/[domain].key
   - last_renewed_at: recent date
   - status: 'active' or 'expired'

5. DeploymentFactory:
   - site_id: Site::factory()
   - commit_hash: fake 40-char hex string
   - branch: 'main', 'develop', or 'staging'
   - status: 'pending', 'in_progress', 'completed', or 'failed'
   - started_at: recent datetime
   - finished_at: started_at + 2-10 minutes (or null if status is pending/in_progress)
   - log_path: /var/log/deployments/[id].log
   - user_id: User::factory() or null

6. BackupFactory:
   - site_id: Site::factory()
   - type: 'db', 'files', or 'full'
   - storage: 'local' or 's3'
   - location: path based on storage type
   - size_bytes: random between 1MB and 5GB
   - checksum: fake sha256 hash
   - status: 'completed' or 'failed'
   - created_by: User::factory()

7. QueueWorkerFactory:
   - site_id: Site::factory()
   - name: worker name (e.g., 'default-worker')
   - connection: 'redis' or 'database'
   - queue: 'default', 'emails', or 'processing'
   - processes: 1-5
   - balance: 'simple' or 'auto'
   - status: 'running' or 'stopped'

8. ScheduledTaskFactory:
   - site_id: Site::factory()
   - expression: valid cron expression (e.g., '0 0 * * *')
   - command: artisan command (e.g., 'backup:run')
   - enabled: random boolean

9. ActivityLogFactory:
   - user_id: User::factory() or null
   - site_id: Site::factory() or null
   - action: action string (e.g., 'site.created', 'deployment.started')
   - meta: array with relevant data
   - ip: fake IP address or null

Include complete use statements, proper namespace, and create useful factory states (e.g., ->primary() for domains, ->completed() for deployments). Follow Laravel 10+ factory syntax.
```

```
Create an activity logging system for Laravel with a fluent interface:

REQUIREMENTS:

1. Create ActivityLogService class (app/Services/ActivityLogService.php):
   - Fluent methods:
     * onSite($site): Set the site context
     * byUser($user): Set the user (defaults to auth()->user())
     * action($action): Set the action string
     * meta($array): Set metadata array
     * withIp($ip): Set IP address (defaults to request()->ip())
     * log(): Save the activity log and return the created record
   
   - Example usage:
   ```
   activity()
     ->onSite($site)
     ->action('site.created')
     ->meta(['key' => 'value'])
     ->log();
   ```
- Should handle:
     * Null user (for system actions)
     * Null site (for global actions)
     * Automatic IP detection from request
     * Automatic user detection from auth

2. Create helper function:
   - In app/Helpers/helpers.php (create if needed)
   - Function: `activity()` returns new ActivityLogService instance

3. Register helper:
   - Auto-load helpers.php in composer.json files section

4. Features to include:
   - Chain method calls (return $this for fluency)
   - Validate required fields before saving (action is required)
   - Handle both model instances and IDs for site/user
   - Throw exception if log() called without action

Include complete code with use statements, proper typing, and PHPDoc blocks. Follow Laravel service patterns and PSR-12 standards.
```

```
Create Laravel authorization policies for a site management platform:

POLICIES TO CREATE:

1. SitePolicy (app/Policies/SitePolicy.php):
   - viewAny(User $user): Can user view the sites list?
   - view(User $user, Site $site): Can user view this specific site?
   - create(User $user): Can user create new sites?
   - update(User $user, Site $site): Can user update this site?
   - delete(User $user, Site $site): Can user delete this site?
   
   Authorization logic:
   - Admins (assume $user->is_admin) can do everything
   - Regular users can only manage sites they created (created_by = $user->id)
   - viewAny always returns true for authenticated users

2. DomainPolicy (app/Policies/DomainPolicy.php):
   - view(User $user, Domain $domain): Check via related site
   - create(User $user, Site $site): Can add domain to this site?
   - update(User $user, Domain $domain): Can update this domain?
   - delete(User $user, Domain $domain): Can delete this domain?
   
   Authorization logic:
   - Check if user can update the related site
   - Admins can do everything

3. DeploymentPolicy (app/Policies/DeploymentPolicy.php):
   - view(User $user, Deployment $deployment): Check via related site
   - create(User $user, Site $site): Can create deployment for this site?
   - cancel(User $user, Deployment $deployment): Can cancel this deployment?
   
   Authorization logic:
   - Check if user can update the related site
   - Admins can do everything

4. Register policies in AuthServiceProvider:
   - Add to $policies array
   - Map Site::class => SitePolicy::class, etc.

Include complete code with:
- Proper use statements and namespaces
- Type hints for parameters and return types
- Handle relationship loading efficiently (use $domain->site)
- PHPDoc blocks
- Follow Laravel 10+ policy conventions
```

```
Create database seeders and comprehensive tests for a Laravel site management platform:

PART 1 - SEEDERS:

Create DatabaseSeeder (database/seeders/DatabaseSeeder.php) that generates:

1. Users:
   - 1 admin user (email: admin@example.com, is_admin: true)
   - 3 regular users

2. For each of 5 sites:
   - 1 site with random data
   - 2-4 domains (one marked as primary)
   - 1 database
   - 1 SSL certificate
   - 3-5 deployments (mix of completed and failed)
   - 2-3 backups
   - 0-2 queue workers
   - 1-3 scheduled tasks
   - 5-10 activity log entries

Ensure variety in data (different statuses, types, etc.)

PART 2 - UNIT TESTS:

Create tests/Unit/ModelTest.php:

Test all model relationships:
- Site hasMany domains, databases, deployments, backups, etc.
- Domain belongsTo site
- Deployment belongsTo site and user
- All bidirectional relationships work

Test attribute casting:
- Site environment casts to array
- Domain is_primary casts to boolean
- SslCertificate expires_at casts to datetime
- Backup size_bytes casts to integer

Test factory data generation:
- Each factory creates valid records
- Factories with states work correctly
- Related factories properly link records

PART 3 - FEATURE TESTS:

Create tests/Feature/ActivityLogTest.php:
- Test activity logging service fluent interface
- Test creating site records activity log entry
- Test activity logs with/without user context
- Test activity logs with/without site context
- Test meta data is properly stored and retrieved

Create tests/Feature/SitePolicyTest.php:
- Test admin can view, create, update, delete any site
- Test user can manage their own sites
- Test user cannot manage other users' sites
- Test viewAny allows all authenticated users
- Test guest cannot perform any actions

Create tests/Feature/DomainPolicyTest.php:
- Test user can manage domains on their sites
- Test user cannot manage domains on other sites
- Test admin can manage all domains

Include:
- Proper use statements and namespaces
- Use RefreshDatabase trait
- Create test data using factories
- Use appropriate assertions (assertEquals, assertTrue, assertDatabaseHas, etc.)
- Follow Laravel testing conventions
- Add descriptive test method names

Generate complete, runnable test code that covers all specified scenarios.
```

```

```
