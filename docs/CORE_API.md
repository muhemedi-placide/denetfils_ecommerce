# Backend API Core

## Objectif

Le module core API pose la base identite, acces et conformite avant le checkout:

- Authentification Sanctum.
- Utilisateurs staff et clients.
- Roles et permissions via `spatie/laravel-permission`.
- Profils client/staff.
- Adresses billing/shipping normalisees pour usage TVA/livraison.
- Pays europeens supportes.
- Consentements RGPD versionnes.
- Audit log des actions admin sensibles.

## Seed local

```powershell
cd api
php artisan migrate
php artisan db:seed --class=CoreSeeder
```

Compte super-admin local:

- Email: `admin@denetfils.fr`
- Mot de passe: `password`

## Endpoints publics

```text
POST /api/v1/auth/register
POST /api/v1/auth/login
GET  /api/v1/supported-countries?locale=fr
GET  /api/v1/privacy/consents/current
```

## Endpoints authentifies

```text
POST   /api/v1/auth/logout
GET    /api/v1/auth/me
GET    /api/v1/me
PATCH  /api/v1/me
GET    /api/v1/me/addresses
POST   /api/v1/me/addresses
PATCH  /api/v1/me/addresses/{address}
DELETE /api/v1/me/addresses/{address}
```

## Endpoints admin

Toutes les routes admin demandent `auth:sanctum`, un compte actif et la permission adaptee.

```text
GET   /api/v1/admin/users
POST  /api/v1/admin/users
GET   /api/v1/admin/users/{user}
PATCH /api/v1/admin/users/{user}
POST  /api/v1/admin/users/{user}/roles
POST  /api/v1/admin/users/{user}/suspend
GET   /api/v1/admin/roles
GET   /api/v1/admin/permissions
GET   /api/v1/admin/audit-logs
```

## Roles initiaux

```text
super_admin
admin
operations_manager
catalog_manager
support_agent
finance_manager
customer
```

## Notes Europe/RGPD

- Pays stockes en ISO-2.
- Locale par defaut: `fr`; locale secondaire: `en`.
- Timezone par defaut: `Europe/Paris`.
- Consentements: `privacy_policy`, `terms`, `marketing_email`.
- Les actions admin critiques creent une entree dans `audit_logs`.
- Le statut `deleted_pending` et les soft deletes preparent le droit a l'oubli sans supprimer immediatement les donnees necessaires aux obligations legales futures.
