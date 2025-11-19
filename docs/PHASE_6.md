# Phase 6 — Domains and SSL (Weeks 5–6)

## Objectives
- Map hostnames to sites; provision SSL via Let’s Encrypt (ACME); support custom/self-signed certs; force HTTPS.

## Deliverables
- Blade view: domains/index; toggle primary; toggle HTTPS forced.
- Certificates stored under storage/app/panel/certs/{site_slug}.

## Implementation
1) Domain CRUD endpoints; validation for FQDN/wildcards; ensure uniqueness per site.
2) ACME client integration (e.g., certbot shell via Shell wrapper) with HTTP-01 challenges via Nginx.
3) Renew cron (Supervisor or system cron) and status tracking; emit notifications on failure.
4) Update Nginx template to include ssl_certificate/ssl_certificate_key when present; set 301 http->https when forced.

## Testing
- Unit tests for domain validation; fake ACME calls; feature tests create cert and update vhost content.

## Acceptance
- Provisioning cert writes files, updates vhost, reloads server, and records expiry date.
