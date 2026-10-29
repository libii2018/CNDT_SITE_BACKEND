# CNDT — Journal de mise en œuvre du backend

> Document récapitulatif de tout le travail effectué sur l'API du site vitrine CNDT.
> Date : 27/10/2026

---

## 1. Contexte et objectif

Le site vitrine CNDT (`CNDT_SITE_VITRINE_2`) est un site statique (HTML/CSS/JS purs).
Objectif : créer un **backend Laravel API** pour rendre le site dynamique — gestion des
actualités, publications, galerie, personnel, projets, messages de contact et organigramme.

- **Emplacement du backend** : `CNDT_SITE_BACKEND/cndt-api`
- **Technologies** : Laravel 13.38, PHP 8.3 (XAMPP), Sanctum (authentification par tokens), MySQL
- **Préfixe des routes** : `/api/v1`

---

## 2. Installation du projet

1. `composer create-project laravel/laravel cndt-api` — projet Laravel 13.38 créé.
2. `php artisan install:api` — scaffolding API : publication de `routes/api.php`,
   installation de **Laravel Sanctum** + migration `personal_access_tokens`.
3. `php artisan storage:link` — accès public aux fichiers uploadés (`public/storage` → `storage/app/public`).
4. `.env` : `APP_NAME="CNDT API"`, `APP_LOCALE=fr`, `APP_FAKER_LOCALE=fr_FR`.
5. `bootstrap/app.php` : préfixe API défini sur `api/v1` (`apiPrefix: 'api/v1'`)
   + rendu JSON forcé pour les requêtes `api/*`.

---

## 3. Base de données

### 3.1 SQLite → MySQL

- Développement commencé sur SQLite (zéro config), puis bascule vers MySQL une fois XAMPP démarré.
- Base **`cndt_api`** créée (utf8mb4_unicode_ci) via le client MySQL de XAMPP.
- `.env` configuré : `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`,
  `DB_DATABASE=cndt_api`, `DB_USERNAME=root`, `DB_PASSWORD=` (vide).
- `php artisan migrate:fresh --seed` exécuté avec succès sur MySQL.

### 3.2 Tables métier créées (migrations)

| Table | Contenu (correspondance site statique) |
|-------|----------------------------------------|
| `articles` | Actualités (`actualites.html`, dossier `actualite/`) |
| `publications` | Publications & médias (`publication.html`) |
| `albums` + `photos` | Galerie photo (`galerie.html`) |
| `members` | Personnel / membres (`personnel.html`, dossier `membres/`) |
| `projects` | Projets (`projets/`) |
| `messages` | Formulaire de contact (`contact.html`) |
| `organizational_units` | Organigramme hiérarchique auto-référencé (`organisation-data.js`) |
| `users` (+ `is_admin`) | Comptes d'administration |
| `personal_access_tokens` | Tokens Sanctum |

