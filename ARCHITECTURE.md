# Architecture CNDT — Frontend / Backend / Dashboard

> Vue d'ensemble de l'architecture complète du projet (27/10/2026).

## Diagramme général

```
                       NAVIGATEUR
        ┌─────────────────────────────────────────────┐
        │                                             │
        │   Site vitrine (public)     Dashboard admin │
        │   Site vitrine Blade      Dashboard admin │
        │   (HTML/CSS/JS statique)    VITRINE_2/admin  │
        │   :8080                    (SPA vanilla JS) │
        │        │                        :8080/admin  │
        │        │  fetch()                    │      │
        │        │  (CORS autorisé)            │      │
        └────────┼────────────────────────────┼──────┘
                 │                            │
                 ▼                            ▼
        ┌─────────────────────────────────────────────┐
        │        API REST  Laravel 13 (cndt-api)       │
        │        http://localhost:8000/api/v1/...      │
        │                                             │
        │  Routes publiques ──► contrôleurs ──► modèles│
        │  Routes admin     ──► auth:sanctum           │
        │                       + middleware admin    │
        └──────────────────────┬──────────────────────┘
                               │  Eloquent (pdo_mysql)
                               ▼
                     ┌───────────────────┐
                     │  MySQL 8 (cndt_api)│
                     └───────────────────┘
```

Les quatre services tournent dans Docker (`docker-compose.yml`, secrets
dans `.env` — voir `.env.example`, jamais versionné) :
`cndt-frontend` (httpd:2.4-alpine, **reverse proxy complet** vers l'application
Laravel — site Blade, dashboard `/admin/`, API `/api/v1`, stockage `/storage/` —
127.0.0.1:8080),
`cndt-api` (php:8.3-apache, interne — 127.0.0.1:8000 en dev uniquement),
`cndt-mysql` (mysql:8.4 LTS, volume persistant `mysql-data`) et
`cndt-backup` (dump gzippé quotidien dans `./backups`, rétention 14 jours).

**Sécurité** : en base (compose), `APP_ENV=production`, `APP_DEBUG=false`,
ports liés à 127.0.0.1, aucun secret en clair (tout vient de `.env`),
et l'API n'est PAS re-seedée au démarrage (le seed est opt-in via
`DB_SEED=true`, uniquement en dev — `docker-compose.override.yml`) ;
le seeder est non-destructif (`firstOrCreate` : n'écrase jamais le
contenu modifié dans le dashboard) et le mot de passe admin vient de
`ADMIN_PASSWORD`. Les adresses d'API sont **relatives** (`/api/v1`)
partout : site public (`app.js`, `api-actualites.js`), dashboard
compilé (`admin/`) — servies par le frontal qui proxifie vers l'API.

## 1. Frontend public — site vitrine (Blade)

**Refonte** : le site vitrine est servi par l'application Laravel (`cndt-api`).

- **Vues** : `cndt-api/resources/views/pages/*.blade.php` (19 vues — une par
  gabarit de page ; tous les articles et fiches individuelles sont désormais
  **gérés en base de données** et rendus par les gabarits dynamiques
  `/article/{slug}` et `/membre/{slug}`) + `layouts/site.blade.php`
  (header et footer **rendus côté serveur**).
- **URLs historiques conservées** (`/`, `/galerie.html`, `/actualite/article.html`,
  `/membres/{page}.html`, …) : `routes/web.php` — tous les liens internes
  existants fonctionnent à l'identique.
- **Assets** : `cndt-api/public/` (`site.css`, `app.js`, `api-actualites.js`,
  `assets/`) ; `app.js` n'injecte plus header/footer si déjà rendus (garde).
- Le contenu dynamique reste alimenté côté client par `api-actualites.js`
  (actualités, hero, instituts, galerie, personnel, fiches, projets).
- Formulaire de contact : `POST /api/v1/contact`.
- L'ancien dossier statique `CNDT_SITE_VITRINE_2/` a été **retiré** du dépôt ;
  la source du dashboard vit désormais à la racine : `dashboard/`.

