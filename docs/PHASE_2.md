# Phase 2 — Authentication and RBAC (Week 1)

## Objectives
- Multi-user auth with roles: admin, developer, viewer; API tokens for CLI.

## Deliverables
- User roles/abilities; policies enforced in controllers; Sanctum tokens if CLI required.

## Implementation
1) Add role column to users with enum [admin, developer, viewer].
2) Define Gates: manage-server (admin), manage-site (admin|developer), view-site (all).
3) Apply middleware in routes; ensure auth and role data are shared to Blade views (View::share or view composers) and exposed to Alpine.js as needed.
4) If CLI planned: install Sanctum and expose token management UI.

## Testing
- Feature tests for role access to site CRUD and sensitive endpoints.

## Acceptance
- Viewer cannot mutate; developer can manage assigned sites; admin is superuser.

---
These are the prompts described by the prompt decomposition agent for Phase 2:

Prompt One:
```
Create a Laravel migration to add role-based access control to the users table. Add a 'role' column with enum values: 'admin', 'developer', 'viewer'. Set 'viewer' as the default role. Then update the User model to include this field in $fillable and add a cast for the enum. Create a seeder that generates at least one user for each role (admin, developer, viewer) for testing purposes. Provide the complete migration file, updated User model code, and seeder code.
```

Prompt Two:
```
Define Laravel Gates in the AuthServiceProvider for the following permissions:
1. 'manage-server': Only admin role can perform server management operations
2. 'manage-site': Both admin and developer roles can manage sites
3. 'view-site': All roles (admin, developer, viewer) can view sites

Write the complete Gate definitions with clear logic. Also create helper methods on the User model: isAdmin(), isDeveloper(), and isViewer() that return boolean values. Provide the complete AuthServiceProvider code and the updated User model with these helper methods.
```

Prompt Three:
```
Implement route protection using the Gates defined in the previous step. Show how to:
1. Apply 'can:manage-server' middleware to admin-only routes
2. Apply 'can:manage-site' middleware to routes for site management (admin and developer)
3. Apply 'can:view-site' middleware to view-only routes

Then add a global view share so all Blade views have access to the authenticated user and their role (e.g., in AppServiceProvider boot() using View::share or a dedicated middleware). Provide example route definitions (web.php), the provider or middleware code that shares auth/role data, and a sample Blade view that reads those values (optionally using Alpine.js for interactivity).
```

Prompt Four:
```
Implement Laravel Sanctum for API token authentication to support CLI access. Provide:
1. Installation steps and configuration (sanctum.php config if customization needed)
2. Migration for personal access tokens table
3. API routes for token management (create, list, revoke tokens)
4. A simple Blade page (with Alpine.js) for users to manage their API tokens (create new tokens, view existing tokens with abilities, revoke tokens)
5. Example of how to authenticate API requests using the token

Ensure tokens respect the same role-based permissions defined in the Gates. Include code for: API routes, TokenController, and the Blade view for token management UI.
```

Prompt Five:
```
Create a comprehensive Laravel feature test suite for the RBAC system. Write tests for:

1. **Viewer Role Tests:**
   - Can view sites
   - Cannot create, update, or delete sites
   - Cannot access admin/server management endpoints

2. **Developer Role Tests:**
   - Can view sites
   - Can create, update, and delete sites they manage
   - Cannot access admin/server management endpoints

3. **Admin Role Tests:**
   - Can perform all site operations
   - Can access server management endpoints
   - Has superuser privileges

4. **API Token Tests (if Sanctum implemented):**
   - Tokens work for authentication
   - Token permissions match user role

Include setup/teardown methods, use appropriate HTTP status code assertions (200, 403, 422), and test both successful and forbidden access scenarios. Provide the complete test file(s) with clear test method names and assertions.
```