Colonnes notables : slugs uniques partout, `is_published` / `is_visible` (booléens indexés),
`published_at` (indexé), `position` (ordre d'affichage), `parent_id` auto-référencé pour
l'organigramme, clés étrangères avec `cascadeOnDelete` / `nullOnDelete`.

---

## 4. Modèles (`app/Models`)

- `Article`, `Publication`, `Album`, `Photo`, `Member`, `Project`, `Message`, `OrganizationalUnit`
- Liaison par **slug** (`getRouteKeyName()`) pour Article, Publication, Album, Member, Project.
- Scopes : `published()` (publiés + date atteinte), `visible()`.
- Relations : `Album hasMany Photo`, `Photo belongsTo Album`, `OrganizationalUnit hasMany children`.
- `User` : trait `HasApiTokens` (Sanctum), attribut `is_admin` (fillable + cast booléen).

---

## 5. API — Contrôleurs et Resources

### 5.1 Contrôleurs (`app/Http/Controllers/Api`)

| Contrôleur | Endpoints |
|------------|-----------|
| `AuthController` | login / me / logout |
| `ArticleController` | CRUD actualités (index paginé + filtres `search`, `category`, `all`) |
| `PublicationController` | CRUD publications (filtre `type`) |
| `AlbumController` | CRUD albums (photos chargées) |
| `PhotoController` | CRUD photos imbriquées sous un album |
| `MemberController` | CRUD personnel (filtres `search`, `unit`, `chapter`, `all`) |
| `ProjectController` | CRUD projets (filtre `status`) |
| `MessageController` | store public (contact) + liste/lecture/suppression admin |
| `OrganizationalUnitController` | arbre complet (`/organigramme`) + CRUD admin |

Fonctionnalités intégrées :
- Validation inline (`$request->validate`) sur chaque store/update ;
- Génération automatique de **slug unique** depuis le titre (`Str::slug` + dédoublonnage) ;
- Pagination standard Laravel (`data`, `links`, `meta`) avec `?per_page=` ;
- Masquage des brouillons/non publiés côté public (404 si non publié et non admin) ;
- Vérification d'unicité et de format (email, dates, statuts) ;
- `published_at` et `position` par défaut automatiques.

### 5.2 API Resources (`app/Http/Resources`)

`ArticleResource`, `PublicationResource`, `AlbumResource`, `PhotoResource`, `MemberResource`,
`ProjectResource`, `MessageResource`, `OrganizationalUnitResource` — réponses JSON propres :
dates ISO 8601, champs lourds (`content`, `biography`, `description`) réservés aux vues détail,
coordonnées (email/téléphone) masquées dans les listes, arbre récursif pour l'organigramme,
`full_name` calculé pour les membres.

---

## 6. Routes (`routes/api.php`)

### Publics (sans authentification)
| Méthode | URI |
|---------|-----|
| POST | `/api/v1/auth/login` (throttle) |
| GET | `/api/v1/articles` · `/articles/{slug}` |
| GET | `/api/v1/publications` · `/publications/{slug}` |
| GET | `/api/v1/albums` · `/albums/{slug}` |
| GET | `/api/v1/members` · `/members/{slug}` |
| GET | `/api/v1/projects` · `/projects/{slug}` |
| GET | `/api/v1/organigramme` |
| POST | `/api/v1/contact` |

### Administration (`auth:sanctum` + `admin`)
- `GET /auth/me`, `POST /auth/logout`
- CRUD : `articles`, `publications`, `albums`, `members`, `projects`
- Photos : `GET|POST /albums/{album}/photos`, `PUT|DELETE /albums/{album}/photos/{photo}`
- Organigramme : `POST|PUT|DELETE /organizational-units[/{id}]`
- Messages : `GET /messages` (filtre `unread`), `GET|PUT|DELETE /messages/{id}`

---

## 7. Authentification Sanctum (durcie)

1. **Middleware custom `Authenticate`** (`app/Http/Middleware/Authenticate.php`) :
   `redirectTo()` retourne `null` → l'API pure n'exige pas de route web `login` ;
   les non-authentifiés reçoivent un **401 JSON** propre au lieu d'une erreur 500.
   Alias `auth` déclaré dans `bootstrap/app.php`.
2. **Middleware `EnsureUserIsAdmin`** : vérifie `is_admin` → **403** pour un utilisateur
   authentifié mais non admin. Appliqué à tout le groupe admin.
3. **Expiration des tokens** : 7 jours par défaut
   (`SANCTUM_TOKEN_EXPIRATION` en minutes dans `.env` / `.env.example`).
4. **Rate limiting du login** : limiteur `login` (5/min par IP + email) déclaré dans
   `AppServiceProvider` + `throttle:login` sur la route → **429** au-delà.
5. **Révocation à la connexion** : chaque login supprime les anciens tokens de l'utilisateur.

---

## 8. CORS (`config/cors.php`)

Origins autorisés pour le frontend local :
`http://localhost[:8080|:5500]` et équivalents `127.0.0.1` — à adapter en production.

---

## 9. Seeders (`database/seeders/DatabaseSeeder.php`)

Idempotents (`updateOrCreate`) :
- **Compte admin** : `admin@cndt.cm` / `password` (à changer en production)
- 3 actualités de démonstration (TIC & IA, séminaire, partenariat)
- 3 publications (rapport, communiqué, revue)
- 4 membres du personnel (Secrétaire Permanent, Cellule Informatique, CRMP, Commission Technique)
- 1 projet (stratégie nationale TIC & IA)
- 1 album « Session ordinaire » avec 2 photos
- Organigramme complet : chapitres I / II / III, 7 unités du Cabinet, 4 commissions,
  3 secrétariats techniques (17 unités au total)
- 1 message de contact d'exemple

---

## 10. Tests réalisés (tous validés)

| Test | Résultat |
|------|----------|
| `php artisan migrate:fresh --seed` (SQLite puis MySQL) | OK |
| `GET /api/v1/articles` (pagination, JSON) | OK |
| `GET /api/v1/organigramme` (arbre chapitres/enfants) | OK |
| `POST /api/v1/contact` (formulaire) | OK |
| `POST /auth/login` (token retourné) | OK |
| `GET /auth/me` avec Bearer token | OK |
| `GET /messages` admin avec token | OK |
| `GET /messages` sans token → **401 JSON** | OK (corrigé : middleware custom) |
| `POST /articles` avec token admin (création) | OK |
| `DELETE /articles/{slug}` avec token admin (suppression) | OK |
| `POST /articles` avec token **non admin** → **403** | OK |
| 6 logins échoués successifs → **429** au 5ᵉ | OK |
| Comptage des données seedées en MySQL (3 articles, 4 membres, 17 unités, 1 message) | OK |

Corrections effectuées pendant les tests :
- erreur 500 « Route [login] not defined » → résolue par le middleware `Authenticate` custom ;
- coquille de syntaxe dans `MemberController` corrigée ;
- validation `title` renforcée (required en création, sometimes en mise à jour) ;
- fichiers de test supprimés (article test, utilisateur lambda), port 8000 libéré.

---

## 11. Démarrage

```bash
cd cndt-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve        # http://127.0.0.1:8000
```

Exemples d'appels :

```bash
curl http://127.0.0.1:8000/api/v1/articles
curl http://127.0.0.1:8000/api/v1/organigramme

curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@cndt.cm","password":"password"}'
```

Côté frontend :

```js
const res = await fetch('http://127.0.0.1:8000/api/v1/articles?per_page=7');
const { data: articles } = await res.json();
```

---

## 12. Page de connexion / administration (frontend)

Fichier créé : `CNDT_SITE_VITRINE_2/admin.html` — première page branchée sur l'API.

- **Formulaire de connexion** (styles du site : `contact-form`, `contact-field`, `button__contact`)
  → `POST /api/v1/auth/login` ; le token est stocké dans `localStorage`
  (`cndt_admin_token`) ; token + utilisateur restaurés au rechargement de la page via `GET /auth/me`.
- **Mini tableau de bord** après connexion :
  - liste des messages reçus (`GET /messages`, non lus surlignés),
  - « marquer comme lu » (`PUT /messages/{id}`), suppression (`DELETE /messages/{id}`),
  - rafraîchissement et déconnexion (`POST /auth/logout` + purge localStorage).
- Gestion d'erreurs : 401 → session expirée et retour au formulaire, messages de validation
  de l'API affichés ; échappement HTML des contenus affichés.
- Intègre les conventions du site : header/footer injectés par `app.js`
  (`data-site-header` / `data-site-footer`), `site.css`, fil d'Ariane, `noindex`.
- Vérifié : page servie en HTTP 200, preflight CORS accepté depuis `http://localhost:8080`
  (`Access-Control-Allow-Origin` correct), login fonctionnel depuis cette origine.

> En local : ouvrir le site via un serveur statique (ex. Live Server sur `localhost:5500`
> ou `php -S 127.0.0.1:8080`) — l'API doit tourner sur `127.0.0.1:8000`.
>
> **Accès via XAMPP** : junction `C:\xampp\htdocs\cndt` → dossier du site vitrine ;
> le site est donc accessible sur `http://localhost/cndt/admin.html` (origin
> `http://localhost` autorisée dans le CORS de l'API).

## 13. Conteneurisation Docker

Fichiers créés :
- `CNDT_SITE_BACKEND/docker-compose.yml` — 3 services :
  - **cndt-mysql** : `mysql:8.0`, base `cndt_api`, volume persistant `mysql-data`,
    healthcheck `mysqladmin ping` ;
  - **cndt-api** : image construite depuis `cndt-api/Dockerfile` (PHP 8.3 + Apache,
    docroot sur `public/`, extensions `pdo_mysql`, outils `unzip`/`git` pour Composer),
    port `8000:80`, dépend du healthcheck MySQL, `migrate --seed` idempotent au démarrage ;
  - **cndt-frontend** : `httpd:2.4-alpine` servant le dossier du site vitrine en
    lecture seule, port `8080:80`.
- `cndt-api/Dockerfile` + `cndt-api/.dockerignore` (exclut vendor, logs, sqlite local).

Commandes :
```bash
cd CNDT_SITE_BACKEND
docker compose up -d --build     # construit et démarre tout
docker compose ps                # état des conteneurs
docker compose logs -f api       # logs de l'API (migrations au démarrage)
docker compose down              # arrêt (données MySQL conservées)
```

Vérifié en exécution : build OK (après correction : ajout de `unzip`/`git` dans l'image,
Composer échouait sans zip), 3 conteneurs `Up`, MySQL `healthy`, `GET /api/v1/articles` → 200
avec données seedées, `http://localhost:8080/admin.html` → 200, preflight CORS accepté,
login admin fonctionnel.

> URL après dockerisation : frontend `http://localhost:8080/admin.html`,
> API `http://localhost:8000/api/v1/...` (identiques à l'installation hors Docker).
> Le site reste aussi accessible via XAMPP (`http://localhost/cndt/...`) si Apache tourne —
> les deux cohabitent (origins déjà autorisées dans le CORS).

## 14. Prochaines étapes envisagées

1. **Brancher le reste du site vitrine** sur l'API (actualités, publications, galerie,
   personnel, projets, organigramme — la page `admin.html` est déjà branchée).
2. **Upload de médias** : endpoint d'upload d'images/fichiers (actuellement les champs
   `cover_image` / `file_path` / `path` acceptent des chemins).
3. **Back-office d'administration** (interface web pour gérer le contenu).
4. **Mise en production** : changer le mot de passe admin, configurer les origins CORS du
   domaine réel, éventuellement Varnish/queue selon le trafic.

---

## 15. Architecture frontend / backend + dashboard (27/10/2026, après-midi)

### Backend
- Nouveau endpoint `GET /api/v1/stats` (compteurs pour le dashboard) — `StatsController`.
- `docker-compose.yml` : montage du code source `./cndt-api:/var/www/html` (dev live).

### Frontend — dashboard d'administration (`CNDT_SITE_VITRINE_2/admin/`)
- `index.html` / `styles.css` / `app.js` — SPA vanilla JS, aucun framework.
- Écran de connexion, session restaurée via `/auth/me`, déconnexion (révocation du token).
- Sidebar + sections : Vue d'ensemble (cartes de stats + derniers messages), Actualités,
  Publications, Galerie (albums + photos), Personnel, Projets, Messages (badge non lus,
  marquer lu, supprimer), Organigramme (arbre CRUD avec ajout d'enfants).
- CRUD générique piloté par la configuration `ENTITIES` (colonnes + champs de formulaire),
  modale partagée, recherche côté API, `?all=true` pour inclure le non publié.
- `admin.html` : simple redirection vers `admin/`.

### Problèmes rencontrés et résolus
1. **Serveur orphelin sur 127.0.0.1:8000** (ancien `php artisan serve` relayé par WSL) qui
   interceptait les requêtes `localhost:8000` alors que Docker écoute sur `0.0.0.0:8000` →
   tous les premiers « tests Docker » passaient en réalité par le serveur hôte. Résolu en
   tuant le processus wslrelay (PID 13820).
2. **« Target class [StatsController] does not exist »** : l'ajout du `use
   App\Http\Controllers\Api\StatsController;` dans `routes/api.php` avait été rapporté
   « réussi » par l'outil d'édition mais jamais écrit (défaillance intermittente) →
   `StatsController::class` résolvait le nom court. Corrigé par insertion perl + vérification.
   (Leçon : toujours vérifier le contenu après une édition de fichier sur ce poste.)

### Tests réalisés (validés via le conteneur Docker)
- `GET /stats` → compteurs corrects (3 articles, 4 membres, 17 unités…)
- Liste avec `?all=true&per_page=200&search=` → filtrage OK
- `messages?unread=true&per_page=1` → `meta.total` pour le badge
- Album + photos (`GET /albums/{slug}`) → photos incluses
- CRUD unité organisationnelle (POST + DELETE) → OK
- `node --check admin/app.js` → syntaxe OK ; pages du dashboard servies en HTTP 200

### Documentation
- `ARCHITECTURE.md` créé : diagramme des 3 couches, sections du dashboard, flux
  d'authentification, organisation Docker.

---

## 16. Correction « je ne peux rien faire » sur le dashboard (27/10/2026)

### Cause principale (bloquante)
`styles.css` définissait `display: flex` sur `.app`, `.login-screen` et `.modal-overlay`,
ce qui **écrasait l'attribut HTML `hidden`** (UA style). Résultat : la modale invisible
(`z-index: 50`, fond semi-transparent) recouvrait toute la page et capturait tous les clics.
→ Correctif : règle globale `[hidden] { display: none !important; }`.

### Cause secondaire (429 partout)
`$middleware->throttleApi('login')` dans `bootstrap/app.php` appliquait le limiteur
de connexion (5/min) à **toutes** les routes API. → Ligne retirée ; le throttle
`login` reste appliqué uniquement à `POST /auth/login` (routes/api.php).

### Cause tertiaire (erreur JS « null.innerHTML »)
Course asynchrone : une réponse réseau arrivait après un changement de section et
écrivait dans un élément disparu. → Garde-fous ajoutés après chaque `await` dans
`renderOverview`, `renderEntityList`, `renderMessages`, `renderOrganigramme`.

### Validation (navigateur réel headless Edge + puppeteer)
- Connexion → application visible, token stocké
- Vue d'ensemble : cartes de stats (3/3 articles, 3 publications, 1 album·2 photos, 4 membres…)
- Actualités : 3 lignes · Messages : liste complète (1 message, boutons lu/supprimer)
- Organigramme : 18 unités · Galerie : 1 album (+ gestion photos)
- Aucune erreur JS résiduelle (`node --check` OK, plus de PAGEERROR)

Note : les montages Windows→conteneur (Docker Desktop) présentent un retard de
synchronisation de quelques dizaines de secondes — attendre ~30 s après une
modification de fichier avant de tester dans le conteneur.

---

## 17. Correction « la connexion ne renvoie pas dans l'admin » (27/10/2026)

### Cause : lenteur massive de l'API (3–5 s par requête)
Le montage du code source Windows (`./cndt-api:/var/www/html`) obligeait chaque requête
PHP à lire des milliers de fichiers à travers le partage de fichiers Docker Desktop
(gRPC-FUSE) → chaque requête prenait 3 à 5 secondes : la connexion semblait « ne rien faire ».

### Correctifs
1. **`docker-compose.yml`** : `vendor` monté depuis un volume conteneur
   (`api-vendor:/var/www/html/vendor`) — il reste sur le FS rapide du conteneur ;
   seul le code applicatif reste synchronisé depuis Windows.
2. **`Dockerfile`** : tuning opcache (`revalidate_freq=30`, realpath cache augmenté).
3. **`bootstrap/cache/packages.php` / `services.php` supprimés** : ils référençaient
   `laravel/pail` (paquet de dev présent dans le vendor de l'hôte mais absent du vendor
   de l'image `--no-dev`) → le conteneur crash-loopait au démarrage. Laravel les régénère.

### Résultat mesuré
| Requête | Avant | Après |
|---------|-------|-------|
| GET /stats | ~4 s | 0,10–0,24 s |
| POST /auth/login | ~4 s | 0,37 s |

Validation navigateur headless : connexion → dashboard immédiat ; toutes les sections
(articles 3 lignes, messages 1, organigramme 18 unités, albums 1) sans erreur JS.

> Note : si un jour le conteneur redémarre en boucle après un `composer install`
> sur l'hôte, supprimer à nouveau `bootstrap/cache/*.php`.

---

## 18. Refonte du dashboard en React + Tailwind + shadcn/ui (27/10/2026)

Le dashboard vanilla JS est remplacé par une application **React 19 + TypeScript +
Tailwind CSS v4 + shadcn/ui** (Radix UI, lucide-react, sonner).

- **Source** : `CNDT_SITE_VITRINE_2/dashboard/` (Vite, routage par hash, base `./`)
- **Déploiement** : `npm run build` → copie de `dist/*` dans `admin/` — l'URL reste
  `http://localhost:8080/admin/` ; l'ancienne version vanilla JS a été supprimée.
- Structure : `lib/api.ts` (client + types), `lib/entities.tsx` (config CRUD générique),
  `components/` (LoginPage, Layout, EntityFormDialog, ui shadcn), `pages/` (Overview,
  Entity CRUD + photos galerie, Messages, Organigramme).
- Composants shadcn installés : button, card, input, label, textarea, select, checkbox,
  table, dialog, badge, sonner, skeleton, alert-dialog.
- `components.json` + thème Tailwind v4 (variables oklch, couleur primaire verte CNDT).

### Notes techniques
- `shadcn init` interactif contourné : configuration manuelle (components.json, index.css,
  lib/utils.ts) puis `npx shadcn add` pour les composants (le CLI crée un dossier `@/`
  littéral → fichiers déplacés dans `src/components/ui/`).
- TypeScript 6 : `baseUrl` retiré des tsconfig (TS5102).

### Validation (Edge headless + puppeteer)
- Écran de connexion → login → dashboard (sidebar, 7 cartes de stats)
- Sections : Actualités (3 lignes), Messages (message affiché), Organigramme (3 chapitres)
- Modale « Nouveau » → création d'un article → visible dans le tableau → suppression
  (alert-dialog) → retiré de la liste
- Aucune erreur navigateur, aucun échec de requête.

---

## 19. Actualités dynamiques — backend + frontend complet (27/10/2026)

Chaîne complète de gestion des actualités : **Dashboard (CRUD React) → API Laravel →
pages publiques dynamiques**.

### Backend
- Seeder enrichi avec les **8 actualités réelles du site** (titres, extraits, images,
  dates, catégories ia/evenement/formation, slugs identiques aux fichiers statiques
  existants) ; les 3 articles de démonstration sont supprimés au seed.
- Endpoints publics inchangés : `GET /articles` (paginé, `?category=`, `?search=`,
  `?per_page=`, `?page=`) et `GET /articles/{slug}`.

### Frontend public (site vitrine)
- **Nouveau `api-actualites.js`** (vanilla, sans build) — 3 contextes :
  1. `[data-actualites-grid]` (actualites.html) : liste paginée (6/page) + bouton
     « Charger plus » + filtres par catégorie (les boutons existants `data-filter`
     interrogent l'API avec `?category=`) ;
  2. `[data-news-list]` (index.html) : widget « Actualités » de l'accueil rempli avec
     les 4 derniers articles (liens statiques conservés en secours si l'API tombe) ;
  3. `[data-article-detail]` (`actualite/article.html`) : page générique d'article,
     slug via `?slug=`, titre/`<title>`/image (préfixe `../`)/date/contenu dynamiques.
- `actualites.html` : grille statique remplacée par le conteneur dynamique ;
- `index.html` : agenda `<div class="agenda-list" data-news-list>` + inclusion du script ;
- Nouvelle page `actualite/article.html` (template générique).
- Les anciennes pages statiques `actualite/*.html` restent en place (pas de rupture
  des liens existants) ; les nouveaux liens pointent vers la page dynamique.

### Validation (Edge headless)
- Liste : 6 cartes puis 8 après « Charger plus », plus aucun bouton en fin de liste
- Filtre IA → 3 articles, filtre Formation → 2 articles (via `?category=`)
- Page article : titre, date « 25 mai 2026 », image `../assets/Symposium_1.jpg`,
  contenu rendu
- Accueil : widget avec 4 liens vers les articles dynamiques
- Aucune erreur JS.

Note : deux éditions d'outil « réussies » mais non écrites (filtre catégorie, puis
corrigées via sed) — toujours vérifier le contenu après édition sur ce poste.

---

## 20. Modale élargie + éditeur WYSIWYG + upload d'images par glisser-déposer (27/10/2026)

### Backend — upload vers le stockage Laravel
- Nouveau `UploadController` : `POST /api/v1/upload` (groupe admin) — validation
  `image|mimes:jpg,jpeg,png,webp,gif,svg|max:5120`, stockage dans le disque **public**
  de Laravel (`storage/app/public/uploads/YYYY/MM/`, nom slugifié + suffixe aléatoire),
  réponse `{path, url}` (URL absolue servie par `/storage/…`).
- Lien `public/storage` vérifié dans le conteneur (image servie HTTP 200).

### Dashboard React — rédaction d'articles
- **Librairie retenue : Tiptap** (@tiptap/react + starter-kit, extensions image/link/
  underline) — éditeur headless, stylé entièrement en Tailwind, intégré aux champs
  shadcn de la modale.
- `RichTextEditor.tsx` : barre d'outils (gras, italique, souligné, barré, titres 2/3,
  listes, citation, lien, image, annuler/rétablir) ; **glisser-déposer ou coller une
  image dans l'éditeur → upload vers Laravel → insertion automatique**.
- `ImageUploadField.tsx` : drop-zone (drag & drop + parcourir + chemin/URL manuel)
  avec aperçu, appliquée à toutes les images de couverture et photos des entités.
- **Modale élargie** : `sm:max-w-3xl` (768px) + hauteur max 90vh, défilement interne.
- `content` des articles = type `richtext` (HTML stocké en base).

### Site public
- `api-actualites.js` : rendu du contenu en **HTML riche** si présent (WYSIWYG),
  repli paragraphes texte brut sinon ; styles `.article-content` ajoutés à `site.css`
  en amont (dans le CSS du dashboard pour l'éditeur, côté public via styles inline).

### Validation
- Upload API via curl : fichier rangé dans `storage/app/public/uploads/2026/10/`,
  URL servie HTTP 200.
- Edge headless : éditeur `contenteditable` présent, saisie + **gras →
  `<p>…<strong>…</strong></p>`**, création d'article, rendu public avec `<strong>`
  affiché, suppression de test, largeur modale 768px. Aucune erreur JS.

Note : encore une édition d'outil « réussie » non écrite (taille modale) — corrigée via
sed et vérifiée par mesure réelle dans le navigateur.

---

## 21. Correction : contenu vide dans l'éditeur lors d'une modification (27/10/2026)

### Cause (double)
1. `ArticleResource` n'incluait `content` que sur la route `articles.show` — or le
   dashboard édite depuis la **liste** (`articles.index`) → champ vide.
2. `$request->user()` est **null sur les routes publiques** (pas de middleware
   `auth:sanctum` sur index/show) — même avec un token valide.

### Correctif
- Résolution de la garde **à la demande** : `$request->user('sanctum')?->is_admin`
  fonctionne sur les routes publiques avec Bearer token (validé par une route de
  debug temporaire : `{"sanctum":"admin@cndt.cm"}` avec token, null sans).
- Appliqué dans `ArticleResource` (inclusion du `content` pour tout admin) et dans
  les contrôleurs Article/Publication/Member/Project (`show` : les admins peuvent
  consulter les éléments non publiés via les routes publiques).
- `RichTextEditor` : effet de synchronisation sur `[value, editor]` (édition
  `immediatelyRender: false` de Tiptap).

### Résultat
- Liste admin : `content` présent · liste publique : absent (perf) · détail public : présent
- Edge headless, modale de modification de l'article « RÉUNION BILATÉRALE » :
  titre pré-rempli, **éditeur WYSIWYG pré-rempli avec le HTML**, image de couverture
  affichée. Aucune erreur JS.

### Incident pendant la correction
Le retrait de la route de debug a laissé des lignes orphelines → boucle de
redémarrage du conteneur (Unmatched '}') → réparé, syntaxe vérifiée (php -l),
conteneur rétabli.

Note : la liste contient un article « test » créé vraisemblablement lors des essais
manuels — à supprimer du dashboard si inutile.

---

## 22. Import Word (.docx) + galerie d'images par article (28/10/2026)

### 1. Import d'un fichier Word dans l'éditeur d'article
- **Librairie : Mammoth 1.13** (build navigateur `mammoth.browser.min.js` chargé en
  global via `<script>`, dans `admin/`) — conversion .docx → HTML structuré.
- Nouveau bouton **« Importer Word »** (icône document) dans la barre d'outils du
  WYSIWYG (`src/lib/docx.ts` + `RichTextEditor.tsx`) :
  - titres Word (Heading 1/2/3, Title) → `<h1>/<h2>/<h3>`, gras/italique/barré,
    listes à puces/numérotées, citations ;
  - **images du Word extraites → téléversées vers le stockage Laravel** (`/upload`)
    puis insérées par URL (pas de base64 en base) ;
  - le contenu **remplace** l'éditeur ; restauration de l'état précédent en cas d'échec.

### 2. Galerie d'images par article
- **Backend** : table `article_images` (article_id, path, caption, position), modèle
  `ArticleImage`, relation `Article::articleImages()`, resource dédiée ; endpoints
  admin `GET|POST /articles/{article}/images` et `DELETE /articles/{article}/images/{image}` ;
  liste admin + détail public renvoient les images (eager loading conditionné admin).
- **Dashboard** : champ **« Galerie de l'article »** dans le formulaire (dropzone
  multi-images avec vignettes + suppression, upload immédiat vers Laravel) ;
  synchronisation à l'enregistrement (ajout des nouvelles / retrait des retirées).

### 3. Site public
- `api-actualites.js` : sous le contenu de l'article, section **« Galerie photos »**
  (grille responsive, légendes, préfixe `../` pour les chemins relatifs).

### Validation (Edge headless, document Word généré via la lib `docx`)
- Import : `<h1>`, `<h2>`, `<strong>`, `<em>`, `<ul>` tous présents dans l'éditeur
- Création : contenu HTML complet enregistré, image de galerie attachée (id 5)
- Page publique : titre, gras rendu, galerie avec 1 image
- Suppression de test OK — aucune erreur JS

Note : les premiers échecs du test venaient du script de test lui-même (chemin de
fichier, délais) — validé par sonde pas-à-pas (contenu présent à t+1s).

---

## 23. Correction : images de galerie non affichées côté site (28/10/2026)

### Diagnostic pas à pas
- Fichiers valides sur le disque (JPEG `ff d8 ff e0`), servis **octet pour octet
  identiques** (MD5 identique via `/storage/…`), type MIME correct
- Chargement direct de l'URL dans le navigateur : OK (1280×960)
- Sur la page article : `<img>` présents mais `naturalWidth = 0` sans requête échouée

### Cause
`loading="lazy"` sur les images de galerie : placées **sous le contenu** (sous le pli
de l'écran), elles ne se chargeaient qu'au défilement — et pas du tout si le
navigateur ne calculait pas d'intersection (cas des anciennes versions en cache).

### Correctif
`loading="lazy"` retiré des images de galerie dans `api-actualites.js` (elles se
chargent immédiatement).

### Validation (Edge headless)
Page `actualite/article.html?slug=test` : les 2 images de galerie chargées
**sans défilement** (1280×1280 et 1280×960, `complete + naturalWidth > 0`).

---

## 24. Page article générique alignée sur le design d'origine (28/10/2026)

La page dynamique `actualite/article.html` reproduit désormais fidèlement la mise en
page des pages d'articles statiques d'origine :

- **Layout deux colonnes** `.project-layout` : contenu principal `.project-content`
  + colonne latérale `.sidebar` (comme `symposium-2026.html` et les autres)
- **Colonne latérale** : bloc « Actualités » (agenda des 4 derniers articles, liens
  locaux `article.html?slug=…` — `articleLink()` détecte désormais le sous-dossier) +
  bloc « Publications & médias » (les 5 liens d'origine : Technomag, Rapports,
  Documents techniques, Émissions TV, Photothèque)
- **Galerie** : rendue avec les classes d'origine (`section#galerie.project-section`
  + `.project-gallery` + `<figure>`) au lieu du rendu custom précédent
- **Hero** : image de couverture + date + **catégorie** ajoutée dans le bandeau
- Section « Actualité » (h2) introduisant le contenu, fil d'Ariane conservé

### Validation (Edge headless, article « test »)
`.project-layout` ✓, sidebar « Actualités » avec 4 liens dynamiques locaux ✓,
« Publications & médias » avec 5 liens ✓, galerie `.project-gallery` : 6 images
toutes chargées ✓, hero + catégorie ✓.

---

## 25. Cache-busting de api-actualites.js (28/10/2026)

Le script consommateur est inclus avec un paramètre de version (`api-actualites.js?v=2`)
dans `index.html`, `actualites.html` et `actualite/article.html` : les mises à jour du
script se propagent immédiatement aux visiteurs, sans rechargement forcé (Ctrl+F5).

Rappel : la section « Galerie » n'apparaît sur la page article **que si des images ont
été attachées via le champ « Galerie de l'article » du dashboard** (article « test » :
6 images affichées, toutes chargées — vérifié). Les articles sans images en galerie
n'affichent pas la section (les images importées d'un Word restent dans le contenu).

---

## 26. Typographie du contenu dynamique alignée sur le site (28/10/2026)

Le contenu des articles dynamiques (HTML du WYSIWYG/Word) n'héritait pas de la
typographie du site (styles par défaut du navigateur pour h2/h3, listes, etc.).

### Correctifs
- **`site.css`** : nouveau bloc `.article-content` reprenant les conventions du site —
  paragraphes en `var(--ink)`, titres h2/h3/h4 en `var(--green-950)` avec le
  soulignement vert clair (`border-bottom: 2px solid var(--green-100)`) comme les
  sections d'origine, listes stylées comme `.project-list` (#4f5c54, 0.92rem),
  `strong` en vert foncé, liens/citations/pointillés horizontaux aux couleurs du site.
- **Cache-busting `site.css?v=2`** dans index.html, actualites.html et
  actualite/article.html (même mécanique que api-actualites.js).

### Validation (Edge headless — styles calculés)
- Paragraphes : rgb(32,41,35) = `--ink` ✓
- `strong` et titres de section : rgb(13,46,32) = `--green-950` ✓
- Feuille liée : `../site.css?v=2` ✓

---

## 27. Section « Instituts sous tutelle du MINRESI » sur la page article (28/10/2026)

La page générique `actualite/article.html` intègre désormais la section présente sur
les pages d'origine : titre `.sp-title` + **carrousel Swiper** `.instituts-carousel`
avec les 8 instituts (CNDT, ANRP, IRAD, IMPM, INC, MIPROMALO, CNE, IRGM), logos
`../assets/instituts/…`, pagination et flèches de navigation — initialisé
automatiquement par `app.js` (section 6, Swiper chargé en fin de body comme les
pages d'origine).

### Validation (Edge headless)
Section présente, titre « Instituts sous tutelle du MINRESI », 8 slides, logos
chargés, `swiper-initialized: true`, autoplay actif.

---

## 28. Correction : logo CNE non affiché (28/10/2026)

Le fichier réel est `assets/instituts/cne-1.jpeg` mais 6 pages référençaient
`cne-1.jpg` (bug hérité des pages d'articles statiques — l'accueil avait la bonne
extension) : `actualite/article.html` (nouvelle page générique) + 5 articles statiques
+ la page projet. Extension corrigée partout.

Validation : image servie (HTTP 200, image/jpeg) et chargée dans le carrousel
(`chargee: true`).

---

## 29. Bloc « Actualités » : 5 liens au lieu de 4 (28/10/2026)

`renderNewsList` de api-actualites.js passe de `per_page=4` à `per_page=5` — le
widget « Actualités » affiche les **5 derniers articles** (accueil + colonne latérale
de la page article). Cache-busting bumpé (`api-actualites.js?v=3`).

Validation : sidebar article → 5 liens, accueil → 5 liens.

---

## 30. Badge du créateur de l'article (28/10/2026)

Reproduction du badge « Rédigé par » présent sur la page d'origine
(`fracture-numerique-per-bayangam-akonolinga.html`) — pastille `.author-badge`
(portrait rond, libellé, nom, fonction), cliquable vers la page du membre.

### Backend
- Migration `add_author_fields_to_articles_table` : `author_name`, `author_role`,
  `author_photo`, `author_url` (nullables) + fillable, resource et validation.
- Seeder : auteur de l'article « fracture numérique » renseigné
  (M. BABGAI Raphaël GUIDÉKÉ, Chef de la cellule informatique — CNDT,
  photo + lien vers membres/babgai-raphael.html).

### Dashboard
- 4 nouveaux champs dans le formulaire d'article : Auteur — nom / fonction / photo
  (drop-zone) / lien.

### Site public
- `api-actualites.js` : badge rendu après le contenu si `author_name` est défini
  (styles `.author-badge` existants de site.css). Cache-busting bumpé (v=4).

### Validation (Edge headless, article « fracture numérique »)
Badge présent : « Rédigé par » / M. BABGAI Raphaël GUIDÉKÉ / fonction /
photo chargée (1024px) / lien vers la page membre / pastille arrondie (999px).
Aucun échec réseau.

---

## 31. Auteur = sélecteur du personnel + carrousel des instituts dynamique (28/10/2026)

### 1. Badge auteur branché sur le personnel
- Nouveau type de champ « member » (`MemberSelectField.tsx`) : **recherche + select
  parmi les membres enregistrés** — sélectionner un membre remplit automatiquement
  `author_name`, `author_role`, `author_photo` et `author_url` (`membres/{slug}.html`).
  Aperçu du badge directement dans le formulaire. Les 4 champs auteur bruts sont
  remplacés par ce sélecteur dans le formulaire d'article.

### 2. Carrousel des instituts dynamique
- **Backend** : table `institutes` (name, url, logo, position, is_visible) + CRUD
  (`GET /instituts` public trié par position, admin store/update/destroy) + seeder
  des 8 instituts d'origine.
- **Dashboard** : nouvelle section « Carrousel instituts » (CRUD complet : nom, site,
  logo en drop-zone, position, visibilité) + **boutons ↑/↓ par ligne** pour changer
  l'ordre des éléments du carrousel (swap des positions via l'API).
- **Site public** : `renderInstituts()` dans api-actualites.js — le carrousel
  (page article, accueil, actualites) est alimenté par l'API, ordre géré depuis le
  dashboard ; secours statique si l'API est indisponible. Cache-busting v=5.

### Bugs corrigés en cours de route
- `apiResource('instituts')` génère le paramètre `{institut}` (singulier) alors que le
  contrôleur attendait `$institute` → liaison de modèle silencieusement vide,
  PUT « 200 » sans persistance. Corrigé par `->parameters(['instituts' => 'institute'])`.
- Gestionnaire de clic du réordonnancement attaché au conteneur du tableau (pas à un
  div vide) ; imports TS nettoyés.

### Validation (Edge headless)
- Page article : 8 slides API, CNDT premier, Swiper initialisé
- Dashboard : section « Carrousel instituts », 8 lignes, boutons ↑/↓ présents
- Réordonnancement : clic ↓ sur CNDT → ordre devient ANRP, CNDT, IRAD (PUT /1 et /2
  en 200, positions persistées) ; restauration vérifiée

---

## 32. Correction du slider des instituts + initialisation Git (28/10/2026)

### Slider « Instituts sous tutelle »
**Cause** : Swiper fixe la largeur des slides à l'initialisation ; les slides
remplacés dynamiquement par l'API gardaient le `width:100%` par défaut → chaque slide
prenait toute la largeur (1232px au lieu de 185px), le carrousel semblait absent.
**Correctif** : après l'injection des slides, appel de `swiper.update()` (+ `loopFix`)
sur l'instance exposée par Swiper 11 (`element.swiper`). Vérifié : slides 185px,
identiques à la version statique (cache-busting v=6).

### Initialisation Git
- **Backend** (`CNDT_SITE_BACKEND`) : nouveau dépôt (branche main) —
  1. `feat: API Laravel 13 du CNDT` (contenus, auth durcie, uploads, galeries,
     auteurs, carrousel instituts, seeders)
  2. `build: conteneurisation Docker + documentation`
  `.gitignore` : vendor/.env gérés par Laravel, uploads et caches exclus.
- **Frontend** (`CNDT_SITE_VITRINE_2`, dépôt existant, branche main) — 3 commits :
  1. `feat: dashboard d'administration React + Tailwind + shadcn`
  2. `feat: site public dynamique` (actualités, page article, galerie, badge, carrousel)
  3. `fix: logo CNE, typographie, cache-busting, redirection admin`
  `dashboard/node_modules` et `dashboard/dist` ignorés (le build déployé dans
  `admin/` est versionné).

---

## 33. Carrousel de l'accueil (hero) géré dynamiquement + dropdown de position (28/10/2026)

### Backend
- Champs `in_hero` (bool) + `hero_position` (int) sur les articles ; `GET /articles?hero=1`
  renvoie les slides du carrousel triés par position ; position auto-attribuée à
  l'activation de `in_hero` ; seeder : les 6 articles du carrousel actuel, positions 1-6.

### Dashboard — section « Carrousel accueil »
- Liste des slides (vignette, titre, statut) avec **menu déroulant « Position »
  (1..N) par slide** : choisir une position déplace l'article et renumérote tout
  l'ordre (PUT séquentiels) — c'est le « dropdown pour bouger les positions » demandé.
- Sélecteur d'ajout (articles hors carrousel) + bouton de retrait.
- Case « Afficher dans le carrousel de l'accueil » ajoutée au formulaire d'article.

### Site public
- `renderHero()` dans api-actualites.js : les slides (image, eyebrow=catégorie, titre,
  extrait, lien vers l'article dynamique) et les points de navigation sont générés
  depuis l'API dans l'ordre géré. La logique du carrousel (is-active, points, flèches,
  autoplay 6,5 s, pause au survol, prefers-reduced-motion) est réinitialisée sur les
  nouveaux éléments (app.js ne voit plus que les slides détachés). Secours statique
  si l'API est indisponible. Cache-busting v=7.

### Validation (Edge headless)
- Accueil : 6 slides dans l'ordre seedé, 6 dots, slide actif avec image chargée et
  lien dynamique
- Dashboard : 6 lignes, dropdowns 1..6 — passage de SIA en position 3 → l'API et
  l'accueil reflètent immédiatement le nouvel ordre (Symposium, AIEA, SIA, …)
- Aucune erreur JS. Ordre initial restauré.

---

## 34. Ergonomie du carrousel accueil + retrait de sections (28/10/2026)

### Ajout d'articles ergonomique
Le sélecteur unique (impraticable avec 100+ articles) est remplacé par une
**recherche** (titre ou catégorie) avec liste de résultats (vignette + titre +
catégorie + bouton « + Ajouter »), plafonnée à 8 résultats — utilisable quel que
soit le nombre d'articles.

### Retrait des sections « Carrousel instituts » et « Organigramme » du dashboard
- Sidebar et routes nettoyées (navigation : Vue d'ensemble, Actualités, Publications,
  Galerie, Personnel, Projets, Messages, Carrousel accueil).
- `OrganigrammePage.tsx` supprimé ; config de l'entité instituts retirée.
- **Le site public est inchangé** : le carrousel des instituts et la page
  organigramme restent alimentés par l'API (données seedées) ; les endpoints
  backend sont conservés.

### Validation (Edge headless)
Navigation sans les deux sections ✓, recherche « test » → ajout au carrousel en
position 7 ✓ puis retrait ✓, accueil public : 8 slides instituts + 6 slides hero,
organigramme public 17 unités ✓, aucune erreur JS.

---

## 35. Symbiose visuelle dashboard ↔ site + albums avec photos intégrées (29/10/2026)

### Interface en symbiose avec le site statique
- **Police du site** : CNDT Sans (URWGothic-Book/Demi) chargée depuis les assets du site
  (chemins corrigés `../../assets/` depuis le CSS du build — les `../assets/`
  résolvaient vers /admin/assets → 404).
- **Palette exacte du site** : --primary #1f663f, encre #202923, vert foncé #0d2e20,
  vert clair #e3eee7, bordures #dce4de, texte atténué #667069.
- **Sidebar** type site : fond #0d2e20, bandeau « République du Cameroun / Paix ·
  Travail · Patrie », item actif #1d5a3b ; topbar avec liseré vert.
- **Connexion** : logo CNDT du site au-dessus du formulaire.

### Albums : création + ajout de photos dans le formulaire
- Champ **« Photos de l'album »** (dropzone multi-images avec vignettes et
  suppression) directement dans la modale de création/édition d'album — les photos
  sont téléversées immédiatement et rattachées à l'album à l'enregistrement
  (synchronisation ajout/retrait via `/albums/{slug}/photos`).
- L'ancien panneau de gestion des photos sous le tableau (AlbumPhotosPanel) est
  retiré — tout se fait dans le formulaire.

### Incident résolu : mot de passe admin inconnu
Le commit de sécurisation (e80c690) fait générer un mot de passe admin aléatoire au
seed ; les identifiants documentés ne fonctionnaient plus. **Réinitialisé à
`admin@cndt.cm` / `password`** via tinker (attention : passer par l'instance du
modèle pour le cast `hashed` — un `->update()` du query builder stocke en clair).
Avant mise en ligne : définir `ADMIN_PASSWORD` ou changer le mot de passe.

### Validation (Edge headless)
Logo chargé ✓, bandeau + sidebar #0d2e20 ✓, police CNDT Sans appliquée ✓,
création d'un album avec 2 images ajoutées dans le formulaire → album enregistré
avec ses 2 photos via l'API ✓, nettoyage ✓, aucune erreur JS.

## 36. Galerie photo dynamique (galerie.html alimentée par l'API)

**Problème** : les albums créés dans le dashboard n'apparaissaient pas sur le site
public — la page `galerie.html` restait 100 % statique (albums codés en dur).

### Ce qui a été fait
- `CNDT_SITE_VITRINE_2/api-actualites.js` — nouvelle section **`renderGalerie()`** :
  - `GET /api/v1/albums?per_page=100` (photos incluses) ;
  - si l'API est indisponible ou qu'aucun album n'existe → **repli sur la galerie
    statique d'origine** (comportement inchangé) ;
  - régénère la grille `.gallery-albums` (mêmes classes que le markup statique :
    cover = `cover_image` ou 1ʳᵉ photo, date « mois année », titre) ;
  - remplace les vues d'album statiques par une vue par album
    (`data-album-view`, back, head titre/meta « N photos »/description,
    `.gallery-photos` avec `data-photo-src` / `data-photo-caption`) ;
  - chemins : URL absolues et `/storage/…` servies telles quelles (le proxy Apache
    du frontal sert déjà `/storage/`), chemins relatifs préfixés d'un `/` ;
  - `onerror` gracieux : une image introuvable est retirée (les albums de seed
    dont les fichiers n'existent pas n'affichent pas d'icônes cassées).
- **`initGalerie()`** : réplique la logique de `app.js` (§GALERIE) sur les éléments
  dynamiques — ceux d'origine sont détachés, leurs listeners sont morts :
  navigation grille → album → retour, deep link `?album=slug`, et **lightbox complète
  clonée** (`cloneNode(true)` + `replaceWith`) pour détacher les listeners de app.js
  (navigation clavier, fond, flèches, miniatures, swipe tactile).
- `galerie.html` : inclusion de `api-actualites.js?v=8` (après `app.js`) —
  le carrousel des instituts de cette page devient dynamique au passage.
- Cache-busting passé à `?v=8` sur `index.html`, `actualites.html`,
  `actualite/article.html`.

### Validation (Edge headless, `/tmp/dash-test/galerie-dyn-test.js`)
Grille : 2 albums du dashboard rendus, covers chargées ✓ ; clic album → vue avec
2 photos chargées ✓ ; clic photo → lightbox ouverte, image chargée, index « 1/2 »,
2 miniatures ✓ ; fermeture ✓ ; retour grille ✓ ; deep link `?album=…` ✓ ;
aucune erreur JS ; aucune régression sur index/actualités/article (hero, cartes,
widget actualités, carrousel instituts).

## 37. Catalogue du personnel dynamique (personnel.html)

**Objectif** : gérer le personnel depuis le dashboard, sachant qu'il s'affiche de
deux façons sur la page publique : en **cartes** regroupées par niveau
hiérarchique (photo + fonction + diplôme) et dans le **tableau** des personnels
(N°, nom, grade, diplôme de recrutement / spécialité).

### Backend (cndt-api)
- Migration `2026_10_29_120000_add_catalog_fields_to_members_table` :
  `display_type` ('card'|'table', défaut 'card'), `level` (niveau hiérarchique),
  `level_position` (ordre du niveau), `grade` (tableau), `degree` (carte),
  `speciality` (tableau) — + modèle, MemberResource, validation du contrôleur.
- **PersonnelSeeder** : catalogue réel repris de la page d'origine
  (14 cartes sur 5 niveaux avec photos `assets/people/*`, 40 personnels du
  tableau) ; remplace les 4 membres de démonstration du seed initial.
  Idempotent (updateOrCreate par slug) — DatabaseSeeder l'appelle désormais.

### Dashboard
- Entité Personnel : colonnes Nom/Fonction/**Affichage (Carte/Tableau)**/Niveau/
  Unité/Visible ; champs « Type d'affichage sur la page Personnel » (select),
  niveau hiérarchique + position du niveau, grade, diplôme, spécialité.
- Build redéployé dans `admin/`.

### Site public (api-actualites.js v=9)
- `renderPersonnel()` : GET `/members?per_page=500`, cartes regroupées par
  niveau (tri `level_position` puis `position`), lignes du tableau triées par
  nom de famille, numérotation automatique, compteurs (« N personnes »)
  mis à jour ; repli sur la page statique si l'API est vide/indisponible.
- `initStaffSearch()` : la barre de recherche prévue dans le markup statique
  n'était câblée par aucun script — elle filtre désormais cartes + lignes du
  tableau (tous les mots doivent figurer), masque les niveaux vides, met à
  jour le compteur et affiche le bouton « Réinitialiser » et l'état vide.
- `personnel.html` : inclusion du script ; cache-busting `?v=9` sur toutes
  les pages consommatrices.

### Validation (Edge headless)
Catalogue réel : 5 niveaux dans l'ordre d'origine (Direction → Autres Chefs de
Secrétariat), 14 cartes, 14/14 photos chargées, 40 lignes triées (« 01 Mme
ATEM TAKANG-ETTA Linda-Rose »), compteur 54, entête tableau « 40 personnes » ✓ ;
recherche « tchuinte » → 1 carte, « tablard » (membre de test) → ligne du
tableau ✓ ; création/édition/suppression via API testées puis nettoyées ✓ ;
dashboard : colonnes + champs présents dans la modale ✓ ; aucune erreur JS,
aucune régression sur index/galerie/actualités.

## 38. Catalogue du personnel — allègement du formulaire et liens des fiches

Retours utilisateur :
1. **plus de saisie de position** (ni position de la personne, ni position du
   niveau) ;
2. **retrait du champ « Chapitre »** ;
3. **restaurer les liens des cartes vers les fiches individuelles**
   (perdus au passage au dynamique, sauf pour le tableau qui n'a pas de fiche).

### Ce qui a été fait
- Backend : colonne **`page_url`** sur `members` (migration 2026_10_29_130000,
  modèle, ressource, validation) — lien de la fiche personnelle de la carte.
- PersonnelSeeder : les 14 cartes portent leurs liens d'origine
  (`membres/ele-abiama-patrice.html`, … ; `#` pour la Direction, comme la page
  statique) ; les positions ne sont plus seedées.
- Dashboard : formulaires allégés — suppression des champs « Position du
  niveau », « Position dans le niveau » et « Chapitre » ; ajout de « Lien de la
  fiche personnelle ». Build redéployé dans `admin/`.
- Site public : l'ordre d'affichage devient **l'ordre de création** (tri par
  `id` des niveaux et des personnes — aucun positionnement à gérer), et les
  cartes pointent vers `page_url` (repli `#`).

### Validation (Edge headless)
5 niveaux d'origine dans l'ordre (Direction → Autres Chefs de Secrétariat) ✓ ;
14 cartes avec leurs liens vers les fiches (fiches testées HTTP 200) ✓ ;
modale dashboard sans champ position/chapitre, champ lien présent ✓ ;
aucune erreur JS. Note : le membre « test test » créé depuis le dashboard
apparaît en dernier dans son niveau « Test » — supprimable depuis le dashboard.

## 39. Fiche individuelle automatique des personnes du catalogue

**Demandes** : 1) la page individuelle d'une personne créée dans le catalogue
doit se créer automatiquement, avec les informations nécessaires dans le
formulaire quand on choisit « carte hiérarchique » ; 2) 4 grades de recherche :
Attaché, Chargé, Maître et Directeur de Recherche.

### Backend
- Colonne **`since`** sur `members` (année « Depuis », migration 2026_10_29_140000,
  modèle, ressource, validation) ; la fiche individuelle est servie par
  `GET /members/{slug}` (déjà en place : email/phone/biographie sur show).

### Dashboard
- **Champs conditionnels** : `showIf` dans FieldConfig, filtrés dans
  EntityFormDialog selon les valeurs du formulaire.
- Formulaire Personnel : « Grade de recherche » en **select** (Attaché de
  Recherche, Chargé de Recherche, Maître de Recherche, Directeur de Recherche) ;
  pour les cartes : niveau hiérarchique, diplôme, **En fonction depuis** et
  biographie (champs fiche) ; ces champs disparaissent pour le tableau.
  Le champ « Lien de la fiche personnelle » est retiré (page automatique).

### Site public
- **Nouvelle page `membres/membre.html?slug=…`** : fiche individuelle
  dynamique calquée sur les fiches statiques d'origine (profile-hero avec photo,
  nom, fonction, informations clés Grade/Spécialité/Fonction/Depuis — seules
  les rubriques renseignées s'affichent —, biographie HTML, sidebar actualités
  dynamiques, retour au personnel, carrousel instituts).
