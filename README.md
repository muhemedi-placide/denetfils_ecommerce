# Denetfils E-Commerce

Monorepo Laravel pour la plateforme e-commerce agroalimentaire Denetfils.

- `api/`: backend REST Laravel 11 avec catalogue produits, categories, panier invite, Sanctum, Stripe, Predis et Pulse.
- `docs/CORE_API.md`: documentation du noyau API identite, roles, permissions et base Europe/RGPD.
- `docs/SWAGGER.md`: documentation Swagger/OpenAPI locale.
- `web/`: frontend Laravel 11 avec Blade, Tailwind CSS 3.4 et Alpine.js.
- `web/lang/fr` et `web/lang/en`: traductions francais/anglais.
- `docker-compose.yml`: services MySQL 8, Redis et Mailpit pour un poste avec Docker.
- `docs/ENV_SETUP.md`: procedure de demarrage local.

## Demarrage rapide sans Docker

Terminal API:

```powershell
cd api
php artisan migrate
php artisan db:seed --class=CoreSeeder
php artisan db:seed --class=EcommerceSeeder
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal Vite:

```powershell
cd web
npm.cmd run dev
```

Terminal Web:

```powershell
cd web
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8001
```

## URLs locales

- API health: `http://127.0.0.1:8000/api/v1/health`
- Swagger UI: `http://127.0.0.1:8000/api/documentation`
- OpenAPI JSON: `http://127.0.0.1:8000/docs`
- SEO site API: `http://127.0.0.1:8000/api/v1/seo/site?locale=fr`
- Robots: `http://127.0.0.1:8000/robots.txt`
- Sitemap XML: `http://127.0.0.1:8000/sitemap.xml`
- Auth API: `http://127.0.0.1:8000/api/v1/auth/login`
- Pays supportes API: `http://127.0.0.1:8000/api/v1/supported-countries?locale=fr`
- Categories API: `http://127.0.0.1:8000/api/v1/categories?locale=fr`
- Produits API: `http://127.0.0.1:8000/api/v1/products?locale=fr`
- Produits filtres: `http://127.0.0.1:8000/api/v1/products?locale=fr&category=boissons-naturelles&q=hibiscus&sort=price_desc`
- Frontend: `http://127.0.0.1:8001`
- Francais: `http://127.0.0.1:8001/fr`
- Anglais: `http://127.0.0.1:8001/en`

## Tests

```powershell
cd api
php artisan test
php artisan l5-swagger:generate

cd ..\web
php artisan test
npm.cmd run build
```