## 2. Frontend admin — dashboard (React + Tailwind + shadcn/ui)

- **Source** : `dashboard/` (à la racine du dépôt — Vite + React 19 + TypeScript)
- **Build déployé** : `cndt-api/public/admin/` — même URL : http://localhost:8080/admin/
  (l'ancien `admin.html` à la racine redirige vers `admin/`)
- Stack : **React 19**, **Tailwind CSS v4**, **shadcn/ui** (composants Radix UI), lucide-react,
  sonner (toasts), routage par hash (`#overview`, `#articles`, …) — aucun serveur de rewrite requis.
- **Authentification** : `POST /auth/login` → token Sanctum en `sessionStorage`
  (jamais `localStorage`), restauré au rechargement via `GET /auth/me` ;
  401 → retour à l'écran de connexion. Le HTML des contenus est nettoyé
  côté API (`App\Support\HtmlSanitizer`) avant stockage.

### Organisation du code
```
dashboard/src/
├── lib/api.ts          Client fetch (token, erreurs) + types du contenu
├── lib/entities.tsx     Configuration générique des entités CRUD
├── hooks/useHashRoute.ts Routeur par hash
├── components/
│   ├── ui/              Composants shadcn (button, card, dialog, table…)
│   ├── LoginPage.tsx    Écran de connexion
│   ├── Layout.tsx       Sidebar + topbar
│   └── EntityFormDialog.tsx  Modale de formulaire générique
└── pages/
    ├── OverviewPage.tsx     Cartes de stats + derniers messages
    ├── EntityPage.tsx       CRUD générique + gestion des photos (galerie)
    ├── MessagesPage.tsx     Messages : non lus, marquer lu, suppression
    └── HeroCarouselPage.tsx Carrousel de l'accueil (recherche d'articles)
```

### Développement et déploiement
```bash
cd dashboard
npm run dev      # serveur de dev Vite (hot reload)
npm run build    # build de production dans dist/
# déployer : copier dist/* dans ../admin/
```

### Sections du dashboard (identiques à l'API)
| Section | Endpoints consommés | Fonctions |
|---------|---------------------|-----------|
| Vue d'ensemble | `GET /stats`, `GET /messages` | Cartes de compteurs + 5 derniers messages |
| Actualités | `/articles` (CRUD) | Liste, recherche, publier/dépublier |
| Publications | `/publications` (CRUD) | Types : rapport, communiqué, revue, discours |
| Galerie | `/albums` (CRUD) + `/albums/{slug}/photos` | Albums + gestion des photos par album |
| Personnel | `/members` (CRUD) | Fiches, visibilité, chapitre, position |
| Projets | `/projects` (CRUD) | Statuts : à venir, en cours, terminé |
| Messages | `/messages` | Badge non lus, marquer lu, suppression |
| Organigramme | `/organigramme` + `/organizational-units` | Arbre, ajout d'enfants, édition |

Le CRUD est **générique** : chaque entité est déclarée dans `lib/entities.tsx`
(colonnes du tableau + champs du formulaire) ; la modale `EntityFormDialog` et la
confirmation de suppression sont communes. Les données non publiées sont
incluses via `?all=true`.

## 3. Backend — API Laravel

- **Emplacement** : `cndt-api/` (Laravel 13, PHP 8.3)
- **Base** : préfixe `/api/v1`, rendu JSON forcé pour `api/*`
- **Couche contrôleur** : `app/Http/Controllers/Api/*Controller.php`
- **Couche transformation** : `app/Http/Resources/*Resource.php` (JSON normalisé, ISO 8601)
- **Couche modèles** : `app/Models/*` (Eloquent, slugs, scopes `published()`/`visible()`)
- **Sécurité** :
  - `Authenticate` (custom) → 401 JSON, pas de redirection web ;
  - `EnsureUserIsAdmin` → 403 si `is_admin` est faux ;
  - throttle `login` (5 tentatives/min par IP+email) ;
  - tokens Sanctum expirant après 7 jours, révoqués à chaque reconnexion.
- **Nouveau** : `GET /stats` (compteurs du dashboard) — `StatsController`.

## 4. Données

| Table | Contenu |
|-------|---------|
| `articles` | Actualités |
| `publications` | Rapports, communiqués, revues |
| `albums` + `photos` | Galerie |
| `members` | Personnel |
| `projects` | Projets |
| `messages` | Contact |
| `organizational_units` | Organigramme (auto-référencé) |
| `users` + `personal_access_tokens` | Administration |

## 5. Docker — organisation des services

```yaml
cndt-mysql    3306 interne      mysql:8.4 LTS, volume mysql-data, healthcheck
cndt-api      80 interne        (dev : 127.0.0.1:8000) ./cndt-api monté en volume
cndt-frontend 127.0.0.1:8080    reverse proxy complet → cndt-api (Laravel : Blade + /admin + /api + /storage)
cndt-backup   —                 mysqldump quotidien → ./backups (rétention 14 j)
```

Tous les secrets viennent du fichier `.env` (voir `.env.example`) :
`APP_KEY`, `MYSQL_ROOT_PASSWORD`, `MYSQL_PASSWORD`, `ADMIN_PASSWORD`,
`MAIL_*`. En dev uniquement (`docker-compose.override.yml`, chargé
automatiquement) : code monté en volume, `APP_DEBUG=true`, `DB_SEED=true`
(seeder non-destructif), port direct 127.0.0.1:8000.

Le code de l'API est **monté en volume** (dev) : toute modification du code est prise en
compte sans rebuild de l'image (redémarrage du conteneur requis pour le cache de
routes/config s'il y en avait). Reconstruire avec `docker compose up -d --build api`
uniquement si `composer.json` ou le `Dockerfile` changent.

## 6. Démarrage complet

```bash
cd CNDT_SITE_BACKEND
docker compose up -d --build
# Site public    : http://localhost:8080          (API via /api/v1, proxy intégré)
# Dashboard      : http://localhost:8080/admin/    (compte créé au premier seed :
#                                                  admin@cndt.cm / ADMIN_PASSWORD du .env)
# API (dev)      : http://localhost:8000/api/v1/articles
# Sauvegardes    : ./backups/ (dump quotidien, rétention 14 jours)
```

Accès alternatif via XAMPP (Apache sur le port 80) :
`http://localhost/cndt/…` (junction `htdocs/cndt`) — les origins sont
autorisées dans `cndt-api/config/cors.php`.

## 7. Flux d'authentification

```
Dashboard                API                          BD
   │  POST /auth/login    │                            │
   │ ────────────────────►│  vérifie credentials       │
   │                      │  Hash::check(password)    │
   │   {token, user}      │  crée personal_access_token
   │ ◄────────────────────│                            │
   │ sessionStorage[token]│                            │
   │                      │                            │
   │  GET /articles       │                            │
   │  Authorization: Bearer … ───► middleware sanctum │
   │                      │  + middleware admin       │
   │   JSON (data/meta)   │                            │
   │ ◄────────────────────│                            │
```

## 8. Prochaines étapes

1. Double authentification (2FA) pour le compte administrateur.
2. Rôles rédacteur / valideur (workflow de publication prévu par le TDR).
3. HTTPS en production (reverse proxy devant 127.0.0.1:8080) + SMTP réel
   pour les alertes du formulaire de contact (`MAIL_ALERT_TO`).

---

## 9. Actualités dynamiques (public)

La page `actualites.html`, le widget d'accueil et la page générique
`actualite/article.html` consomment l'API via **`api-actualites.js`** (inclus après
`app.js`) :

```
Dashboard admin (React)  ──POST/PUT/DELETE──►  API /api/v1/articles  ──GET──►  Pages publiques
        │                                              │                          │
        └──────── contenu géré sans toucher le code ────┴──── api-actualites.js ───┘
```

- Liste : 6 articles/page, « Charger plus », filtres (`?category=ia|evenement|formation`)
- Accueil : 4 derniers titres dans l'agenda (secours statique si l'API est indisponible)
- Article : `actualite/article.html?slug=<slug>` — image, date, contenu, `<title>`
