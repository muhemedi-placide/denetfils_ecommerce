# Swagger / OpenAPI

## Local URLs

Swagger UI:

```text
http://127.0.0.1:8000/api/documentation
```

OpenAPI JSON:

```text
http://127.0.0.1:8000/docs
```

Generated file:

```text
api/storage/api-docs/api-docs.json
```

## Regenerate docs

Run this after changing routes, request schemas, resources or OpenAPI annotations:

```powershell
cd api
php artisan l5-swagger:generate
```

## Auth in Swagger UI

For protected endpoints, click `Authorize` and enter:

```text
Bearer <sanctum-token>
```

You can obtain a token with:

```text
POST /api/v1/auth/login
```

Local seeded admin:

- Email: `admin@denetfils.fr`
- Password: `password`
