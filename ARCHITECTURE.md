# Architecture CNDT — Frontend / Backend / Dashboard

> Vue d'ensemble de l'architecture complète du projet (27/10/2026).

## Diagramme général

```
                       NAVIGATEUR
        ┌─────────────────────────────────────────────┐
        │                                             │
        │   Site vitrine (public)     Dashboard admin │
        │   CNDT_SITE_VITRINE_2       CNDT_SITE_      │
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

Les trois services tournent dans Docker (`docker-compose.yml`) :
`cndt-frontend` (httpd:2.4-alpine, port 8080), `cndt-api` (php:8.3-apache, port 8000),
`cndt-mysql` (mysql:8.0, volume persistant `mysql-data`).

## 1. Frontend public — site vitrine

- **Emplacement** : `CNDT_SITE_VITRINE_2/` (à la racine)
- HTML/CSS/JS purs sans build ; header/footer injectés par `app.js`.
- Actuellement **statique** ; prévu pour consommer l'API via `fetch()`
  (ex. `GET /api/v1/articles`, `/api/v1/organigramme`).
- Formulaire de contact : `POST /api/v1/contact`.

## 2. Frontend admin — dashboard (React + Tailwind + shadcn/ui)

- **Source** : `CNDT_SITE_VITRINE_2/dashboard/` (Vite + React 19 + TypeScript)
- **Build déployé** : `CNDT_SITE_VITRINE_2/admin/` — même URL : http://localhost:8080/admin/
  (l'ancien `admin.html` à la racine redirige vers `admin/`)
- Stack : **React 19**, **Tailwind CSS v4**, **shadcn/ui** (composants Radix UI), lucide-react,
  sonner (toasts), routage par hash (`#overview`, `#articles`, …) — aucun serveur de rewrite requis.
- **Authentification** : `POST /auth/login` → token Sanctum en `localStorage`, restauré au
  rechargement via `GET /auth/me` ; 401 → retour à l'écran de connexion.

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
    └── OrganigrammePage.tsx Arbre des unités + CRUD
```

### Développement et déploiement
```bash
cd CNDT_SITE_VITRINE_2/dashboard
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
cndt-mysql   :3306 interne   volume mysql-data, healthcheck
cndt-api     8000→80         ./cndt-api monté en volume (dev live, sans rebuild)
cndt-frontend 8080→80        ../CNDT_SITE_VITRINE_2 monté en lecture seule
```

Variables d'environnement (compose) : `DB_HOST=mysql`, `DB_DATABASE=cndt_api`,
`DB_USERNAME=cndt`, `DB_PASSWORD=cndt_secret`, `SANCTUM_TOKEN_EXPIRATION=10080`.

Le code de l'API est **monté en volume** : toute modification du code est prise en
compte sans rebuild de l'image (redémarrage du conteneur requis pour le cache de
routes/config s'il y en avait). Reconstruire avec `docker compose up -d --build api`
uniquement si `composer.json` ou le `Dockerfile` changent.

## 6. Démarrage complet

```bash
cd CNDT_SITE_BACKEND
docker compose up -d --build
# Site public    : http://localhost:8080
# Dashboard      : http://localhost:8080/admin/   (admin@cndt.cm / password)
# API            : http://localhost:8000/api/v1/articles
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
   │  localStorage[token] │                            │
   │                      │                            │
   │  GET /articles       │                            │
   │  Authorization: Bearer … ───► middleware sanctum │
   │                      │  + middleware admin       │
   │   JSON (data/meta)   │                            │
   │ ◄────────────────────│                            │
```

## 8. Prochaines étapes

1. Brancher les pages publiques du site vitrine sur l'API (actualités, organigramme…).
2. Upload de fichiers réel (images de couverture, photos) via multipart.
3. HTTPS + mots de passe forts en production.

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