- `renderMembreFiche()` dans api-actualites.js (v=10) ; `articleLink()` gère
  désormais le sous-dossier membres/.
- Les cartes de personnel.html pointent vers `page_url` (fiches statiques
  d'origine, ex : membres/ele-abiama-patrice.html) **ou** vers la fiche
  automatique `membres/membre.html?slug=…` pour les nouvelles personnes.

### Validation (Edge headless)
Membre créé via l'API (grade « Chargé de Recherche », depuis 2024, biographie
riche) → fiche complète : titre, fonction, 4 infos clés, biographie HTML rendue,
photo masquée si absente, 5 actualités dans la sidebar, lien retour ✓ ;
formulaire dashboard : champs fiche présents pour « carte », absents pour
« tableau » ✓ ; aucune erreur JS, aucune régression (index, galerie,
actualités) ; membre de test supprimé.

## 40. Fiche individuelle — biographie en WYSIWYG et centres d'intérêts

**Demandes** : éditeur visuel pour la biographie + saisie des points d'intérêt
multiples, représentés comme sur le design (puces « chips »).

### Ce qui a été fait
- **Biographie** : le champ passe de `textarea` à l'éditeur WYSIWYG du
  dashboard (RichTextEditor, déjà utilisé pour les articles) — contenu HTML
  nettoyé côté serveur (HtmlSanitizer) et rendu tel quel sur la fiche.
