# Phase 13 — Optional: Docker mode and Teams (Weeks 12–13)

## Docker mode
- Generate docker-compose.yml per site with services: app, queue, scheduler, nginx, db (optional).
- Map volumes to /srv/sites/{slug}.

## Teams and permissions
- SiteMemberships table linking users to sites with role [owner|maintainer|viewer].
- Policies consult membership when role != admin.
