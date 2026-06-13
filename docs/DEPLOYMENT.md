# Deployment notes

## Cible MVP

- Runtime PHP 8.3+
- Laravel 11
- MySQL 8
- Redis
- Queue workers supervises
- HTTPS via Cloudflare ou reverse proxy

## Production checklist

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` genere par application
- `CACHE_STORE=redis`
- `QUEUE_CONNECTION=redis`
- `SESSION_SECURE_COOKIE=true`
- `SANCTUM_STATEFUL_DOMAINS` limite aux domaines de production
- Secrets Stripe/PayPal/Sentry/AWS injectes par le provider, pas commits
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`

## Horizon

Installer Horizon uniquement sur Linux, car Windows ne fournit pas `pcntl`.

## Security audit note

`composer audit` signale actuellement `CVE-2026-48019` sur `laravel/framework` 11.x. Le cahier des charges demande Laravel 11, donc le setup respecte cette contrainte. Avant staging/production, valider avec l'equipe si le projet doit rester en Laravel 11 avec mitigation applicative, ou passer a Laravel 12/13 pour recuperer le correctif upstream.