- **Centres d'intérêts** : nouvelle colonne `interests` sur `members`
  (JSON, migration 2026_10_29_150000, cast `array`, validation
  `interests.*:max 150`).
- **Dashboard** : nouveau type de champ `tags` + composant `TagsField`
  (saisie puis Entrée/virgule → puce retirable ; Retour arrière retire la
  dernière puce). Le champ « Centres d'intérêts » n'apparaît que pour les
  cartes hiérarchiques (showIf).
- **Fiche publique** : section « Centres d'intérêts » avec les mêmes classes
  que les fiches d'origine (`.project-section`, `ul.chip-list > li.chip`) —
  masquée si aucun intérêt n'est renseigné. Cache-busting v=11.

### Validation (Edge headless)
Dashboard : éditeur WYSIWYG présent, champ intérêts présent, saisie de
« Robotique de test » + Entrée → puce ajoutée puis seconde puce ✓ ;
fiche publique : titre, biographie HTML rendue (gras), section intérêts avec
3 chips ✓ ; aucune erreur JS ; membre de test supprimé.

## 41. Correctif — logos du carrousel des instituts sur les fiches individuelles

**Problème** : sur les pages individuelles créées (membres/membre.html), les
logos du carrousel « Instituts sous tutelle du MINRESI » ne s'affichaient pas.

