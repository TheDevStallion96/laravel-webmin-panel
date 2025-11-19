# Phase 3 — Site Management (MVP Core) (Weeks 2–3)

## Objectives
- CRUD for Sites; Create Site Wizard; .env management; root path and public dir validation; pause/resume and delete.

## Deliverables
- Blade views: sites/index, sites/create-wizard, sites/show, sites/settings.
- Actions for provisioning directory structure (local or remote) and writing .env.

## Implementation
### Data and model updates
- sites: add public_dir (default "public"), environment (json for key/val), deploy_strategy [basic|zero_downtime].

### Routes and controllers
- routes/web.php
  - GET /sites -> SitesController@index
  - GET /sites/create -> SitesController@create
  - POST /sites -> SitesController@store
  - GET /sites/{site} -> SitesController@show
  - PUT /sites/{site} -> SitesController@update
  - DELETE /sites/{site} -> SitesController@destroy
  - POST /sites/{site}/pause -> SitesPauseController@store
  - POST /sites/{site}/resume -> SitesResumeController@store

### Form requests and policies
- StoreSiteRequest, UpdateSiteRequest (validate unique slug, path ownership, php_version whitelist).
- Authorize via SitePolicy.

### Services and actions
- app/Actions/Sites/ProvisionSite.php — create directories, write .env, initialize git (optional).
- app/Actions/Sites/WriteEnv.php — renders .env from stored environment JSON.
- app/Services/FilesystemService.php — safe FS ops; dry-run support for tests.

### UI and UX
- Blade views enhanced with Alpine.js step wizard: Basics -> PHP -> Database -> Repository -> Review.
- Blade components/partials: EnvEditor, PathPicker, PhpVersionSelector.

## Testing
- Feature tests for Site CRUD and wizard; unit tests for env rendering.

## Acceptance
- Can create, pause, resume, delete site; env managed; audit records written.
