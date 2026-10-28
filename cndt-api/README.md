# CNDT API — Backend Laravel

API REST du site vitrine du CNDT. Génée avec **Laravel 13** (PHP 8.3), authentification par tokens **Sanctum**.

## Démarrage

```bash
cd cndt-api
composer install
cp .env.example .env          # si besoin
php artisan key:generate
php artisan migrate --seed    # crée les tables + contenu de démonstration
php artisan storage:link      # accès public aux fichiers (images, documents)
php artisan serve             # API disponible sur http://127.0.0.1:8000
```

Base par défaut : **SQLite** (`database/database.sqlite`) — zéro configuration.

### Passer à MySQL (XAMPP)

Démarrer MySQL puis créer la base :

```sql
CREATE DATABASE cndt_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Puis dans `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cndt_api
DB_USERNAME=root
DB_PASSWORD=
```

Relancer `php artisan migrate --seed`.

## Compte admin de démonstration

| Email            | Mot de passe |
|------------------|--------------|
| `admin@cndt.cm`  | `password`   |

> À changer avant toute mise en production.

## Authentification (Sanctum)

- Les routes admin exigent un token **et** le rôle admin (`is_admin = 1`) → 401 sans token, 403 pour un utilisateur non admin.
- Les tokens expirent après **7 jours** (`SANCTUM_TOKEN_EXPIRATION` en minutes dans `.env`).
- Chaque nouvelle connexion révoque les anciens tokens de l'utilisateur.
- Le login est limité à **5 tentatives par minute** (IP + email) → HTTP 429 au-delà.

```bash
# Connexion → récupérer un token
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@cndt.cm","password":"password"}'

# Utiliser le token pour les routes admin
curl http://127.0.0.1:8000/api/v1/auth/me -H "Authorization: Bearer <token>"
```

## Endpoints (préfixe `/api/v1`)

### Publics
| Méthode | URI | Description |
|---------|-----|-------------|
| GET  | `/articles` | Actualités publiées (`?search=`, `?category=`, `?per_page=`) |
| GET  | `/articles/{slug}` | Détail d'une actualité |
| GET  | `/publications` | Publications (`?type=rapport\|communique\|revue...`) |
| GET  | `/publications/{slug}` | Détail d'une publication |
| GET  | `/albums` | Albums photo (avec photos) |
| GET  | `/albums/{slug}` | Détail d'un album |
| GET  | `/members` | Personnel visible (`?search=`, `?unit=`, `?chapter=`) |
| GET  | `/members/{slug}` | Fiche d'un membre |
| GET  | `/projects` | Projets publiés (`?status=a_venir\|en_cours\|termine`) |
| GET  | `/projects/{slug}` | Détail d'un projet |
| GET  | `/organigramme` | Arbre des unités organisationnelles (chapitres I/II/III) |
| POST | `/contact` | Formulaire de contact (name, email, message) |

### Administration (en-tête `Authorization: Bearer <token>`)
| Méthode | URI | Description |
|---------|-----|-------------|
| GET  | `/auth/me` | Utilisateur courant |
| POST | `/auth/logout` | Révoque le token courant |
| POST/PUT/DELETE | `/articles`, `/publications`, `/albums`, `/members`, `/projects` | CRUD complet |
| GET/POST/PUT/DELETE | `/albums/{album}/photos[/{photo}]` | Gestion des photos |
| POST/PUT/DELETE | `/organizational-units[/{id}]` | Gestion de l'organigramme |
| GET  | `/messages` | Messages reçus (`?unread=true`) |
| GET/PUT/DELETE | `/messages/{id}` | Lire (marque lu) / supprimer |

Les listes sont paginées au format standard Laravel (`data`, `links`, `meta`).
Ajouter `?all=true` sur les listes pour inclure les éléments non publiés (admin).

## Structure du contenu

| Table | Contenu |
|-------|---------|
| `articles` | Actualités (ex-`actualite/`) |
| `publications` | Publications & médias (ex-`publication.html`) |
| `albums` + `photos` | Galerie (ex-`galerie.html`) |
| `members` | Personnel & membres (ex-`personnel.html`, `membres/`) |
| `projects` | Projets (ex-`projets/`) |
| `messages` | Formulaire de contact (ex-`contact.html`) |
| `organizational_units` | Organigramme hiérarchique (ex-`organisation-data.js`) |

## CORS

Origins autorisés pour le frontend : `http://localhost[:8080|:5500]` et équivalents en `127.0.0.1`
(`config/cors.php`). À adapter au domaine de production.

## Frontend

Côté site vitrine (`CNDT_SITE_VITRINE_2`), remplacer les données statiques par des appels `fetch()` vers
l'API, par exemple :

```js
const res = await fetch('http://127.0.0.1:8000/api/v1/articles?per_page=7');
const { data: articles } = await res.json();
```

## Tests rapides

```bash
curl http://127.0.0.1:8000/api/v1/articles
curl http://127.0.0.1:8000/api/v1/organigramme
curl -X POST http://127.0.0.1:8000/api/v1/contact -H "Content-Type: application/json" \
  -d '{"name":"Nom","email":"a@b.cm","message":"Bonjour"}'
```