**Cause** : le carrousel est rendu dynamiquement par `renderInstituts()` et le
préfixe de chemin des logos relatifs ne couvrait que le sous-dossier
`actualite/` — depuis `membres/`, « assets/… » résolvait vers
`/membres/assets/…` (404).

**Correctif** : le préfixe couvre désormais les sous-dossiers `actualite/` et
`membres/` (regex étendue) ; cache-busting v=12.

**Validation (Edge headless)** : les 8 logos (CNDT, ANRP, IRAD, IMPM, INC,
MIPROMALO, CNE, IRGM) répondent HTTP 200 depuis membre.html, article.html,
personnel.html et index.html.

## 42. Formulaire de contact câblé sur l'API + section Messages du dashboard

**Problème** : le formulaire de `contact.html` était une démo locale (succès
simulé, rien n'était envoyé) et la section « Messages » du dashboard était vide
(aucune configuration d'entité).

### Ce qui a été fait
- **Site public** (`app.js` §13) : le submit envoie un vrai
  `POST /api/v1/contact` (throttle serveur : 5 messages / 10 min) —
  bouton désactivé pendant l'envoi, message de succès/erreur dans
  `[data-contact-feedback]`, gestion du 429 (trop de messages), l'objet envoyé
  est le libellé français de l'option choisie ; les validations locales
  d'origine sont conservées.
- **Dashboard** : configuration `messages` (colonnes Reçu le / Nom / Email /
  Téléphone / Objet / Message (tronqué) / Lu ; modale de consultation avec
  « Marqué comme lu » — seul champ persistant via PUT). Nouvelle option
  `canCreate: false` dans EntityConfig → le bouton « Nouveau » est masqué
  pour les entités alimentées par le site.
- Le badge « non lus » de la sidebar existait déjà et s'appuie sur
  `GET /messages?unread=1`.

### Validation (Edge headless)
Envoi réel depuis contact.html → message enregistré (nom, objet
« Demande d'information », non lu) ✓ ; validation locale (email invalide)
toujours active ✓ ; dashboard : message visible dans la section Messages,
colonnes correctes, pas de bouton « Nouveau » ✓ ; aucune erreur JS ;
message de test supprimé.
