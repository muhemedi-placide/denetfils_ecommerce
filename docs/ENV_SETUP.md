# Environment setup

## Etat local detecte

- PHP 8.3.10: disponible.
- Composer 2.7.8: disponible.
- Node 22.12.0: disponible.
- npm: utiliser `npm.cmd`, car `npm.ps1` est bloque par la policy PowerShell.
- Docker: non installe sur cette machine.

## Structure creee

```text
api/   Backend Laravel 11 REST API
web/   Frontend Laravel 11 + Livewire
docs/  Documentation projet
shared/ Code partage futur
```

## Variables locales

Les fichiers locaux `api/.env` et `web/.env` sont preconfigures avec SQLite pour permettre un demarrage sans MySQL ni Docker.

Le frontend lit l'API via `web/.env`:

```env
API_BASE_URL=http://127.0.0.1:8000/api/v1
```

Les fichiers `api/.env.example` et `web/.env.example` ciblent l'environnement standard du cahier des charges:

- MySQL 8 sur `127.0.0.1:3307`
- Redis sur `127.0.0.1:6379`
- Mailpit SMTP sur `127.0.0.1:1025`
- API locale sur `http://127.0.0.1:8000`
- Web local sur `http://127.0.0.1:8001`

## Demarrer en local sans Docker

Terminal 1:

```powershell
cd api
php artisan migrate
php artisan db:seed --class=EcommerceSeeder
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2:

```powershell
cd web
npm.cmd run dev
```

Terminal 3:

```powershell
cd web
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8001
```

Verifier:

- API: `http://127.0.0.1:8000/api/v1/health`
- Categories API: `http://127.0.0.1:8000/api/v1/categories?locale=fr`
- Produits API: `http://127.0.0.1:8000/api/v1/products?locale=fr`
- Produits filtres: `http://127.0.0.1:8000/api/v1/products?locale=fr&category=boissons-naturelles&q=hibiscus&sort=price_desc`
- Web: `http://127.0.0.1:8001`
- Web francais: `http://127.0.0.1:8001/fr`
- Web anglais: `http://127.0.0.1:8001/en`

## Tests et build

```powershell
cd api
php artisan test

cd ..\web
php artisan test
npm.cmd run build
```

## Demarrer avec Docker apres installation

```powershell
copy .env.example .env
docker compose up -d
```

Puis remplacer dans `api/.env` et `web/.env` les blocs DB par les valeurs MySQL des fichiers `.env.example`.

## Services externes a completer

Ajouter les vraies valeurs avant staging ou production:

- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`
- `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`
- `GA4_MEASUREMENT_ID`
- `SENTRY_LARAVEL_DSN`
- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`

## Note Horizon

Laravel Horizon demande l'extension PHP `pcntl`, absente de PHP Windows. Il faut l'ajouter sur l'environnement Linux de staging/production, par exemple Forge/Hetzner, avec:

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```
