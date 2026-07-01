# Localisation automatique

Le front Laravel détermine un contexte visiteur utilisé uniquement pour préremplir la langue, le pays et les estimations du panier. La commande finale recalcule toujours la TVA et la livraison depuis l’adresse de livraison confirmée.

## Configuration

Les variables sont définies dans `web/.env` :

- `VISITOR_TRUST_CLOUDFLARE_COUNTRY=true` autorise la lecture de `CF-IPCountry`. Ne l’activer que si l’origine est inaccessible hors du proxy Cloudflare et si la géolocalisation Cloudflare est activée.
- `IPINFO_TOKEN` active le repli IPinfo Lite lorsque Cloudflare ne fournit pas de pays.
- `IPINFO_TIMEOUT_SECONDS` et `IPINFO_CACHE_SECONDS` contrôlent le délai réseau et le cache.
- `VISITOR_SUPPORTED_COUNTRIES`, `VISITOR_DEFAULT_COUNTRY` et `VISITOR_DEFAULT_LOCALE` définissent les valeurs de repli du front.

Après modification de la configuration en production, exécuter `php artisan config:cache` dans `web`.

## Données et sécurité

- L’adresse IP complète n’est pas enregistrée en base par cette fonctionnalité.
- La clé de cache contient uniquement un SHA-256 de l’adresse IP.
- IPinfo reçoit l’adresse IP seulement lorsque son repli est activé.
- Les choix manuels de pays et de langue sont conservés dans des cookies Laravel chiffrés pendant 90 jours.
- Les codes Cloudflare `XX` et `T1` ne sont pas utilisés comme pays.
